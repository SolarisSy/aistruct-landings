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


@app.get("/admin/diag")
async def admin_diag(token: str = ""):
    """Diagnostic — reports env and import availability."""
    if token != ADMIN_TOKEN:
        raise HTTPException(403, "forbidden")
    diag = {"pg_url_set": bool(PG_URL), "pg_url_prefix": PG_URL[:25] if PG_URL else ""}
    try:
        import psycopg2
        diag["psycopg2_import"] = "ok"
    except Exception as e:
        diag["psycopg2_import"] = f"FAIL: {str(e)[:200]}"
    flow_path = _Path(__file__).parent / "crra_flow.json"
    diag["crra_flow_exists"] = flow_path.exists()
    diag["crra_flow_size"] = flow_path.stat().st_size if flow_path.exists() else 0
    return JSONResponse(diag)


@app.get("/admin/publish-crra")
async def admin_publish_crra(token: str = ""):
    """UPDATE the crra bot's publishedTypebot column with crra_flow.json contents."""
    if token != ADMIN_TOKEN:
        raise HTTPException(403, "forbidden")
    if not PG_URL:
        return JSONResponse({"error": "TYPEBOT_DATABASE_URL not set"}, status_code=500)

    try:
        flow_path = _Path(__file__).parent / "crra_flow.json"
        if not flow_path.exists():
            return JSONResponse({"error": f"crra_flow.json not found at {flow_path}"}, status_code=500)
        flow = _json.loads(flow_path.read_text(encoding="utf-8"))
    except Exception as e:
        return JSONResponse({"error": "flow load err", "detail": str(e)[:300]}, status_code=500)

    published_keys = [
        "version", "events", "groups", "edges", "variables",
        "theme", "selectedThemeTemplateId", "settings",
    ]
    published = {k: flow.get(k) for k in published_keys if k in flow}
    published["updatedAt"] = flow.get("updatedAt")

    try:
        conn = _pg_connect()
    except Exception as e:
        return JSONResponse({"error": "pg connect err", "detail": str(e)[:300]}, status_code=500)

    try:
        cur = conn.cursor()
        # Find the bot (Typebot table holds the draft + publicId)
        cur.execute('SELECT id, name, "publicId" FROM "Typebot" WHERE "publicId" = %s;', ("crra",))
        row = cur.fetchone()
        if not row:
            cur.execute('SELECT id, name, "publicId" FROM "Typebot";')
            all_rows = cur.fetchall()
            return JSONResponse({
                "error": "no Typebot with publicId=crra",
                "all_typebots": [{"id": r[0], "name": r[1], "publicId": r[2]} for r in all_rows],
            })
        bot_id, bot_name, bot_pubid = row

        # NEW API schema: published flow lives in PublicTypebot (linked by typebotId),
        # NOT a publishedTypebot column on Typebot. Update both draft (Typebot) and
        # the published row (PublicTypebot) so the viewer serves the new flow.
        pub_fields = {
            "groups": _json.dumps(published.get("groups", [])),
            "edges": _json.dumps(published.get("edges", [])),
            "variables": _json.dumps(published.get("variables", [])),
            "events": _json.dumps(published.get("events", [])),
            "theme": _json.dumps(published.get("theme", {})),
            "settings": _json.dumps(published.get("settings", {})),
            "version": published.get("version", "6"),
        }
        # 1) PublicTypebot: the row the viewer actually serves
        cur.execute('SELECT id FROM "PublicTypebot" WHERE "typebotId" = %s;', (bot_id,))
        pub_row = cur.fetchone()
        pub_updated = 0
        if pub_row:
            set_clause = ", ".join(f'"{k}" = %s::jsonb' if k != "version" else f'"{k}" = %s' for k in pub_fields)
            cur.execute(
                f'UPDATE "PublicTypebot" SET {set_clause}, "updatedAt" = NOW() WHERE "typebotId" = %s;',
                (*pub_fields.values(), bot_id),
            )
            pub_updated = cur.rowcount
        # 2) Typebot draft (keeps draft in sync; groups/edges/variables/events are json)
        draft_fields = {k: v for k, v in pub_fields.items()}
        set_clause2 = ", ".join(f'"{k}" = %s::jsonb' if k != "version" else f'"{k}" = %s' for k in draft_fields)
        cur.execute(
            f'UPDATE "Typebot" SET {set_clause2}, "updatedAt" = NOW() WHERE "id" = %s;',
            (*draft_fields.values(), bot_id),
        )
        conn.commit()

        # Verify (read back from PublicTypebot)
        verify_parsed = {}
        if pub_row:
            cur.execute(
                'SELECT "groups"::text, "edges"::text FROM "PublicTypebot" WHERE "typebotId" = %s;',
                (bot_id,),
            )
            vrow = cur.fetchone()
            if vrow:
                verify_parsed["groups"] = _json.loads(vrow[0]) if isinstance(vrow[0], str) else vrow[0]
                verify_parsed["edges"] = _json.loads(vrow[1]) if isinstance(vrow[1], str) else vrow[1]

        webhook_urls = []
        redirect_urls = []
        code_urls = []
        for g in verify_parsed.get("groups", []):
            for b in g.get("blocks", []):
                if b.get("type") == "Webhook":
                    wh = b.get("options", {}).get("webhook", {})
                    webhook_urls.append(wh.get("url", ""))
                elif b.get("type") == "Redirect":
                    redirect_urls.append(b.get("options", {}).get("url", ""))
                elif b.get("type") == "Code":
                    code_urls.append(b.get("options", {}).get("content", ""))

        return JSONResponse({
            "ok": True,
            "bot": {"id": bot_id, "name": bot_name, "publicId": bot_pubid},
            "publictypebot_updated": pub_updated,
            "webhooks_after_update": webhook_urls,
            "redirect_urls": redirect_urls,
            "code_blocks": [c[:120] for c in code_urls],
            "groups_count": len(verify_parsed.get("groups", [])),
            "edges_count": len(verify_parsed.get("edges", [])),
        })
    except Exception as e:
        try: conn.rollback()
        except: pass
        return JSONResponse({"error": "update err", "detail": str(e)[:400]}, status_code=500)
    finally:
        try: cur.close()
        except: pass
        try: conn.close()
        except: pass


