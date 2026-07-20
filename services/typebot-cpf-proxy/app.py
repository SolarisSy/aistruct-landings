"""
Proxy CPF: traduz response do hubdodesenvolvedor pro schema esperado pelo flow Typebot original.

Flow original espera response em formato:
  {"data":{"DADOS":{"nome":"...","nome_mae":"...","data_nascimento":"...","sexo":"..."}}}

hubdodesenvolvedor retorna:
  {"status":true,"result":{"nome_da_pf":"...","data_nascimento":"..."}}

Este proxy recebe ?cpf=XXX, consulta hubdodesenvolvedor, retorna no schema antigo.
URL final no flow Typebot: http://typebot-cpf-proxy:8080/?cpf={{cpf}}
"""
import os, httpx
from fastapi import FastAPI, HTTPException
from fastapi.responses import JSONResponse

app = FastAPI()

HUBDO_TOKEN = os.environ.get("RF_TOKEN_API_CPF", "")
HUBDO_URL = "https://ws.hubdodesenvolvedor.com.br/v2/cpf/"


@app.get("/")
@app.get("/consulta")
async def cpf_consulta(cpf: str = ""):
    """Endpoint unico: recebe ?cpf=XXX retorna schema antigo amnesia."""
    if not cpf:
        raise HTTPException(400, "cpf parameter required")

    # Limpa CPF (remove pontuacao)
    cpf_clean = "".join(c for c in cpf if c.isdigit())

    # Consulta hubdodesenvolvedor
    async with httpx.AsyncClient(timeout=15) as c:
        try:
            r = await c.get(f"{HUBDO_URL}?cpf={cpf_clean}&token={HUBDO_TOKEN}")
            data = r.json()
        except Exception as e:
            return JSONResponse({"status": False, "message": f"upstream error: {str(e)[:80]}"})

    # Traduz pro schema antigo (amnesia: data.DADOS.{nome,nome_mae,data_nascimento,sexo})
    result = data.get("result", {})
    translated = {
        "status": data.get("status", False),
        "message": data.get("message", ""),
        "data": {
            "DADOS": {
                "nome": result.get("nome_da_pf", ""),
                "nome_mae": result.get("nome_mae", ""),
                "data_nascimento": result.get("data_nascimento", ""),
                "sexo": result.get("sexo", ""),
            }
        },
    }
    return JSONResponse(translated)


@app.get("/health")
async def health():
    return {"ok": True, "token_set": bool(HUBDO_TOKEN)}


# ── TEMP ADMIN: publish crra flow directly to Postgres ─────────────────────
# Reads crra_flow.json (next to app.py), UPDATEs the Typebot.publishedTypebot
# column for the bot with public_id='crra'. Gated by ADMIN_TOKEN env.
# Remove this endpoint after publishing.
import json as _json
from pathlib import Path as _Path

PG_URL = os.environ.get("TYPEBOT_DATABASE_URL", "")
ADMIN_TOKEN = os.environ.get("ADMIN_PUBLISH_TOKEN", "crra-publish-once-2026")


def _pg_connect():
    import psycopg2
    # TYPEBOT_DATABASE_URL should be: postgresql://postgres:PWD@typebot-postgres:5432/typebot
    return psycopg2.connect(PG_URL)


