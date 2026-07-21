"""Radar — painel de operação de tráfego. FastAPI + SQLite + Jinja."""
from __future__ import annotations

import os
import re
from datetime import datetime
from pathlib import Path

from fastapi import Depends, FastAPI, Form, Request
from fastapi.responses import RedirectResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from sqlmodel import Session, select
from starlette.middleware.sessions import SessionMiddleware

from . import stats
from .auth import authenticate, current_user, hash_password
from .db import get_session, init_db
from .models import (CORES, PLATAFORMAS, STATUS, STATUS_ORDEM, Campanha,
                     Dominio, Oferta, User, roas_cls)
from .seed import seed

BASE = Path(__file__).parent

app = FastAPI(title="Radar")
app.add_middleware(SessionMiddleware,
                   secret_key=os.environ.get("RADAR_SECRET", "dev-secret-troque"))
app.mount("/static", StaticFiles(directory=BASE / "static"), name="static")

templates = Jinja2Templates(directory=BASE / "templates")


def _brl(n) -> str:
    try:
        return "R$ " + f"{round(float(n)):,}".replace(",", ".")
    except (TypeError, ValueError):
        return "R$ 0"


def _num(v) -> str:
    """Número limpo p/ inputs: inteiro sem '.0', senão o valor; None -> ''."""
    if v is None or v == "":
        return ""
    f = float(v)
    return str(int(f)) if f == int(f) else str(f)


templates.env.filters["brl"] = _brl
templates.env.filters["num"] = _num
templates.env.globals.update(STATUS=STATUS, roas_cls=roas_cls, PLATAFORMAS=PLATAFORMAS)


@app.on_event("startup")
def _startup() -> None:
    init_db()
    seed()


def _render(request: Request, user: User, template: str, active: str, **ctx):
    return templates.TemplateResponse(
        template, {"request": request, "user": user, "active": active, **ctx})


def _can_edit(user: User, camp: Campanha) -> bool:
    return user.is_admin or camp.gestor_id == user.id


def _parse_domains(raw: str) -> list[str]:
    parts = re.split(r"[\s,;]+", (raw or "").strip())
    seen, out = set(), []
    for p in parts:
        p = p.strip().lower()
        p = re.sub(r"^https?://", "", p)   # remove esquema
        p = re.sub(r"^www\.", "", p)        # remove www.
        p = p.rstrip("/")
        if p and p not in seen:
            seen.add(p)
            out.append(p)
    return out


# --- auth --------------------------------------------------------------------

@app.get("/login")
def login_form(request: Request):
    return templates.TemplateResponse("login.html", {"request": request, "erro": None})


@app.post("/login")
def login_post(request: Request, email: str = Form(...), senha: str = Form(...),
               session: Session = Depends(get_session)):
    user = authenticate(session, email, senha)
    if not user:
        return templates.TemplateResponse(
            "login.html", {"request": request, "erro": "E-mail ou senha inválidos."})
    request.session["uid"] = user.id
    return RedirectResponse("/", status_code=303)


@app.get("/logout")
def logout(request: Request):
    request.session.clear()
    return RedirectResponse("/login", status_code=303)


# --- dashboard ---------------------------------------------------------------

@app.get("/")
def home(request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)
    camps = stats.all_campaigns(session)
    offs = stats.por_oferta(camps)
    max_fat = max((o.fat for o in offs), default=1) or 1
    return _render(request, user, "home.html", "home",
                   totais=stats.totais(camps), ofertas=offs, max_fat=max_fat,
                   gestores=stats.por_gestor(camps), alertas=stats.alertas(camps),
                   camps=sorted(camps, key=lambda c: c.faturamento, reverse=True))


@app.get("/campanhas")
def kanban(request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)
    camps = stats.all_campaigns(session)
    colunas = {st: [c for c in camps if c.status == st] for st in STATUS_ORDEM}
    return _render(request, user, "kanban.html", "kanban", colunas=colunas)


@app.get("/ofertas")
def ofertas(request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)
    offs = stats.por_oferta(stats.all_campaigns(session))
    return _render(request, user, "offers.html", "offers", ofertas=offs)


@app.get("/oferta/{oferta_id}")
def oferta_detalhe(oferta_id: int, request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)
    oferta = session.get(Oferta, oferta_id)
    if not oferta:
        return RedirectResponse("/ofertas", status_code=303)
    camps = [c for c in stats.all_campaigns(session) if c.oferta_id == oferta_id]
    agg = stats.por_oferta(camps)
    return _render(request, user, "oferta_detalhe.html", "offers",
                   oferta=oferta, agg=agg[0] if agg else None, camps=camps)


@app.get("/gestores")
def gestores(request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)
    users = list(session.exec(select(User)).all())
    aggs = {g.nome: g for g in stats.por_gestor(stats.all_campaigns(session))}
    return _render(request, user, "team.html", "team", usuarios=users, aggs=aggs)


# --- campanha CRUD -----------------------------------------------------------

@app.get("/campanha/nova")
def campanha_nova(request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)
    return _render(request, user, "campanha_form.html", "kanban", camp=None,
                   ofertas=list(session.exec(select(Oferta)).all()),
                   gestores=list(session.exec(select(User)).all()))


@app.get("/campanha/{camp_id}")
def campanha_editar(camp_id: int, request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)
    camp = session.get(Campanha, camp_id)
    if not camp:
        return RedirectResponse("/", status_code=303)
    return _render(request, user, "campanha_form.html", "kanban", camp=camp,
                   pode_editar=_can_edit(user, camp),
                   ofertas=list(session.exec(select(Oferta)).all()),
                   gestores=list(session.exec(select(User)).all()))


