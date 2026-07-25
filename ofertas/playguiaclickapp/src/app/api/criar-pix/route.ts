import { NextResponse } from 'next/server';
import axios, { isAxiosError } from 'axios';

// StreetPays — gateway PIX (conta Solas, perfil solaris).
// Valores em CENTAVOS. Auth = Bearer da "Chave de API" (Financeiro -> Integrações).
const STREETPAYS_API = 'https://api.streetpays.com.br/v1';
const API_KEY = process.env.STREETPAYS_API_KEY;

// O que aparece no extrato do comprador = items[].name (+ description).
const PRODUCT_NAME = process.env.PRODUCT_NAME || 'Pacote de créditos';

interface Item {
    title?: string;
    name?: string;
    quantity: number;
    unitPrice: number;
}

// StreetPays recusa cobrança abaixo das taxas acumuladas (fixa ~R$2,99 + ~3%).
const MIN_AMOUNT = 500;

export async function POST(request: Request) {
    if (!API_KEY) {
        console.error('Erro: STREETPAYS_API_KEY não definida.');
        return NextResponse.json({ error: 'Configuração do servidor incompleta.' }, { status: 500 });
    }

    try {
        const headers = request.headers;
        const protocol = headers.get('x-forwarded-proto') || 'https';
        const host = headers.get('host') || 'localhost:3000';
        const publicServerUrl = `${protocol}://${host}`;

        const body = await request.json();
        const { amount, items, customer, metadata } = body;

        if (!amount || !items || !customer?.name || !customer?.document?.number) {
            return NextResponse.json({ error: 'Dados incompletos para criar PIX.' }, { status: 400 });
        }
        if (Number(amount) < MIN_AMOUNT) {
            return NextResponse.json(
                { error: 'Valor abaixo do mínimo aceito pelo gateway.' }, { status: 400 });
        }

        // externalRef é ECOADO no webhook — é por aqui que o clickid volta pra atribuição.
        const externalRef = String(
            metadata?.clickid || metadata?.src || metadata?.rtkcid || `pg_${Date.now()}`
        );

        const payload = {
            amount: Number(amount),
            currency: 'BRL',
            method: 'PIX',
            description: PRODUCT_NAME,
            externalRef,
            notificationUrl: `${publicServerUrl}/api/streetpays-webhook`,
            payer: {
                name: customer.name,
                taxId: String(customer.document.number).replace(/\D/g, ''),
                email: customer.email || undefined,
                phone: customer.phone ? String(customer.phone).replace(/\D/g, '') : undefined,
            },
            // DIGITAL: PHYSICAL exigiria data.delivery (endereço) e o create falharia.
            items: (items as Item[]).map((item) => ({
                quantity: item.quantity,
                name: item.name || item.title || PRODUCT_NAME,
                price: item.unitPrice,
                type: 'DIGITAL' as const,
            })),
        };

        const resp = await axios.post(`${STREETPAYS_API}/payment`, payload, {
            headers: {
                Authorization: `Bearer ${API_KEY}`,
                'Content-Type': 'application/json',
                accept: 'application/json',
            },
            timeout: 30000,
        });

        // PIX copia-e-cola do StreetPays = data.copypaste (não pix.qrcode como na BlackCat).
        const copypaste = resp.data?.data?.copypaste;
        if (resp.data?.id && copypaste) {
            return NextResponse.json({
                message: 'PIX criado com sucesso!',
                transactionId: String(resp.data.id),
                pixCode: copypaste,
                status: 'pending_payment',
            });
        }

        console.error('Resposta inesperada do StreetPays:', resp.data);
        return NextResponse.json(
            { error: 'Resposta inesperada do gateway ao criar PIX.', details: resp.data },
            { status: 500 });
    } catch (error) {
        if (isAxiosError(error)) {
            const data = error.response?.data as { message?: string; error?: string } | undefined;
            console.error('Erro ao criar PIX no StreetPays:', data || error.message);
            return NextResponse.json(
                {
                    error: data?.message || data?.error || 'Erro ao comunicar com o gateway.',
                    details: data ?? error.message,
                },
                { status: error.response?.status || 500 });
        }
        console.error('Erro desconhecido:', error);
        return NextResponse.json({ error: 'Ocorreu um erro inesperado.' }, { status: 500 });
    }
}
