"""Cria admin no primeiro boot e, opcionalmente, dados de demonstração."""
from __future__ import annotations

import os

from sqlmodel import Session, select

from .auth import hash_password
from .db import engine
from .models import Campanha, CORES, Dominio, Oferta, User


def seed() -> None:
    with Session(engine) as s:
        if s.exec(select(User)).first():
            return  # banco já populado

        admin = User(
            nome=os.environ.get("RADAR_ADMIN_NAME", "Chefe"),
            email=os.environ.get("RADAR_ADMIN_EMAIL", "admin@radar.local").lower(),
            senha_hash=hash_password(os.environ.get("RADAR_ADMIN_PASS", "troque123")),
            papel="admin",
            cor="#141a26",
        )
        s.add(admin)

        if os.environ.get("RADAR_SEED_DEMO", "1") != "1":
            s.commit()
            return

        # --- só o perfil SIP (operação real) — sem números fictícios ---
        sip = User(
            nome="Sip",
            email="sip@radar.local",
            senha_hash=hash_password("demo123"),
            papel="gestor",
            cor=CORES[4 % len(CORES)],
        )
        s.add(sip)

        oferta = Oferta(nome="Planejeja (loto)", nicho="loteria")
        s.add(oferta)
        s.commit()

        # campanha real do SIP; gasto/vendas/faturamento zerados p/ o gestor lançar
        c = Campanha(
            gestor_id=sip.id, oferta_id=oferta.id,
            plataforma="Google", status="esc", budget=None,
            gasto=0, vendas=0, faturamento=0,
        )
        s.add(c)
        s.commit()
        s.add(Dominio(campanha_id=c.id, url="planejeja.click"))
        s.commit()
