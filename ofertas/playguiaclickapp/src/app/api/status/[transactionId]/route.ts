import { NextResponse } from 'next/server';
import axios, { isAxiosError } from 'axios';

const BLACKCAT_API_URL = 'https://api.blackcatpagamentos.com/v1/transactions';
const BLACKCAT_PUBLIC_KEY = process.env.PUBLIC_KEY;
const BLACKCAT_SECRET_KEY = process.env.SECRET_KEY;

export async function GET(request: Request, context: any) {
    if (!BLACKCAT_PUBLIC_KEY || !BLACKCAT_SECRET_KEY) {
        console.error("Erro: Chaves da BlackCat (PUBLIC_KEY e SECRET_KEY) não definidas.");
        return NextResponse.json({ error: "Configuração do servidor incompleta." }, { status: 500 });
    }
    
    const { transactionId } = context.params;

    if (!transactionId) {
        return NextResponse.json({ error: 'ID da transação é obrigatório.' }, { status: 400 });
    }

    try {
        // BlackCat usa PUBLIC_KEY:SECRET_KEY para Basic Auth
        const basicAuth = Buffer.from(`${BLACKCAT_PUBLIC_KEY}:${BLACKCAT_SECRET_KEY}`).toString('base64');
        const blackcatStatusResponse = await axios.get(`${BLACKCAT_API_URL}/${transactionId}`, {
            headers: { 'Authorization': `Basic ${basicAuth}` }
        });

        const responseData = blackcatStatusResponse.data;
        if (responseData && responseData.status) {
            return NextResponse.json({
                transactionId: responseData.id,
                status: responseData.status,
                amount: responseData.amount, // A API da BlackCat retorna em centavos
                paidAmount: responseData.paidAmount,
                paymentMethod: responseData.paymentMethod,
                createdAt: responseData.createdAt,
                paidAt: responseData.paidAt,
            });
        } else {
            console.error(`Resposta inesperada da BlackCat ao buscar status para ${transactionId}:`, responseData);
            return NextResponse.json({ error: 'Resposta inesperada do gateway ao buscar status.' }, { status: 500 });
        }
    } catch (error) {
        if (isAxiosError(error)) {
            console.error(`Erro ao buscar status da BlackCat para ${transactionId}:`, error.response ? error.response.data : error.message);
        
            const status = error.response?.status || 500;
            const responseData = error.response?.data as { message?: string, errors?: unknown };
            const message = responseData?.message || 'Erro ao buscar status da transação no gateway.';
            const details = responseData?.errors || responseData || error.message;
            
            if (status === 404) {
                return NextResponse.json({ error: 'Transação não encontrada no gateway de pagamento.', details }, { status: 404 });
            }
            return NextResponse.json({ error: message, details }, { status });
        }
        
        console.error(`Erro desconhecido ao buscar status para ${transactionId}:`, error);
        return NextResponse.json({ error: 'Ocorreu um erro inesperado.' }, { status: 500 });
    }
} 