@app.get("/admin/typebots")
async def admin_list_typebots(token: str = ""):
    """List all Typebots — diagnostic."""
    if token != ADMIN_TOKEN:
        raise HTTPException(403, "forbidden")
    if not PG_URL:
        return JSONResponse({"error": "TYPEBOT_DATABASE_URL not set"}, status_code=500)
    try:
        conn = _pg_connect()
    except Exception as e:
        return JSONResponse({"error": "pg connect err", "detail": str(e)[:300], "pg_url_prefix": PG_URL[:30]}, status_code=500)
    try:
        cur = conn.cursor()
        # Discover table names first (Prisma uses PascalCase by default)
        cur.execute("""
            SELECT table_schema, table_name
            FROM information_schema.tables
            WHERE table_schema NOT IN ('pg_catalog','information_schema')
            ORDER BY table_schema, table_name;
        """)
        tables = [{"schema": r[0], "table": r[1]} for r in cur.fetchall()]
        # Find typebot-like tables
        typebot_tables = [t for t in tables if 'typebot' in t['table'].lower()]
        result = {"all_tables_count": len(tables), "typebot_tables": typebot_tables, "first_20_tables": tables[:20]}

        # If we found a Typebot table, list its rows
        for t in typebot_tables:
            schema = t['schema']
            table = t['table']
            try:
                # Detect columns
                cur.execute("""
                    SELECT column_name FROM information_schema.columns
                    WHERE table_schema=%s AND table_name=%s
                    ORDER BY ordinal_position;
                """, (schema, table))
                cols = [r[0] for r in cur.fetchall()]
                result[f"{table}_cols"] = cols
                # Try to select id/name/publicId-like columns
                id_col = next((c for c in cols if c.lower() == 'id'), 'id')
                name_col = next((c for c in cols if c.lower() == 'name'), None)
                pubid_col = next((c for c in cols if 'publicid' in c.lower() or 'public_id' in c.lower()), None)
                if name_col:
                    select_cols = [id_col, name_col]
                    if pubid_col: select_cols.append(pubid_col)
                    cur.execute(f'SELECT {",".join(chr(34)+c+chr(34) for c in select_cols)} FROM {chr(34)+schema+chr(34)}.{chr(34)+table+chr(34)} LIMIT 20;')
                    rows = cur.fetchall()
                    result[f"{table}_rows"] = [
                        {"id": r[0], "name": r[1], "publicId": r[2] if pubid_col else None}
                        for r in rows
                    ]
            except Exception as e:
                result[f"{table}_err"] = str(e)[:200]

        return JSONResponse(result)
    except Exception as e:
        return JSONResponse({"error": "query err", "detail": str(e)[:300]}, status_code=500)
    finally:
        try: cur.close()
        except: pass
        try: conn.close()
        except: pass


