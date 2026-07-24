require('dotenv').config(); // Carrega variáveis do .env
const express = require('express');
const axios = require('axios');
const path = require('path'); // Para servir o HTML
const QRCode = require('qrcode'); // <--- Importar a biblioteca QR Code

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware para parsear JSON no corpo das requisições
app.use(express.json());
// Middleware para servir arquivos estáticos (HTML, CSS, JS do frontend)
app.use(express.static(path.join(__dirname, 'public')));

const SHARKPAY_API_URL = 'https://api.paysharkgateway.com.br/v1/transactions';
const SHARKPAY_SECRET_KEY = process.env.SHARKPAY_SECRET_KEY;

if (!SHARKPAY_SECRET_KEY) {
    console.error("Erro: Chave secreta do SharkPay (SHARKPAY_SECRET_KEY) não definida no .env");
    process.exit(1); // Aborta se a chave não estiver configurada
}

// <<< INÍCIO: Armazenamento em memória para status (Exemplo SIMPLES e VOLÁTIL) >>>
const transactionStatuses = {}; // { "transactionId": "status" }
// <<< FIM: Armazenamento em memória >>>

// Endpoint para criar a transação PIX
app.post('/api/criar-pix', async (req, res) => {
    console.log('Recebido no backend:', req.body);

    const { amount, items, customer } = req.body;

    // Validação básica
    if (!amount || !items || !customer || !customer.name || !customer.document?.number) { // Adicionada validação mínima de customer
        return res.status(400).json({ error: 'Dados incompletos para criar PIX.' });
    }

    // <<< INÍCIO DA CORREÇÃO: Adicionar email padrão se ausente >>>
    const customerPayload = {
        name: customer.name,
        document: customer.document,
        phone: customer.phone, // Inclui telefone se existir
        email: customer.email || `notprovided_${Date.now()}@example.com` // Usa email recebido OU GERA UM ÚNICO
    };
    // <<< FIM DA CORREÇÃO >>>

    // Monta o payload para a API SharkPay
    const payload = {
        amount: parseInt(amount, 10),
        paymentMethod: 'pix',
        items: items,
        customer: customerPayload, // <--- Usa o customerPayload corrigido
        metadata: req.body.metadata ? JSON.stringify(req.body.metadata) : undefined, // <<< Descomentado
        // postbackUrl: 'https://SEU_DOMINIO_PUBLICO/api/sharkpay-postback' // Adicionar quando tiver URL pública
    };

    // Prepara a autenticação Basic
    const credentials = Buffer.from(`${SHARKPAY_SECRET_KEY}:x`).toString('base64');
    const headers = {
        'Authorization': `Basic ${credentials}`,
        'Content-Type': 'application/json'
    };

    try {
        console.log('Enviando para SharkPay:', payload);
        const response = await axios.post(SHARKPAY_API_URL, payload, { headers });
        console.log('Resposta da SharkPay:', response.data);

        // <<< INÍCIO: Armazenar status inicial e preparar resposta >>>
        const transactionId = response.data.id; // Obter ID da transação
        const initialStatus = response.data.status; // Obter status inicial

        if (transactionId) {
             transactionStatuses[transactionId] = initialStatus; // Armazena status inicial
             console.log(`Status inicial para transação ${transactionId}: ${initialStatus}`);
        }

        const { pix } = response.data;
        if (pix && pix.qrcode) {
            // <<< INÍCIO DA MODIFICAÇÃO: Gerar QR Code no backend >>>
            try {
                const qrCodeImageDataUrl = await QRCode.toDataURL(pix.qrcode, {
                    errorCorrectionLevel: 'L', // Nível de correção
                    margin: 2, // Margem pequena
                    width: 200 // Largura desejada da imagem em pixels
                });

                res.json({
                    transactionId: transactionId, // <<< Retornar o ID da transação
                    qrCodeString: pix.qrcode,
                    qrCodeImageDataUrl: qrCodeImageDataUrl,
                    expirationDate: pix.expirationDate
                });

            } catch (qrError) {
                console.error('Erro ao gerar QR Code no backend:', qrError);
                res.status(500).json({ error: 'Erro interno ao gerar imagem do QR Code.' });
            }
            // <<< FIM DA MODIFICAÇÃO >>>
        } else {
            console.error('Resposta da SharkPay não contém dados PIX esperados:', response.data);
            res.status(500).json({ error: 'Erro ao processar resposta do gateway de pagamento.' });
        }
        // <<< FIM: Armazenar status inicial e preparar resposta >>>

    } catch (error) {
        console.error('Erro ao chamar API SharkPay:', error.response ? error.response.data : error.message);
        res.status(error.response?.status || 500).json({
            error: 'Erro ao comunicar com o gateway de pagamento.',
            details: error.response?.data
        });
    }
});

