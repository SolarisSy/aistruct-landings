// Estado compartilhado entre o webhook e as rotas de status/debug.
//
// PAID_TX: o webhook popula; /api/status consulta aqui primeiro (O(1)) antes de
// bater no gateway — deixa a confirmação no checkout em até 1 ciclo de poll (3s)
// sem gastar chamada de API. Em memória de propósito: é cache, não fonte da
// verdade (o fallback continua sendo o GET /payment/:id).
//
// EVENTOS: buffer curto do que aconteceu com a conversão, pra conferir se o
// gclid está chegando de verdade sem precisar caçar log de container.
const g = globalThis as unknown as {
    __paidTx?: Set<string>;
    __eventos?: EventoConversao[];
};

export interface EventoConversao {
    tipo: 'conversao_enviada' | 'conversao_erro' | 'conversao_desligada' | 'pago_sem_gclid';
    gclid?: string;
    orderId?: string;
    resposta?: string;
    erro?: string;
    quando?: string;
}

if (!g.__paidTx) g.__paidTx = new Set<string>();
if (!g.__eventos) g.__eventos = [];

export const PAID_TX: Set<string> = g.__paidTx;
const EVENTOS: EventoConversao[] = g.__eventos;

const MAX_EVENTOS = 50;

export function marcarPago(txId: string): void {
    if (txId) PAID_TX.add(String(txId));
}

export function estaPago(txId: string): boolean {
    return PAID_TX.has(String(txId));
}

export function registrarEvento(ev: EventoConversao): void {
    EVENTOS.unshift({ ...ev, quando: new Date().toISOString() });
    if (EVENTOS.length > MAX_EVENTOS) EVENTOS.length = MAX_EVENTOS;
}

export function eventosRecentes(): EventoConversao[] {
    return EVENTOS;
}
