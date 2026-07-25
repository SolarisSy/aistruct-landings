// Estado compartilhado de transações pagas.
// O webhook popula; /api/status consulta aqui primeiro (O(1)) antes de bater no
// gateway — deixa a confirmação no checkout em até 1 ciclo de poll (3s) sem gastar
// chamada de API. Em memória de propósito: é cache, não fonte da verdade (o
// fallback continua sendo o GET /payment/:id).
const g = globalThis as unknown as { __paidTx?: Set<string> };

if (!g.__paidTx) {
    g.__paidTx = new Set<string>();
}

export const PAID_TX: Set<string> = g.__paidTx;

export function marcarPago(txId: string): void {
    if (txId) {
        PAID_TX.add(String(txId));
    }
}

export function estaPago(txId: string): boolean {
    return PAID_TX.has(String(txId));
}
