import { NextResponse } from 'next/server';
import axios, { isAxiosError } from 'axios';

const BLACKCAT_API_URL = 'https://api.blackcatpagamentos.com/v1/transactions';
// BlackCat usa PUBLIC_KEY e SECRET_KEY para autenticação Basic Auth
const BLACKCAT_PUBLIC_KEY = process.env.PUBLIC_KEY;
const BLACKCAT_SECRET_KEY = process.env.SECRET_KEY; 

interface Item {
    title: string;
    quantity: number;
    unitPrice: number;
    tangible?: boolean;
}

export async function POST(request: Request) {
    if (!BLACKCAT_PUBLIC_KEY || !BLACKCAT_SECRET_KEY) {
        console.error("Erro: Chaves da BlackCat (PUBLIC_KEY e SECRET_KEY) não definidas.");
        return NextResponse.json({ error: "Configuração do servidor incompleta." }, { status: 500 });
    }

    try {
        // Detecta o domínio dinamicamente a partir dos headers da requisição
        const headers = request.headers;
        const protocol = headers.get('x-forwarded-proto') || 'http';
        const host = headers.get('host') || 'localhost:3000';
        const publicServerUrl = `${protocol}://${host}`;

        const body = await request.json();
        console.log('Recebido no endpoint /api/criar-pix:', body);

        const { amount, items, customer, metadata } = body;

        if (!amount || !items || !customer || !customer.name || !customer.document?.number) {
            return NextResponse.json({ error: 'Dados incompletos para criar PIX.' }, { status: 400 });
        }

        const pixData = {
            paymentMethod: 'pix',
            amount: amount,
            items: items.map((item: Item) => ({
                title: item.title,
                quantity: item.quantity,
                unitPrice: item.unitPrice,
                tangible: item.tangible || false
            })),
            customer: {
                name: customer.name,
                document: customer.document,
                email: customer.email,
                phone: customer.phone
            },
            // Usa a URL detectada dinamicamente para o postback
            postbackUrl: `${publicServerUrl}/api/blackcat-webhook`,
            metadata: JSON.stringify({
                ...metadata,
                platform: "FFCheckoutGA-NextJS"
            })
        };

        console.log('Payload para BlackCat:', JSON.stringify(pixData, null, 2));
        
        // BlackCat usa PUBLIC_KEY:SECRET_KEY para Basic Auth
        const basicAuth = Buffer.from(`${BLACKCAT_PUBLIC_KEY}:${BLACKCAT_SECRET_KEY}`).toString('base64');
        const blackcatResponse = await axios.post(BLACKCAT_API_URL, pixData, {
            headers: {
                'Authorization': `Basic ${basicAuth}`,
                'Content-Type': 'application/json'
            }
        });

        console.log('Resposta da BlackCat:', blackcatResponse.data);

        if (blackcatResponse.data && blackcatResponse.data.id && blackcatResponse.data.pix && blackcatResponse.data.pix.qrcode) {
            return NextResponse.json({
                message: 'PIX criado com sucesso!',
                transactionId: blackcatResponse.data.id.toString(),
                pixCode: blackcatResponse.data.pix.qrcode,
                status: blackcatResponse.data.status || 'pending_payment'
            });
        } else {
            console.error('Resposta inesperada da BlackCat (campos esperados ausentes):', blackcatResponse.data);
            return NextResponse.json({ 
                error: 'Resposta inesperada da BlackCat ao criar PIX (campos ausentes).',
                details: blackcatResponse.data
            }, { status: 500 });
        }
    } catch (error) {
        if (isAxiosError(error)) {
            console.error('Erro ao criar PIX na BlackCat:', error.response ? error.response.data : error.message);
        
            let errorMessage = 'Erro ao comunicar com o gateway de pagamento.';
            let errorDetails: unknown = error.message;
            let blackcatErrorData = null;
            const status = error.response?.status || 500;

            if (error.response && error.response.data) {
                const responseData = error.response.data as { message?: string, errors?: { field: string, message: string }[] };
                blackcatErrorData = responseData;
                errorDetails = responseData.message || JSON.stringify(responseData.errors || responseData);
                if (responseData.errors && Array.isArray(responseData.errors) && responseData.errors.length > 0) {
                     const firstError = responseData.errors[0];
                     if (firstError.field && firstError.message) {
                         errorMessage = `Erro no campo ${firstError.field}: ${firstError.message}`;
                     } else if (firstError.message) {
                         errorMessage = firstError.message;
                     }
                } else if (responseData.message) {
                   errorMessage = responseData.message;
                }
            }
            
            return NextResponse.json({
                error: errorMessage,
                details: errorDetails,
                blackcatError: blackcatErrorData
            }, { status });
        }
        
        console.error('Erro desconhecido:', error);
        return NextResponse.json({ error: 'Ocorreu um erro inesperado.' }, { status: 500 });
    }
}