"""Engine SQLite + sessão. Banco em RADAR_DB (default arquivo local)."""
from __future__ import annotations

import os

from sqlmodel import SQLModel, Session, create_engine

DB_URL = os.environ.get("RADAR_DB", "sqlite:///radar.db")

engine = create_engine(DB_URL, echo=False, connect_args={"check_same_thread": False})


def init_db() -> None:
    SQLModel.metadata.create_all(engine)


def get_session():
    with Session(engine) as session:
        yield session
