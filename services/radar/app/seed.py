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

        # --- gestores demo ---
        nomes = ["Marcos", "Solaris", "Bomb", "Hyu", "Sip"]
        gestores: dict[str, User] = {}
        for i, nome in enumerate(nomes):
            u = User(
                nome=nome,
                email=f"{nome.lower()}@radar.local",
                senha_hash=hash_password("demo123"),
                papel="gestor",
                cor=CORES[i % len(CORES)],
            )
            s.add(u)
            gestores[nome] = u

        # --- ofertas demo ---
        ofertas: dict[str, Oferta] = {}
        for nome, nicho in [("Loto BR", "loteria"), ("Crédito Ágil", "crédito"),
                            ("Renda Extra", "renda"), ("Correios Taxa", "rastreio")]:
            o = Oferta(nome=nome, nicho=nicho)
            s.add(o)
            ofertas[nome] = o
        s.commit()

        # --- campanhas demo (gestor, oferta, plat, status, budget, gasto, vendas, fat, [domínios]) ---
        demo = [
            ("Marcos", "Loto BR", "Google", "esc", 300, 4200, 92, 8924, ["ganhabr.lat", "ganhosbr.lat"]),
            ("Solaris", "Loto BR", "Google", "est", 200, 1800, 31, 3007, ["ganharja.lat"]),
            ("Bomb", "Crédito Ágil", "Google", "tes", 150, 2600, 12, 1164, ["creditoagil.cfd", "oportunidademomento.cfd"]),
            ("Hyu", "Renda Extra", "Meta", "esc", 250, 3100, 48, 6720, ["rendahoje.site"]),
            ("Sip", "Correios Taxa", "Google", "pau", 100, 900, 20, 1380, ["rastreiofacil.cfd"]),
            ("Marcos", "Renda Extra", "Google", "est", 180, 1500, 22, 2134, ["renda-extra.lat"]),
        ]
        for gnome, onome, plat, st, budget, gasto, vendas, fat, doms in demo:
            c = Campanha(
                gestor_id=gestores[gnome].id, oferta_id=ofertas[onome].id,
                plataforma=plat, status=st, budget=budget,
                gasto=gasto, vendas=vendas, faturamento=fat,
            )
            s.add(c)
            s.commit()
            for url in doms:
                s.add(Dominio(campanha_id=c.id, url=url))
        s.commit()
