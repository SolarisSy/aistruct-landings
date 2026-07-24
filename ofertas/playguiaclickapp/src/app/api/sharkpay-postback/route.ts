import { NextResponse } from 'next/server';

export async function POST(request: Request) {
    try {
        const postbackData = await request.json();
        console.log('Postback recebido da SharkPay:', JSON.stringify(postbackData, null, 2));

        // Validação básica da estrutura do postback da SharkPay
        if (!postbackData || typeof postbackData.data !== 'object' || postbackData.data === null || !postbackData.data.id || !postbackData.data.status || !postbackData.objectId) {
            console.warn('Postback inválido ou incompleto recebido.', postbackData);
            return NextResponse.json({ error: 'Postback inválido ou estrutura de dados incorreta.' }, { status: 400 });
        }

        const transactionIdFromPostback = postbackData.objectId.toString();
        const newStatus = postbackData.data.status;

        console.log(`Postback para Transação ID: ${transactionIdFromPostback}, Novo Status: ${newStatus}`);
        
        // Aqui você implementaria a lógica de negócios com base no postback,
        // como atualizar um banco de dados, notificar sistemas, etc.
        // if (newStatus === 'paid') {
        //     console.log(`Pagamento CONFIRMADO para transação ${transactionIdFromPostback}.`);
        //     // Chamar função para liberar produto, enviar email, etc.
        // }

        // Responda 200 OK para a SharkPay para confirmar o recebimento do postback.
        return NextResponse.json({ message: 'Postback recebido e logado com sucesso.' }, { status: 200 });

    } catch (error) {
        if (error instanceof Error) {
            console.error('Erro ao processar postback da SharkPay:', error.message);
            return NextResponse.json({ error: 'Erro interno ao processar postback.', details: error.message }, { status: 500 });
        }
        console.error('Erro desconhecido ao processar postback:', error);
        return NextResponse.json({ error: 'Erro interno desconhecido ao processar postback.' }, { status: 500 });
    }
} 