@app.get("/admin/publish-crra")
async def admin_publish_crra(token: str = ""):
    """UPDATE the crra bot's publishedTypebot column with crra_flow.json contents."""
    if token != ADMIN_TOKEN:
        raise HTTPException(403, "forbidden")
    if not PG_URL:
        raise HTTPException(500, "TYPEBOT_DATABASE_URL not set")

    flow_path = _Path(__file__).parent / "crra_flow.json"
    if not flow_path.exists():
        raise HTTPException(500, f"crra_flow.json not found at {flow_path}")

    flow = _json.loads(flow_path.read_text(encoding="utf-8"))

    # The publishedTypebot column expects a Prisma Json value with the flow
    # structure (groups, edges, variables, events, version, settings, theme).
    # We strip top-level metadata that belongs to the Typebot record, not the published flow.
    published_keys = [
        "version", "events", "groups", "edges", "variables",
        "theme", "selectedThemeTemplateId", "settings",
    ]
    published = {k: flow.get(k) for k in published_keys if k in flow}

    # Connect and UPDATE
    try:
        conn = _pg_connect()
    except Exception as e:
        raise HTTPException(500, f"pg connect err: {str(e)[:200]}")

    cur = conn.cursor()
    try:
        # Find the bot first
        cur.execute(
            'SELECT id, name, "publicId" FROM "Typebot" WHERE "publicId" = %s;',
            ("crra",),
        )
        row = cur.fetchone()
        if not row:
            # Maybe publicId column is empty; try by name match
            cur.execute('SELECT id, name, "publicId" FROM "Typebot";')
            all_rows = cur.fetchall()
            return JSONResponse({
                "error": "no Typebot with publicId=crra",
                "all_typebots": [{"id": r[0], "name": r[1], "publicId": r[2]} for r in all_rows],
            })
        bot_id, bot_name, bot_pubid = row

        # Read the current publishedTypebot to see if column is jsonb or text
        cur.execute(
            'SELECT "publishedTypebot" FROM "Typebot" WHERE id = %s;',
            (bot_id,),
        )
        cur_row = cur.fetchone()
        current = cur_row[0] if cur_row else None
        current_is_dict = isinstance(current, dict)

        # Build the new value. Typebot v6 stores publishedTypebot as JSONB
        # with shape: {"version": "...", "events": [...], "groups": [...], ...}
        # plus sometimes "updatedAt". Keep schema compatible.
        new_published = dict(published)
        new_published["updatedAt"] = flow.get("updatedAt")

        # UPDATE
        if current_is_dict:
            # jsonb column — pass dict, psycopg2 adapts via json
            import psycopg2.extras
            cur.execute(
                'UPDATE "Typebot" SET "publishedTypebot" = %s WHERE id = %s;',
                (_json.dumps(new_published), bot_id),
            )
        else:
            # text column
            cur.execute(
                'UPDATE "Typebot" SET "publishedTypebot" = %s WHERE id = %s;',
                (_json.dumps(new_published), bot_id),
            )
        conn.commit()

        # Verify
        cur.execute(
            'SELECT "publishedTypebot"::text FROM "Typebot" WHERE id = %s;',
            (bot_id,),
        )
        verify = cur.fetchone()[0]
        verify_parsed = _json.loads(verify) if isinstance(verify, str) else verify
        # Extract webhook URLs to confirm
        webhook_urls = []
        for g in verify_parsed.get("groups", []):
            for b in g.get("blocks", []):
                if b.get("type") == "Webhook":
                    wh = b.get("options", {}).get("webhook", {})
                    webhook_urls.append(wh.get("url", ""))

        return JSONResponse({
            "ok": True,
            "bot": {"id": bot_id, "name": bot_name, "publicId": bot_pubid},
            "webhooks_after_update": webhook_urls,
            "groups_count": len(verify_parsed.get("groups", [])),
            "edges_count": len(verify_parsed.get("edges", [])),
        })
    except Exception as e:
        conn.rollback()
        raise HTTPException(500, f"update err: {str(e)[:300]}")
    finally:
        cur.close()
        conn.close()


@app.get("/admin/typebots")
async def admin_list_typebots(token: str = ""):
    """List all Typebots — diagnostic."""
    if token != ADMIN_TOKEN:
        raise HTTPException(403, "forbidden")
    if not PG_URL:
        raise HTTPException(500, "TYPEBOT_DATABASE_URL not set")
    try:
        conn = _pg_connect()
    except Exception as e:
        raise HTTPException(500, f"pg connect err: {str(e)[:200]}")
    cur = conn.cursor()
    try:
        cur.execute('SELECT id, name, "publicId", "updatedAt" FROM "Typebot" ORDER BY "updatedAt" DESC;')
        rows = cur.fetchall()
        return JSONResponse({
            "typebots": [
                {"id": r[0], "name": r[1], "publicId": r[2], "updatedAt": str(r[3])}
                for r in rows
            ]
        })
    finally:
        cur.close()
        conn.close()


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8080)
