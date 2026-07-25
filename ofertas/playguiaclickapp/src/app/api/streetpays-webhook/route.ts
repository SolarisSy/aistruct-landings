import { NextResponse } from 'next/server';
import { marcarPago } from '../_paid-store';

// Webhook do StreetPays (chega no notificationUrl passado no create).
// Payload = o objeto de pagamento completo; status pago é "PAID" (maiúsculo).
// externalRef vem ECOADO — é o clickid, usado depois pro postback de conversão.
export async function POST(request: Request) {
    try {
        const data = await request.json();
        const txId = data?.id ? String(data.id) : '';
        const status = String(data?.status || '').toUpperCase();
        const externalRef = data?.externalRef ?? null;

        console.log(
            `[streetpays-webhook] tx=${txId} status=${status} ` +
            `externalRef=${externalRef} amount=${data?.amount}`
        );

        if (status === 'PAID' && txId) {
            marcarPago(txId);
            // TODO(tracker): quando houver RedTrack/UTMify nesta oferta, disparar aqui
            // o postback com clickid=externalRef e sum=amount/100.
        }

        return NextResponse.json({ received: true });
    } catch (error) {
        console.error('[streetpays-webhook] payload inválido:', error);
        // 200 de propósito: erro nosso não deve fazer o gateway reenviar em loop.
        return NextResponse.json({ received: false }, { status: 200 });
    }
}
