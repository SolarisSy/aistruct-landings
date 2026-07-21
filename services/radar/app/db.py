"""Engine SQLite + sessão. Banco em RADAR_DB (default arquivo local)."""
from __future__ import annotations

import os

from sqlmodel import SQLModel, Session, create_engine

DB_URL = os.environ.get("RADAR_DB", "sqlite:///radar.db")

engine = create_engine(DB_URL, echo=False, connect_args={"check_same_thread": False})


def init_db() -> None:
    SQLModel.metadata.create_all(engine)
    _migrate()


def _migrate() -> None:
    """Migrações leves p/ bancos já existentes (SQLite ALTER TABLE idempotente)."""
    with engine.begin() as conn:
        cols = [r[1] for r in conn.exec_driver_sql("PRAGMA table_info(campanha)").fetchall()]
        if "observacao" not in cols:
            conn.exec_driver_sql("ALTER TABLE campanha ADD COLUMN observacao VARCHAR DEFAULT ''")
        if "moeda" not in cols:
            conn.exec_driver_sql("ALTER TABLE campanha ADD COLUMN moeda VARCHAR DEFAULT 'BRL'")
        # backlog ganhou campos de rascunho de anúncio
        bcols = [r[1] for r in conn.exec_driver_sql("PRAGMA table_info(backlog)").fetchall()]
        for col, default in [("plataforma", "'Google'"), ("moeda", "'BRL'"), ("dominios", "''"),
                             ("budget", "''"), ("criativo", "''"), ("publico", "''"),
                             ("config", "''"), ("observacao", "''")]:
            if bcols and col not in bcols:
                conn.exec_driver_sql(f"ALTER TABLE backlog ADD COLUMN {col} VARCHAR DEFAULT {default}")


def get_session():
    with Session(engine) as session:
        yield session