@app.post("/campanha/salvar")
def campanha_salvar(
    request: Request, session: Session = Depends(get_session),
    camp_id: str = Form(""), oferta_nome: str = Form(...), plataforma: str = Form("Google"),
    status: str = Form("tes"), gestor_id: str = Form(""), budget: str = Form(""),
    gasto: float = Form(0), vendas: int = Form(0), faturamento: float = Form(0),
    dominios: str = Form(""), observacao: str = Form(""),
):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)

    # autorizar ANTES de tocar no banco (senão POST bloqueado cria oferta-fantasma)
    dono = int(gestor_id) if (user.is_admin and gestor_id) else user.id
    if camp_id:
        camp = session.get(Campanha, int(camp_id))
        if not camp or not _can_edit(user, camp):
            return RedirectResponse("/", status_code=303)
    else:
        camp = None

    # oferta: get-or-create por nome (só depois de autorizado)
    nome = oferta_nome.strip()
    oferta = session.exec(select(Oferta).where(Oferta.nome == nome)).first()
    if not oferta:
        oferta = Oferta(nome=nome)
        session.add(oferta)
        session.commit()
        session.refresh(oferta)

    if camp is None:
        camp = Campanha(gestor_id=dono, oferta_id=oferta.id)
        session.add(camp)

    camp.oferta_id = oferta.id
    camp.plataforma = plataforma
    camp.status = status if status in STATUS else "tes"
    if user.is_admin and gestor_id:
        camp.gestor_id = dono
    camp.budget = float(budget) if budget.strip() else None
    camp.gasto = gasto
    camp.vendas = vendas
    camp.faturamento = faturamento
    camp.observacao = observacao.strip()
    camp.atualizado_em = datetime.utcnow()
    session.commit()
    session.refresh(camp)

    # domínios: substitui o conjunto
    for d in list(camp.dominios):
        session.delete(d)
    for url in _parse_domains(dominios):
        session.add(Dominio(campanha_id=camp.id, url=url))
    session.commit()

    return RedirectResponse("/", status_code=303)


@app.post("/campanha/{camp_id}/status")
def campanha_status(camp_id: int, request: Request, novo: str = Form(...),
                    session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)
    camp = session.get(Campanha, camp_id)
    if camp and _can_edit(user, camp) and novo in STATUS:
        camp.status = novo
        session.commit()
    return RedirectResponse("/campanhas", status_code=303)


@app.post("/campanha/{camp_id}/deletar")
def campanha_deletar(camp_id: int, request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user:
        return RedirectResponse("/login", status_code=303)
    camp = session.get(Campanha, camp_id)
    if camp and _can_edit(user, camp):
        session.delete(camp)
        session.commit()
    return RedirectResponse("/", status_code=303)


# --- admin: gestores ---------------------------------------------------------

@app.post("/gestor/novo")
def gestor_novo(request: Request, nome: str = Form(...), email: str = Form(...),
                senha: str = Form(...), papel: str = Form("gestor"),
                session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user or not user.is_admin:
        return RedirectResponse("/login", status_code=303)
    if not session.exec(select(User).where(User.email == email.strip().lower())).first():
        n = session.exec(select(User)).all()
        session.add(User(nome=nome.strip(), email=email.strip().lower(),
                         senha_hash=hash_password(senha), papel=papel,
                         cor=CORES[len(n) % len(CORES)]))
        session.commit()
    return RedirectResponse("/gestores", status_code=303)


@app.get("/gestor/{gestor_id}/editar")
def gestor_editar(gestor_id: int, request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user or not user.is_admin:
        return RedirectResponse("/login", status_code=303)
    g = session.get(User, gestor_id)
    if not g:
        return RedirectResponse("/gestores", status_code=303)
    tem_camp = session.exec(select(Campanha).where(Campanha.gestor_id == gestor_id)).first() is not None
    return _render(request, user, "gestor_form.html", "team", g=g, tem_camp=tem_camp)


@app.post("/gestor/{gestor_id}/editar")
def gestor_editar_post(gestor_id: int, request: Request, nome: str = Form(...),
                       email: str = Form(...), papel: str = Form("gestor"), cor: str = Form(""),
                       session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user or not user.is_admin:
        return RedirectResponse("/login", status_code=303)
    g = session.get(User, gestor_id)
    if g:
        g.nome = nome.strip()
        g.email = email.strip().lower()
        g.papel = papel if papel in ("gestor", "admin") else "gestor"
        if cor.strip():
            g.cor = cor.strip()
        session.commit()
    return RedirectResponse("/gestores", status_code=303)


@app.post("/gestor/{gestor_id}/deletar")
def gestor_deletar(gestor_id: int, request: Request, session: Session = Depends(get_session)):
    user = current_user(request, session)
    if not user or not user.is_admin:
        return RedirectResponse("/login", status_code=303)
    g = session.get(User, gestor_id)
    tem_camp = session.exec(select(Campanha).where(Campanha.gestor_id == gestor_id)).first() is not None
    # não apaga admin, nem gestor com campanhas (reatribua antes)
    if g and not g.is_admin and not tem_camp and g.id != user.id:
        session.delete(g)
        session.commit()
    return RedirectResponse("/gestores", status_code=303)


@app.get("/healthz")
def healthz():
    return {"ok": True}
