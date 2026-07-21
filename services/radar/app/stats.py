"""Agregações lidas pelo dashboard. Tudo derivado das campanhas."""
from __future__ import annotations

from dataclasses import dataclass, field

from sqlmodel import Session, select

from .models import Campanha, Config, DEFAULT_USD_BRL, roas_cls


def get_rate(session: Session) -> float:
    c = session.exec(select(Config).where(Config.chave == "usd_brl")).first()
    try:
        return float(c.valor) if c else DEFAULT_USD_BRL
    except (TypeError, ValueError):
        return DEFAULT_USD_BRL


def set_rate(session: Session, v) -> float:
    v = max(0.01, float(v))
    c = session.exec(select(Config).where(Config.chave == "usd_brl")).first()
    if c:
        c.valor = str(v)
    else:
        session.add(Config(chave="usd_brl", valor=str(v)))
    session.commit()
    return v


def em_view(valor: float, moeda: str, rate: float, view: str) -> float:
    """Converte um valor NATIVO para a moeda de exibição (view).
    Passo 1: nativo -> BRL (USD * taxa). Passo 2: BRL -> view (÷ taxa se USD)."""
    brl = valor * rate if moeda == "USD" else valor
    return brl if view == "BRL" else brl / rate


@dataclass
class Totais:
    fat: float = 0.0        # consolidado em BRL
    gasto: float = 0.0      # consolidado em BRL
    vendas: int = 0
    ativas: int = 0
    total: int = 0
    por_moeda: dict = field(default_factory=dict)  # moeda -> {fat, gasto} nativos

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


def totais(camps: list[Campanha], rate: float, view: str = "BRL") -> Totais:
    t = Totais(total=len(camps))
    for c in camps:
        t.fat += em_view(c.faturamento, c.moeda, rate, view)
        t.gasto += em_view(c.gasto, c.moeda, rate, view)
        t.vendas += c.vendas
        if c.status != "pau":
            t.ativas += 1
        m = t.por_moeda.setdefault(c.moeda, {"fat": 0.0, "gasto": 0.0})
        m["fat"] += c.faturamento
        m["gasto"] += c.gasto
    return t


def por_oferta(camps: list[Campanha], rate: float, view: str = "BRL") -> list[OfertaAgg]:
    m: dict[str, OfertaAgg] = {}
    for c in camps:
        nome = c.oferta.nome if c.oferta else "—"
        o = m.setdefault(nome, OfertaAgg(nome=nome, oferta_id=c.oferta_id))
        o.fat += em_view(c.faturamento, c.moeda, rate, view)
        o.gasto += em_view(c.gasto, c.moeda, rate, view)
        o.vendas += c.vendas
        o.n += 1
        for d in c.dominios:
            o.dominios.add(d.url)
        if c.gestor:
            o.gestores.add(c.gestor.nome)
    return sorted(m.values(), key=lambda o: o.fat, reverse=True)


def por_gestor(camps: list[Campanha], rate: float, view: str = "BRL") -> list[GestorAgg]:
    m: dict[str, GestorAgg] = {}
    for c in camps:
        if not c.gestor:
            continue
        g = m.setdefault(c.gestor.nome, GestorAgg(nome=c.gestor.nome, cor=c.gestor.cor))
        g.fat += em_view(c.faturamento, c.moeda, rate, view)
        g.gasto += em_view(c.gasto, c.moeda, rate, view)
        g.vendas += c.vendas
        g.n += 1
    return sorted(m.values(), key=lambda g: g.lucro, reverse=True)


def alertas(camps: list[Campanha]) -> list[dict]:
    """Alertas do painel: avisos manuais (observação) + prejuízo detectado."""
    out: list[dict] = []
    # avisos manuais primeiro (reprovação, pendência etc.)
    for c in camps:
        if c.observacao and c.observacao.strip():
            out.append({
                "tipo": "w",
                "camp_id": c.id,
                "titulo": f"{c.oferta.nome if c.oferta else '—'} ({c.gestor.nome if c.gestor else '?'}) — aviso",
                "sub": c.observacao.strip(),
            })
    # prejuízo (ROAS < 1)
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
