import { NextResponse } from 'next/server';
import axios, { isAxiosError } from 'axios';
import { estaPago, marcarPago } from '../../_paid-store';

const STREETPAYS_API = 'https://api.streetpays.com.br/v1';
const API_KEY = process.env.STREETPAYS_API_KEY;

// O checkout (public/script*.js) compara `data.status === 'paid'` em minúsculo.
// O StreetPays devolve 'PAID'. Normalizamos aqui pra não mexer no front.
function normaliza(status: string): string {
    const s = String(status || '').toUpperCase();
    if (s === 'PAID') return 'paid';
    if (s === 'REFUNDED') return 'refunded';
    if (s === 'PENDING') return 'pending_payment';
    return s.toLowerCase();
}

export async function GET(request: Request, context: any) {
    if (!API_KEY) {
        console.error('Erro: STREETPAYS_API_KEY não definida.');
        return NextResponse.json({ error: 'Configuração do servidor incompleta.' }, { status: 500 });
    }

    const { transactionId } = context.params;
    if (!transactionId) {
        return NextResponse.json({ error: 'ID da transação é obrigatório.' }, { status: 400 });
    }

    // Atalho: o webhook já confirmou este pagamento.
    if (estaPago(transactionId)) {
        return NextResponse.json({ transactionId, status: 'paid', paid: true, source: 'webhook' });
    }

    try {
        const resp = await axios.get(`${STREETPAYS_API}/payment/${transactionId}`, {
            headers: { Authorization: `Bearer ${API_KEY}`, accept: 'application/json' },
            timeout: 30000,
        });

        const d = resp.data;
        if (d?.status) {
            const status = normaliza(d.status);
            if (status === 'paid') {
                marcarPago(transactionId);
            }
            return NextResponse.json({
                transactionId: String(d.id ?? transactionId),
                status,
                paid: status === 'paid',
                amount: d.amount,
                paymentMethod: d.method,
                createdAt: d.createdAt,
                paidAt: d.paidAt,
                source: 'gateway',
            });
        }

        console.error(`Resposta inesperada do StreetPays para ${transactionId}:`, d);
        return NextResponse.json(
            { error: 'Resposta inesperada do gateway ao buscar status.' }, { status: 500 });
    } catch (error) {
        if (isAxiosError(error)) {
            const status = error.response?.status || 500;
            const data = error.response?.data as { message?: string; errors?: unknown } | undefined;
            console.error(`Erro ao buscar status ${transactionId}:`, data || error.message);
            if (status === 404) {
                return NextResponse.json(
                    { error: 'Transação não encontrada no gateway.', details: data }, { status: 404 });
            }
            return NextResponse.json(
                {
                    error: data?.message || 'Erro ao consultar o gateway.',
                    details: data?.errors ?? data ?? error.message,
                },
                { status });
        }
        console.error(`Erro desconhecido ao buscar status ${transactionId}:`, error);
        return NextResponse.json({ error: 'Ocorreu um erro inesperado.' }, { status: 500 });
    }
}
