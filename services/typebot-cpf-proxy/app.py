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


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8080)
