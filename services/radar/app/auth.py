"""Autenticação: hash de senha (pbkdf2, stdlib) + helpers de sessão.

Sem passlib/bcrypt de propósito — pbkdf2_hmac da stdlib evita dor de versão
de dependência nativa e é seguro o suficiente pra um painel interno.
"""
from __future__ import annotations

import base64
import hashlib
import hmac
import os

from fastapi import Request
from sqlmodel import Session, select

from .models import User

_ITERATIONS = 200_000


def hash_password(password: str) -> str:
    salt = os.urandom(16)
    dk = hashlib.pbkdf2_hmac("sha256", password.encode(), salt, _ITERATIONS)
    return "pbkdf2$%d$%s$%s" % (
        _ITERATIONS,
        base64.b64encode(salt).decode(),
        base64.b64encode(dk).decode(),
    )


def verify_password(password: str, stored: str) -> bool:
    try:
        _, iters, salt_b64, dk_b64 = stored.split("$")
        salt = base64.b64decode(salt_b64)
        expected = base64.b64decode(dk_b64)
        dk = hashlib.pbkdf2_hmac("sha256", password.encode(), salt, int(iters))
        return hmac.compare_digest(dk, expected)
    except Exception:
        return False


def current_user(request: Request, session: Session) -> User | None:
    uid = request.session.get("uid")
    if not uid:
        return None
    return session.get(User, uid)


def authenticate(session: Session, email: str, password: str) -> User | None:
    user = session.exec(select(User).where(User.email == email.strip().lower())).first()
    if user and verify_password(password, user.senha_hash):
        return user
    return None
