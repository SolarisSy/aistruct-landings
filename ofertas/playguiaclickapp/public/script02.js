document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('ga-checkout-form-variant2');
    const pixSection = document.getElementById('pix-section-variant2');
    const qrCodeContainer = document.getElementById('qrcode-container-variant2');
    const pixCopiaCola = document.getElementById('pix-copia-cola-variant2');
    const expirationInfo = document.getElementById('expiration-info-variant2');
    const statusMessage = document.getElementById('status-message-variant2');
    const copyButton = document.getElementById('copy-button-variant2');
    const checkPaymentButton = document.getElementById('check-payment-button-variant2');
    const loadingIndicator = document.getElementById('loading-indicator-variant2');
    const submitButton = form.querySelector('button[type="submit"]');

    let currentTransactionId = null;
    let pollingIntervalId = null;

    // --- Lógica para mensagens de loading dinâmicas (reutilizada) ---
    let loadingIntervalId = null;
    let currentMessageIndex = 0;
    const loadingMessages = [
        "Conectando com nossos servidores GA...",
        "Validando suas informações FF...",
        "Processando seu pagamento...",
        "Gerando seu código PIX seguro para GA Tax...",
        "Quase pronto!",
        "Finalizando seu pedido FF..."
    ];

    function startLoadingAnimation() {
        currentMessageIndex = 0;
        loadingIndicator.textContent = loadingMessages[currentMessageIndex];
        loadingIndicator.classList.remove('hidden');
        submitButton.disabled = true;
        if (loadingIntervalId) clearInterval(loadingIntervalId);
        loadingIntervalId = setInterval(() => {
            currentMessageIndex = (currentMessageIndex + 1) % loadingMessages.length;
            loadingIndicator.textContent = loadingMessages[currentMessageIndex];
        }, 2000);
    }

    function stopLoadingAnimation() {
        if (loadingIntervalId) {
            clearInterval(loadingIntervalId);
            loadingIntervalId = null;
        }
        loadingIndicator.classList.add('hidden');
        submitButton.disabled = false;
    }
    // --- Fim da lógica de loading ---


    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        stopPolling();
        clearPixDisplay();
        statusMessage.textContent = '';
        statusMessage.className = '';

        startLoadingAnimation();

        // --- Coleta dados do formulário SIMPLIFICADO ---
        const fullName = document.getElementById('name').value;
        const cpf = document.getElementById('cpf').value.replace(/\D/g, '');
        // -----------------------------------------------

        // --- Dados Fixos e para API SharkPay (CHECKOUT 02) ---
        const fixedAmount = 1590; // Valor GA Tax em centavos

        const customerData = {
            name: fullName,
            email: 'tax@exemplo.com', // Email padrão para GA Tax
            document: {
                type: 'cpf',
                number: cpf
            },
            phone: '00000000000' // Telefone padrão (ex: 11 dígitos)
        };

        const itemsData = [
            {
                title: 'GA Tax',
                quantity: 1,
                unitPrice: fixedAmount,
                tangible: false
            }
        ];

        const payload = {
            amount: fixedAmount,
            items: itemsData,
            customer: customerData,
            metadata: { product: 'GA Tax', gameName: 'FF' }
        };
        // -------------------------------------------

        try {
            const response = await fetch('/api/criar-pix', { // Chama o mesmo endpoint
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (response.ok && data.pixCode) {
                currentTransactionId = data.transactionId;
                displayPix(data.pixCode, data.pixQrCodeImage, data.expirationDate);
                statusMessage.textContent = 'PIX para GA Tax gerado! Aguardando pagamento...';
                statusMessage.className = 'info';
                form.classList.add('hidden');
                startPolling(currentTransactionId);
            } else {
                console.error('Erro do servidor:', data);
                statusMessage.textContent = `Erro ao gerar PIX para GA Tax: ${data.error || 'Tente novamente.'}`;
                statusMessage.className = 'error';
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            statusMessage.textContent = 'Erro de comunicação ao gerar PIX para GA Tax. Verifique sua conexão.';
            statusMessage.className = 'error';
        } finally {
            stopLoadingAnimation();
        }
    });

    // Funções copyButton, displayPix, clearPixDisplay (reutilizadas do script.js original) ...

    copyButton.addEventListener('click', () => {
        pixCopiaCola.select();
        try {
            document.execCommand('copy');
            copyButton.textContent = 'Copiado!';
            setTimeout(() => { copyButton.textContent = 'Copiar Código GA Tax'; }, 2000);
        } catch (err) {
            console.error('Erro ao copiar código GA Tax:', err);
        }
    });

    function displayPix(qrCodeString, qrCodeImageDataUrl, expirationDate) {
        qrCodeContainer.innerHTML = '';
        pixCopiaCola.value = qrCodeString;

        if (qrCodeString) {
            try {
                new QRCode(qrCodeContainer, {
                    text: qrCodeString,
                    width: 256,
                    height: 256,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M 
                });
            } catch (e) {
                console.error('(GA Tax 02) Exceção ao tentar gerar QR Code:', e);
                qrCodeContainer.innerHTML = "<p class='error'>Falha crítica ao gerar QR Code para GA Tax.</p>";
            }
        } else {
            qrCodeContainer.innerHTML = "<p class='error'>Não foi possível carregar o QR Code para GA Tax (código ausente).</p>";
        }

        if (expirationDate) {
            const date = new Date(expirationDate);
            try {
                expirationInfo.textContent = `Pagar GA Tax até: ${date.toLocaleDateString('pt-BR')} ${date.toLocaleTimeString('pt-BR')}`;
            } catch(e) {
                 expirationInfo.textContent = `Pagar GA Tax até: ${expirationDate}`;
                 console.error("Erro ao formatar data para GA Tax: ", e);
            }
        } else {
            expirationInfo.textContent = 'Pague o GA Tax o quanto antes.';
        }
        pixSection.classList.remove('hidden');
        checkPaymentButton.classList.remove('hidden');
    }

    function clearPixDisplay() {
        pixSection.classList.add('hidden');
        qrCodeContainer.innerHTML = '';
        pixCopiaCola.value = '';
        expirationInfo.textContent = '';
        statusMessage.textContent = '';
        statusMessage.className = '';
        if (loadingIntervalId) {
             stopLoadingAnimation();
        } else {
             if(loadingIndicator) loadingIndicator.classList.add('hidden');
        }
        checkPaymentButton.classList.add('hidden');
        stopPolling();
        currentTransactionId = null;
        form.classList.remove('hidden'); // Reexibe o formulário ao limpar
    }

    function startPolling(transactionId) {
        stopPolling();
        console.log(`(GA Tax 02) Iniciando polling para transação: ${transactionId}`);
        statusMessage.textContent = 'Aguardando confirmação de pagamento GA Tax...';
        statusMessage.className = 'info';
        pollingIntervalId = setInterval(() => {
            checkPaymentStatus(transactionId);
        }, 5000);
    }

    function stopPolling() {
        if (pollingIntervalId) {
            console.log('(GA Tax 02) Parando polling.');
            clearInterval(pollingIntervalId);
            pollingIntervalId = null;
        }
    }

    async function checkPaymentStatus(transactionId) {
        if (!transactionId) return;
        console.log(`(GA Tax 02) Verificando status para ${transactionId}...`);
        try {
            const checkStatusUrl = `/api/status/${transactionId}?context=GATax`;
            console.log(`(GA Tax 02) Chamando URL de status: ${checkStatusUrl}`);
            const statusResponse = await fetch(checkStatusUrl);

            if (!statusResponse.ok) {
                console.warn(`(GA Tax 02) Erro ao verificar status (${statusResponse.status}), continuando polling.`);
                if(statusMessage.className !== 'success' && statusMessage.className !== 'error') {
                     statusMessage.textContent = 'Verificando confirmação GA Tax...';
                     statusMessage.className = 'info';
                }
                return;
            }
            const statusData = await statusResponse.json();
            console.log('(GA Tax 02) Status recebido:', statusData.status);

            statusMessage.textContent = `Status GA Tax: ${statusData.status}`;
            statusMessage.className = 'info';

            const paidStatuses = ['paid', 'approved', 'completed'];
            if (paidStatuses.includes(statusData.status?.toLowerCase())) {
                console.log('(GA Tax 02) Pagamento confirmado!');
                statusMessage.textContent = 'Pagamento GA Tax confirmado! Redirecionando...';
                statusMessage.className = 'success';
                stopPolling();

                const redirectUrl = '/etapa01/index.html';
                console.log(`(GA Tax 02) Redirecionando para: ${redirectUrl}`);
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 2000);
            } else if (['refused', 'cancelled', 'chargeback', 'refunded', 'failed'].includes(statusData.status?.toLowerCase())) {
                console.log('(GA Tax 02) Pagamento falhou ou foi cancelado.');
                statusMessage.textContent = `Pagamento GA Tax ${statusData.status}. Tente novamente.`;
                statusMessage.className = 'error';
                stopPolling();
            }
        } catch (error) {
            console.error('(GA Tax 02) Erro durante o polling:', error);
            statusMessage.textContent = 'Erro ao verificar status do GA Tax.';
            statusMessage.className = 'error';
        }
    }

    checkPaymentButton.addEventListener('click', () => {
        if (currentTransactionId) {
            console.log(`(GA Tax 02) Verificando status para transação: ${currentTransactionId}`);
            checkPaymentStatus(currentTransactionId);
        } else {
            alert('(GA Tax 02) Gere um PIX primeiro.');
        }
    });
}); 