@app.get("/admin/schema-token")
async def admin_schema_token(token: str = ""):
    """Lê schema + rows de ApiToken/User/Workspace pra permitir criar token via API oficial."""
    if token != ADMIN_TOKEN:
        raise HTTPException(403, "forbidden")
    if not PG_URL:
        return JSONResponse({"error": "TYPEBOT_DATABASE_URL not set"}, status_code=500)
    try:
        conn = _pg_connect()
    except Exception as e:
        return JSONResponse({"error": "pg connect err", "detail": str(e)[:300]}, status_code=500)
    try:
        cur = conn.cursor()
        out = {}
        for t in ["ApiToken", "User", "Workspace"]:
            cur.execute(
                "SELECT column_name, data_type FROM information_schema.columns "
                "WHERE table_name = %s ORDER BY ordinal_position;",
                (t,),
            )
            out[f"{t}_cols"] = [{"name": r[0], "type": r[1]} for r in cur.fetchall()]
        # sample rows (ApiToken - pode estar vazio; User/Workspace - precisamos dos IDs)
        for t, lim in [("ApiToken", 3), ("User", 3), ("Workspace", 3)]:
            try:
                cur.execute(f'SELECT * FROM "{t}" LIMIT %s;', (lim,))
                cols = [d[0] for d in cur.description]
                rows = [dict(zip(cols, [str(v)[:80] if v is not None else None for v in r])) for r in cur.fetchall()]
                out[f"{t}_rows"] = rows
            except Exception as e:
                out[f"{t}_rows_err"] = str(e)[:150]
        return JSONResponse(out)
    except Exception as e:
        return JSONResponse({"error": "query err", "detail": str(e)[:300]}, status_code=500)
    finally:
        try: cur.close()
        except: pass
        try: conn.close()
        except: pass


@app.get("/admin/create-token")
async def admin_create_token(token: str = "", name: str = "crra-admin"):
    """Cria um ApiToken (schema real: id, token, name, ownerId, createdAt) p/ o 1o user."""
    if token != ADMIN_TOKEN:
        raise HTTPException(403, "forbidden")
    if not PG_URL:
        return JSONResponse({"error": "TYPEBOT_DATABASE_URL not set"}, status_code=500)
    try:
        conn = _pg_connect()
    except Exception as e:
        return JSONResponse({"error": "pg connect err", "detail": str(e)[:300]}, status_code=500)
    try:
        import secrets as _secrets
        cur = conn.cursor()
        cur.execute('SELECT id, email FROM "User" LIMIT 1;')
        urow = cur.fetchone()
        if not urow:
            return JSONResponse({"error": "no user found"})
        user_id, user_email = urow
        tk = "tb_" + _secrets.token_urlsafe(24)
        tk_id = "tok_" + _secrets.token_urlsafe(12)
        cur.execute(
            'INSERT INTO "ApiToken" ("id", "token", "name", "ownerId", "createdAt") '
            'VALUES (%s, %s, %s, %s, NOW());',
            (tk_id, tk, name, user_id),
        )
        conn.commit()
        return JSONResponse({
            "ok": True, "token": tk, "token_id": tk_id, "name": name,
            "owner": {"id": user_id, "email": user_email},
        })
    except Exception as e:
        try: conn.rollback()
        except: pass
        return JSONResponse({"error": "create err", "detail": str(e)[:400]}, status_code=500)
    finally:
        try: cur.close()
        except: pass
        try: conn.close()
        except: pass


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8080)
