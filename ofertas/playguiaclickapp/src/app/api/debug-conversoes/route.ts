import { NextResponse } from 'next/server';
import { eventosRecentes, PAID_TX } from '../_paid-store';

// Diagnóstico da marcação: mostra se o gclid está chegando e se a conversão saiu.
// Protegido por token porque revela gclid (dado da campanha) — sem GADS_DEBUG_TOKEN
// setado, a rota fica fechada.
const TOKEN = process.env.GADS_DEBUG_TOKEN || '';

export async function GET(request: Request) {
    const url = new URL(request.url);
    if (!TOKEN || url.searchParams.get('token') !== TOKEN) {
        return NextResponse.json({ error: 'não autorizado' }, { status: 404 });
    }

    const eventos = eventosRecentes();
    return NextResponse.json({
        conversao_ligada: !!process.env.GADS_SHEET_WEBHOOK_URL,
        conversion_name: process.env.GADS_CONVERSION_NAME || 'Compra',
        pagos_em_memoria: PAID_TX.size,
        contagem: {
            enviadas: eventos.filter((e) => e.tipo === 'conversao_enviada').length,
            erros: eventos.filter((e) => e.tipo === 'conversao_erro').length,
            sem_gclid: eventos.filter((e) => e.tipo === 'pago_sem_gclid').length,
            desligada: eventos.filter((e) => e.tipo === 'conversao_desligada').length,
        },
        recentes: eventos,
    });
}
