import { NextResponse } from 'next/server';

export async function POST(request: Request) {
    try {
        const webhookData = await request.json();
        console.log('Webhook recebido da BlackCat:', JSON.stringify(webhookData, null, 2));

        // Validação básica da estrutura do webhook da BlackCat
        // A estrutura exata depende da documentação da BlackCat, ajuste conforme necessário
        if (!webhookData || typeof webhookData.data !== 'object' || webhookData.data === null || !webhookData.data.id || !webhookData.data.status || !webhookData.objectId) {
            console.warn('Webhook inválido ou incompleto recebido.', webhookData);
            return NextResponse.json({ error: 'Webhook inválido ou estrutura de dados incorreta.' }, { status: 400 });
        }

        const transactionIdFromWebhook = webhookData.objectId.toString();
        const newStatus = webhookData.data.status;

        console.log(`Webhook para Transação ID: ${transactionIdFromWebhook}, Novo Status: ${newStatus}`);
        
        // Aqui você implementaria a lógica de negócios com base no webhook,
        // como atualizar um banco de dados, notificar sistemas, etc.
        // if (newStatus === 'paid') {
        //     console.log(`Pagamento CONFIRMADO para transação ${transactionIdFromWebhook}.`);
        //     // Chamar função para liberar produto, enviar email, etc.
        // }

        // Responda 200 OK para a BlackCat para confirmar o recebimento do webhook.
        return NextResponse.json({ message: 'Webhook recebido e logado com sucesso.' }, { status: 200 });

    } catch (error) {
        if (error instanceof Error) {
            console.error('Erro ao processar webhook da BlackCat:', error.message);
            return NextResponse.json({ error: 'Erro interno ao processar webhook.', details: error.message }, { status: 500 });
        }
        console.error('Erro desconhecido ao processar webhook:', error);
        return NextResponse.json({ error: 'Erro interno desconhecido ao processar webhook.' }, { status: 500 });
    }
}

