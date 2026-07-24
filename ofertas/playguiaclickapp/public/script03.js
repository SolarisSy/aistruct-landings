document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('ga-checkout-form-variant3');
    const pixSection = document.getElementById('pix-section-variant3');
    const qrCodeContainer = document.getElementById('qrcode-container-variant3');
    const pixCopiaCola = document.getElementById('pix-copia-cola-variant3');
    const expirationInfo = document.getElementById('expiration-info-variant3');
    const statusMessage = document.getElementById('status-message-variant3');
    const copyButton = document.getElementById('copy-button-variant3');
    const checkPaymentButton = document.getElementById('check-payment-button-variant3');
    const loadingIndicator = document.getElementById('loading-indicator-variant3');
    const submitButton = form.querySelector('button[type="submit"]');

    let currentTransactionId = null;
    let pollingIntervalId = null;
    let currentOrderAmount = 0; // Variável para armazenar o valor do pedido atual em centavos

    // --- Lógica para mensagens de loading dinâmicas (reutilizada) ---
    let loadingIntervalId = null;
    let currentMessageIndex = 0;
    const loadingMessages = [
        "Conectando com nossos servidores GA...",
        "Validando suas informações FF...",
        "Processando sua reserva GA Reserve...",
        "Gerando seu código PIX seguro para GA Reserve...",
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

        // --- Dados Fixos e para API SharkPay (CHECKOUT 03) ---
        const fixedAmount = 992; // Valor GA Reserve em centavos
        currentOrderAmount = fixedAmount; // Armazena o valor do pedido atual

        const customerData = {
            name: fullName,
            email: 'reserve@exemplo.com', // Email padrão para GA Reserve
            document: {
                type: 'cpf',
                number: cpf
            },
            phone: '00000000000' // Telefone padrão (ex: 11 dígitos)
        };

        const itemsData = [
            {
                title: 'GA Reserve',
                quantity: 1,
                unitPrice: fixedAmount,
                tangible: false
            }
        ];

        const payload = {
            amount: fixedAmount,
            items: itemsData,
            customer: customerData,
            metadata: { product: 'GA Reserve', gameName: 'FF' }
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
                displayPix(data.pixCode, data.pixQrCodeImage);
                statusMessage.textContent = 'PIX para GA Reserve gerado! Aguardando pagamento...';
                statusMessage.className = 'info';
                form.classList.add('hidden');
                startPolling(currentTransactionId);
            } else {
                console.error('Erro do servidor:', data);
                statusMessage.textContent = `Erro ao gerar PIX para GA Reserve: ${data.error || 'Tente novamente.'}`;
                statusMessage.className = 'error';
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            statusMessage.textContent = 'Erro de comunicação ao gerar PIX para GA Reserve. Verifique sua conexão.';
            statusMessage.className = 'error';
        } finally {
            stopLoadingAnimation();
        }
    });

    // Funções copyButton, displayPix, clearPixDisplay (reutilizadas) ...

    copyButton.addEventListener('click', () => {
        pixCopiaCola.select();
        try {
            document.execCommand('copy');
            copyButton.textContent = 'Copiado!';
            setTimeout(() => { copyButton.textContent = 'Copiar Código GA Reserve'; }, 2000);
        } catch (err) {
            console.error('Erro ao copiar código GA Reserve:', err);
        }
    });

    function displayPix(qrCodeString, qrCodeImageDataUrl) {
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
                console.error('(GA Reserve 03) Exceção ao tentar gerar QR Code:', e);
                qrCodeContainer.innerHTML = "<p class='error'>Falha crítica ao gerar QR Code para GA Reserve.</p>";
            }
        } else {
            qrCodeContainer.innerHTML = "<p class='error'>Não foi possível carregar o QR Code para GA Reserve (código ausente).</p>";
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

    checkPaymentButton.addEventListener('click', () => {
        if (currentTransactionId) {
            console.log(`(GA Reserve 03) Verificando status para transação: ${currentTransactionId}`);
            checkPaymentStatus(currentTransactionId);
        } else {
            alert('(GA Reserve 03) Gere um PIX primeiro.');
        }
    });

    function startPolling(transactionId) {
        stopPolling();
        console.log(`(GA Reserve 03) Iniciando polling para transação: ${transactionId}`);
        statusMessage.textContent = 'Aguardando confirmação de pagamento GA Reserve...';
        statusMessage.className = 'info';
        pollingIntervalId = setInterval(() => {
            checkPaymentStatus(transactionId);
        }, 5000);
    }

    function stopPolling() {
        if (pollingIntervalId) {
            console.log('(GA Reserve 03) Parando polling.');
            clearInterval(pollingIntervalId);
            pollingIntervalId = null;
        }
    }

    async function checkPaymentStatus(transactionId) {
        if (!transactionId) return;
        console.log(`(GA Reserve 03) Verificando status para ${transactionId}...`);
        try {
            const response = await fetch(`/api/status/${transactionId}?context=GAReserve`);
            if (!response.ok) {
                console.warn(`(GA Reserve 03) Erro ao verificar status (${response.status}), continuando polling.`);
                if(statusMessage.className !== 'success' && statusMessage.className !== 'error') {
                     statusMessage.textContent = 'Verificando confirmação GA Reserve...';
                     statusMessage.className = 'info';
                }
                return;
            }
            const data = await response.json();
            console.log('(GA Reserve 03) Status recebido:', data.status);
            statusMessage.textContent = `Status GA Reserve: ${data.status}`;
            statusMessage.className = 'info';

            const paidStatuses = ['paid', 'approved', 'completed']; // Adicionar 'completed'
            if (paidStatuses.includes(data.status?.toLowerCase())) { // Verificação case-insensitive
                console.log('(GA Reserve 03) Pagamento confirmado!');
                statusMessage.textContent = 'Pagamento GA Reserve confirmado! Redirecionando...';
                statusMessage.className = 'success';
                stopPolling();

                // ALTERADO: Redirecionar para /etapa02/index.html
                const redirectUrl = '/etapa02/index.html'; 
                console.log(`(GA Reserve 03) Redirecionando para: ${redirectUrl}`);
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 2000);
            } else if (['refused', 'cancelled', 'chargeback', 'refunded', 'failed'].includes(data.status?.toLowerCase())) { // Adicionar 'failed' e verificação case-insensitive
                console.log('(GA Reserve 03) Pagamento falhou ou foi cancelado.');
                statusMessage.textContent = `Pagamento GA Reserve ${data.status}. Tente novamente.`;
                statusMessage.className = 'error';
                stopPolling();
            }
        } catch (error) {
            console.error('(GA Reserve 03) Erro durante o polling:', error);
            statusMessage.textContent = 'Erro ao verificar status do GA Reserve.';
            statusMessage.className = 'error';
        }
    }
}); 