// <<< INÍCIO: Endpoint para receber Postback do SharkPay (Placeholder) >>>
app.post('/api/sharkpay-postback', (req, res) => {
    console.log('Recebido postback do SharkPay:', req.body);
    const { id, status } = req.body?.data || {}; // Acessa os dados dentro de 'data'

    if (id && status) {
        console.log(`Atualizando status para transação ${id}: ${status}`);
        transactionStatuses[id] = status; // Atualiza o status em memória
        res.status(200).send('OK'); // Responde OK para o SharkPay
    } else {
        console.warn('Postback recebido sem id ou status válidos.');
        res.status(400).send('Payload inválido');
    }
});
// <<< FIM: Endpoint Postback >>>

// <<< INÍCIO: Endpoint para verificar Status REAL da Transação (Polling) >>>
app.get('/api/check-status/:transactionId', async (req, res) => {
    const { transactionId } = req.params;

    if (!transactionId || isNaN(parseInt(transactionId))) {
        return res.status(400).json({ error: 'ID da transação inválido.' });
    }

    const sharkpayTransactionUrl = `${SHARKPAY_API_URL}/${transactionId}`;
    const credentials = Buffer.from(`${SHARKPAY_SECRET_KEY}:x`).toString('base64');
    const headers = {
        'Authorization': `Basic ${credentials}`,
        'Accept': 'application/json' // Boa prática adicionar Accept header
    };

    console.log(`Consultando status REAL para ${transactionId} em ${sharkpayTransactionUrl}`);

    try {
        const response = await axios.get(sharkpayTransactionUrl, { headers });
        const currentStatus = response.data?.status;

        if (currentStatus) {
            console.log(`Status REAL recebido para ${transactionId}: ${currentStatus}`);
            // Opcional: Atualizar nosso armazenamento em memória também
            transactionStatuses[transactionId] = currentStatus;
            res.json({ status: currentStatus });
        } else {
            console.warn(`Resposta da SharkPay para ${transactionId} não continha status.`);
            res.status(500).json({ error: 'Resposta inesperada do gateway de pagamento.' });
        }

    } catch (error) {
        console.error(`Erro ao consultar API SharkPay para status da transação ${transactionId}:`, error.response ? error.response.data : error.message);
        if (error.response?.status === 404) {
            res.status(404).json({ error: 'Transação não encontrada no gateway.' });
        } else {
            res.status(error.response?.status || 500).json({
                error: 'Erro ao comunicar com o gateway de pagamento para verificar status.',
                details: error.response?.data
            });
        }
    }
});
// <<< FIM: Endpoint Check Status >>>

// Rota para servir o arquivo HTML principal
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Rota para servir o checkout 02
app.get('/checkout02', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'checkout02.html'));
});

// Rota para servir o checkout 03
app.get('/checkout03', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'checkout03.html'));
});

app.listen(PORT, () => {
    console.log(`Servidor rodando na porta ${PORT}`);
    console.log(`Acesse o checkout em http://localhost:${PORT}`);
}); 