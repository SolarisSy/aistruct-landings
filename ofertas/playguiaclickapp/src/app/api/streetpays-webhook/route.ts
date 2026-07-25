import { NextResponse } from 'next/server';
import { marcarPago, registrarEvento } from '../_paid-store';

// Webhook do StreetPays (chega no notificationUrl passado no create).
// Payload = o objeto de pagamento completo; status pago é "PAID" (maiúsculo).
// externalRef vem ECOADO — é o gclid do clique no Google Ads.
//
// CONVERSÃO: este serviço NÃO põe pixel do Google em página nenhuma. A money é
// cloakada; um gtag/pixel client-side faria o navegador do comprador chamar o
// Google a partir da money — entregando a URL da página cloakada justamente pra
// quem o cloaker esconde. Em vez disso, a venda é gravada numa Google Sheet e
// importada como CONVERSÃO OFFLINE POR GCLID. Todo o tráfego é server-side: o
// Google recebe gclid + valor, e nunca vê a página.
const SHEET_URL = process.env.GADS_SHEET_WEBHOOK_URL || '';
const SHEET_SECRET = process.env.GADS_SHEET_SECRET || '';
const CONVERSION_NAME = process.env.GADS_CONVERSION_NAME || 'Compra';
const TZ_OFFSET = process.env.GADS_TZ_OFFSET || '-03:00';

// gclid real é um token longo e opaco. `pg_<timestamp>` é o fallback que o
// criar-pix gera quando a venda chegou sem clique identificado — essa não deve
// virar conversão (não há o que atribuir).
function ehGclid(ref: string): boolean {
    return !!ref && ref.length >= 20 && !ref.startsWith('pg_') && !/\s/.test(ref);
}

// Google Ads espera "yyyy-MM-dd HH:mm:ss+/-HH:mm" no fuso declarado.
function horaConversao(offset: string): string {
    const sinal = offset.startsWith('-') ? -1 : 1;
    const [h, m] = offset.slice(1).split(':').map(Number);
    const deslocMs = sinal * ((h * 60 + (m || 0)) * 60 * 1000);
    const d = new Date(Date.now() + deslocMs);
    const p = (n: number) => String(n).padStart(2, '0');
    return `${d.getUTCFullYear()}-${p(d.getUTCMonth() + 1)}-${p(d.getUTCDate())} ` +
        `${p(d.getUTCHours())}:${p(d.getUTCMinutes())}:${p(d.getUTCSeconds())}${offset}`;
}

async function enviarConversao(gclid: string, valorReais: string, orderId: string) {
    if (!SHEET_URL) {
        registrarEvento({ tipo: 'conversao_desligada', gclid, orderId });
        return;
    }
    try {
        const resp = await fetch(SHEET_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                secret: SHEET_SECRET,
                gclid,
                conversion_name: CONVERSION_NAME,
                conversion_time: horaConversao(TZ_OFFSET),
                conversion_value: valorReais,
                conversion_currency: 'BRL',
                order_id: orderId,
            }),
        });
        const txt = (await resp.text()).slice(0, 120);
        registrarEvento({ tipo: 'conversao_enviada', gclid, orderId, resposta: txt });
        console.log(`[conversao] gclid=${gclid} valor=${valorReais} -> ${txt}`);
    } catch (e) {
        registrarEvento({ tipo: 'conversao_erro', gclid, orderId, erro: String(e).slice(0, 160) });
        console.error('[conversao] falhou:', e);
    }
}

export async function POST(request: Request) {
    try {
        const data = await request.json();
        const txId = data?.id ? String(data.id) : '';
        const status = String(data?.status || '').toUpperCase();
        const externalRef = String(data?.externalRef || '');
        const amount = Number(data?.amount || 0);

        console.log(
            `[streetpays-webhook] tx=${txId} status=${status} ` +
            `externalRef=${externalRef} amount=${amount}`
        );

        if (status === 'PAID' && txId) {
            marcarPago(txId);
            if (ehGclid(externalRef)) {
                await enviarConversao(externalRef, (amount / 100).toFixed(2), txId);
            } else {
                registrarEvento({ tipo: 'pago_sem_gclid', gclid: externalRef, orderId: txId });
            }
        }

        return NextResponse.json({ received: true });
    } catch (error) {
        console.error('[streetpays-webhook] payload inválido:', error);
        // 200 de propósito: erro nosso não deve fazer o gateway reenviar em loop.
        return NextResponse.json({ received: false }, { status: 200 });
    }
}
