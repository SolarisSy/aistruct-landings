"""Modelo de dados do Radar.

Unidade central = Campanha (um gestor rodando uma oferta). Gasto/vendas/
faturamento são manuais e editáveis; lucro e ROAS são SEMPRE derivados.
"""
from datetime import datetime
from typing import Optional

from sqlmodel import Field, Relationship, SQLModel

# --- domínios de valores (constantes de UI) ----------------------------------

# status -> (rótulo, classe css do pill, cor do swatch). Ordem = colunas kanban.
STATUS: dict[str, tuple[str, str, str]] = {
    "tes": ("Testando", "p-tes", "#c07a13"),
    "esc": ("Escalando", "p-esc", "#12a150"),
    "est": ("Estável", "p-est", "#3b6ef5"),
    "pau": ("Pausado", "p-pau", "#8a94a6"),
}
STATUS_ORDEM = ["tes", "esc", "est", "pau"]

PLATAFORMAS = ["Google", "Meta", "TikTok", "Taboola", "Outra"]

# moeda -> (símbolo, nome). Valores guardados na moeda NATIVA da campanha;
# os totais do painel consolidam em BRL via taxa manual (Config usd_brl).
MOEDAS = {"BRL": ("R$", "Real"), "USD": ("US$", "Dólar")}
DEFAULT_USD_BRL = 5.40


def fmt_money(v, moeda: str = "BRL") -> str:
    sym = MOEDAS.get(moeda, MOEDAS["BRL"])[0]
    try:
        return sym + " " + f"{round(float(v)):,}".replace(",", ".")
    except (TypeError, ValueError):
        return sym + " 0"

# paleta de avatar por gestor (fallback ciclado no cadastro)
CORES = ["#3b6ef5", "#7c53e6", "#d23b47", "#12a150", "#c07a13", "#0e9bb5", "#c2410c"]


class User(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    nome: str
    email: str = Field(index=True, unique=True)
    senha_hash: str
    papel: str = "gestor"  # gestor | admin
    cor: str = "#3b6ef5"

    campanhas: list["Campanha"] = Relationship(back_populates="gestor")

    @property
    def iniciais(self) -> str:
        return (self.nome or "?")[:2].upper()

    @property
    def is_admin(self) -> bool:
        return self.papel == "admin"


class Oferta(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    nome: str = Field(index=True, unique=True)
    nicho: str = ""

    campanhas: list["Campanha"] = Relationship(back_populates="oferta")


class Campanha(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    gestor_id: int = Field(foreign_key="user.id", index=True)
    oferta_id: int = Field(foreign_key="oferta.id", index=True)
    plataforma: str = "Google"
    status: str = "tes"
    budget: Optional[float] = None  # diário, opcional
    moeda: str = "BRL"   # BRL | USD — moeda em que gasto/faturamento/budget são lançados
    gasto: float = 0.0
    vendas: int = 0
    faturamento: float = 0.0
    observacao: str = ""  # aviso/nota livre (ex.: reprovação, pendência)
    criado_em: datetime = Field(default_factory=datetime.utcnow)
    atualizado_em: datetime = Field(default_factory=datetime.utcnow)

    gestor: Optional[User] = Relationship(back_populates="campanhas")
    oferta: Optional[Oferta] = Relationship(back_populates="campanhas")
    dominios: list["Dominio"] = Relationship(
        back_populates="campanha",
        sa_relationship_kwargs={"cascade": "all, delete-orphan"},
    )

    # --- derivados (nunca digitados) ---
    @property
    def lucro(self) -> float:
        return self.faturamento - self.gasto

    @property
    def roas(self) -> float:
        return (self.faturamento / self.gasto) if self.gasto else 0.0

    @property
    def status_info(self) -> tuple[str, str, str]:
        return STATUS.get(self.status, STATUS["tes"])

    @property
    def simbolo(self) -> str:
        return MOEDAS.get(self.moeda, MOEDAS["BRL"])[0]


class Dominio(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    campanha_id: int = Field(foreign_key="campanha.id", index=True)
    url: str
    ativo: bool = True

    campanha: Optional[Campanha] = Relationship(back_populates="dominios")


class Backlog(SQLModel, table=True):
    """Esteira — rascunho de anúncio/oferta a testar (ainda não virou campanha).
    Guarda todas as características de um anúncio padrão pra planejar o teste."""
    id: Optional[int] = Field(default=None, primary_key=True)
    texto: str                    # título / nome da oferta
    plataforma: str = "Google"
    moeda: str = "BRL"
    dominios: str = ""            # texto livre (um por linha) — planejados
    budget: str = ""             # budget planejado (texto flexível: "R$ 100/dia")
    criativo: str = ""           # criativo a usar (descrição / link)
    publico: str = ""            # público / segmentação (país, device, interesses)
    config: str = ""             # configuração (cloaker, tracker, checkout, etc.)
    observacao: str = ""         # detalhes / observações gerais
    nota: str = ""               # legado (não usado no form novo)
    feito: bool = False
    autor: str = ""
    criado_em: datetime = Field(default_factory=datetime.utcnow)

    @property
    def simbolo(self) -> str:
        return MOEDAS.get(self.moeda, MOEDAS["BRL"])[0]


class Config(SQLModel, table=True):
    """Configurações chave-valor (ex.: taxa de câmbio usd_brl)."""
    id: Optional[int] = Field(default=None, primary_key=True)
    chave: str = Field(index=True, unique=True)
    valor: str


class AcaoPendente(SQLModel, table=True):
    """Ação proposta pela IA, aguardando confirmação humana explícita.

    Nada é aplicado enquanto isto existir — o gate de execução é o endpoint
    /assistente/confirmar (código determinístico), NÃO a IA.
    """
    id: Optional[int] = Field(default=None, primary_key=True)
    user_id: int = Field(foreign_key="user.id", index=True)
    tipo: str            # criar_campanha | atualizar_numeros
    payload: str         # JSON dos argumentos JÁ validados
    resumo: str          # o que vai mudar (humano)
    aviso: str = ""      # alerta de atenção
    criado_em: datetime = Field(default_factory=datetime.utcnow)


# --- helpers de apresentação -------------------------------------------------

def roas_cls(r: float) -> str:
    """Classe css do ROAS: verde >=1.8, âmbar >=1, vermelho abaixo."""
    return "g" if r >= 1.8 else ("m" if r >= 1.0 else "b")
