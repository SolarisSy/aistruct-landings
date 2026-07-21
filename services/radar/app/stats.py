"""Agregações lidas pelo dashboard. Tudo derivado das campanhas."""
from __future__ import annotations

from dataclasses import dataclass, field

from sqlmodel import Session, select

from .models import Campanha, roas_cls


@dataclass
class Totais:
    fat: float = 0.0
    gasto: float = 0.0
    vendas: int = 0
    ativas: int = 0
    total: int = 0

    @property
    def lucro(self) -> float:
        return self.fat - self.gasto

    @property
    def roas(self) -> float:
        return (self.fat / self.gasto) if self.gasto else 0.0

    @property
    def margem(self) -> int:
        return round(self.lucro / self.fat * 100) if self.fat else 0


@dataclass
class OfertaAgg:
    nome: str
    fat: float = 0.0
    gasto: float = 0.0
    vendas: int = 0
    n: int = 0
    oferta_id: int = 0
    dominios: set[str] = field(default_factory=set)
    gestores: set[str] = field(default_factory=set)

    @property
    def lucro(self) -> float:
        return self.fat - self.gasto

    @property
    def roas(self) -> float:
        return (self.fat / self.gasto) if self.gasto else 0.0

    @property
    def roas_cls(self) -> str:
        return roas_cls(self.roas)


@dataclass
class GestorAgg:
    nome: str
    cor: str = "#3b6ef5"
    fat: float = 0.0
    gasto: float = 0.0
    vendas: int = 0
    n: int = 0

    @property
    def iniciais(self) -> str:
        return self.nome[:2].upper()

    @property
    def lucro(self) -> float:
        return self.fat - self.gasto

    @property
    def roas(self) -> float:
        return (self.fat / self.gasto) if self.gasto else 0.0

    @property
    def roas_cls(self) -> str:
        return roas_cls(self.roas)


def all_campaigns(session: Session) -> list[Campanha]:
    return list(session.exec(select(Campanha)).all())


def totais(camps: list[Campanha]) -> Totais:
    t = Totais(total=len(camps))
    for c in camps:
        t.fat += c.faturamento
        t.gasto += c.gasto
        t.vendas += c.vendas
        if c.status != "pau":
            t.ativas += 1
    return t


def por_oferta(camps: list[Campanha]) -> list[OfertaAgg]:
    m: dict[str, OfertaAgg] = {}
    for c in camps:
        nome = c.oferta.nome if c.oferta else "—"
        o = m.setdefault(nome, OfertaAgg(nome=nome, oferta_id=c.oferta_id))
        o.fat += c.faturamento
        o.gasto += c.gasto
        o.vendas += c.vendas
        o.n += 1
        for d in c.dominios:
            o.dominios.add(d.url)
        if c.gestor:
            o.gestores.add(c.gestor.nome)
    return sorted(m.values(), key=lambda o: o.fat, reverse=True)


def por_gestor(camps: list[Campanha]) -> list[GestorAgg]:
    m: dict[str, GestorAgg] = {}
    for c in camps:
        if not c.gestor:
            continue
        g = m.setdefault(c.gestor.nome, GestorAgg(nome=c.gestor.nome, cor=c.gestor.cor))
        g.fat += c.faturamento
        g.gasto += c.gasto
        g.vendas += c.vendas
        g.n += 1
    return sorted(m.values(), key=lambda g: g.lucro, reverse=True)


def alertas(camps: list[Campanha]) -> list[dict]:
    """Gera alertas dos números que os gestores lançam."""
    out: list[dict] = []
    for c in camps:
        if c.status != "pau" and c.gasto > 0 and c.roas < 1.0:
            out.append({
                "tipo": "crit",
                "camp_id": c.id,
                "titulo": f"{c.oferta.nome if c.oferta else '—'} ({c.gestor.nome if c.gestor else '?'}) no prejuízo",
                "sub": f"ROAS {c.roas:.2f}x · gastou R$ {c.gasto:,.0f} e faturou R$ {c.faturamento:,.0f}."
                       .replace(",", "."),
            })
    return out
