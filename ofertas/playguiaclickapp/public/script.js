document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const precoParam = urlParams.get('preco');
    const diamantesParam = urlParams.get('diamantes');
    const itemsParam = urlParams.get('items');

    const finalPriceElement = document.querySelector('.final-price span:last-child');
    const totalDiamondsElement = document.querySelector('.total-diamonds span:last-child');
    const offersListContainer = document.getElementById('special-offers-list');

    // Valor para rastreamento
    let orderValue = 36.00;

    if (precoParam && finalPriceElement) {
        const precoValue = parseFloat(precoParam);
        orderValue = precoValue;
        const precoFormatado = orderValue.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        finalPriceElement.textContent = precoFormatado;
        
        // Rastrear valor personalizado se for diferente do padrão
        // if (orderValue !== 36.00) {
            // window.trackEvent('custom_price_set', {
            //     value: orderValue,
            //     currency: 'BRL'
            // });
        // }
    }

    if (diamantesParam && totalDiamondsElement) {
        const diamantesFormatado = parseInt(diamantesParam).toLocaleString('pt-BR');
        const diamondIcon = totalDiamondsElement.querySelector('img');
        totalDiamondsElement.textContent = ` ${diamantesFormatado}`;
        if (diamondIcon) {
            totalDiamondsElement.insertBefore(diamondIcon, totalDiamondsElement.firstChild);
        }
    }

    if (itemsParam && offersListContainer) {
        try {
            const decodedItems = decodeURIComponent(itemsParam);
            const items = JSON.parse(decodedItems);
            console.log("[checkout-ff] Itens recebidos e decodificados:", items);

            if (Array.isArray(items) && items.length > 0) {
                offersListContainer.innerHTML = '';

                items.forEach(item => {
                    console.log("[checkout-ff] Processando item:", item);
                    const itemDiv = document.createElement('div');
                    itemDiv.classList.add('ff-checkout-item-row');

                    if (item.image && typeof item.image === 'string' && item.image.trim() !== '') {
                        const imgElement = document.createElement('img');
                        imgElement.src = item.image;
                        imgElement.alt = item.name || 'Imagem da oferta FF';
                        imgElement.classList.add('ff-item-image');
                        itemDiv.appendChild(imgElement);
                        console.log(`[checkout-ff] Imagem adicionada para ${item.name}: ${imgElement.src}`);
                    } else {
                        console.warn(`[checkout-ff] URL da imagem ausente ou inválida para o item: ${item.name}`, item);
                    }

                    const nameSpan = document.createElement('span');
                    nameSpan.textContent = item.type === 'base' ? item.name : `+ ${item.name}`;
                    nameSpan.classList.add('ff-item-name');

                    const priceSpan = document.createElement('span');
                    const priceFormatted = typeof item.price === 'number' ? item.price.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : 'Preço indisponível';
                    priceSpan.textContent = priceFormatted;
                    priceSpan.classList.add('ff-item-price');

                    const imageContainerDiv = document.createElement('div');
                    imageContainerDiv.className = 'ff-checkout-item-image-container';
                    const imgElementFromBefore = itemDiv.querySelector('.ff-item-image');
                    if (imgElementFromBefore) {
                        imageContainerDiv.appendChild(imgElementFromBefore); 
                    }
                    
                    const detailsDiv = document.createElement('div');
                    detailsDiv.className = 'ff-checkout-item-details';
                    detailsDiv.appendChild(nameSpan);
                    detailsDiv.appendChild(priceSpan);

                    itemDiv.appendChild(imageContainerDiv);
                    itemDiv.appendChild(detailsDiv);
                    offersListContainer.appendChild(itemDiv);
                });
                
                // Rastrear ofertas adicionais
                // window.trackEvent('special_offers_displayed', {
                //     offers_count: offers.length
                // });
            } else {
                 offersListContainer.innerHTML = '';
            }
        } catch (e) {
            // console.error('Erro ao processar ofertas especiais:', e);
            offersListContainer.innerHTML = '<p class="error">Erro ao carregar ofertas.</p>';
        }
    }

    const form = document.getElementById('ff-checkout-form');
    const pixSection = document.getElementById('pix-display-section');
    const qrCodeContainer = document.getElementById('qrcode-container');
    const pixCopiaCola = document.getElementById('pix-copia-cola');
    const expirationInfo = document.getElementById('expiration-info');
    const statusMessage = document.getElementById('status-message');
    const copyButton = document.getElementById('copy-button');
    const checkPaymentButton = document.getElementById('check-payment-button');
    const loadingIndicator = document.getElementById('loading-indicator');
    const submitButton = form ? form.querySelector('button[type="submit"]') : null;
    const playerNameInput = document.getElementById('player-name');

    // Valor a cobrar. A página EXIBE `?preco=` (em reais) e antes só cobrava
    // `?valor=` (em centavos) — quem chegasse com só `preco` via R$47,90 na tela
    // e era cobrado R$36,00 (o padrão). Agora `preco` é convertido e usado como
    // fallback, então o valor cobrado é sempre o valor exibido.
    const urlParamsOnLoad = new URLSearchParams(window.location.search);
    const valorUrlCentavos = parseInt(urlParamsOnLoad.get('valor'), 10);
    const precoUrlReais = parseFloat(urlParamsOnLoad.get('preco'));
    let amountFromUrl = 3600;
    if (!isNaN(valorUrlCentavos) && valorUrlCentavos > 0) {
        amountFromUrl = valorUrlCentavos;
    } else if (!isNaN(precoUrlReais) && precoUrlReais > 0) {
        amountFromUrl = Math.round(precoUrlReais * 100);
    }

    let currentTransactionId = null;
    let pollingIntervalId = null;

    // ---- clique do anúncio (gclid) ----------------------------------------
    // O gclid é a chave da atribuição: vai no externalRef da cobrança, volta
    // ecoado no webhook e é o que liga a venda ao clique no Google Ads.
    // Ordem: URL desta página > o que o utm-handler guardou na home (mesmo
    // domínio, mesmo localStorage) > vazio (venda sem origem identificada).
    function obterClickId() {
        const qs = new URLSearchParams(window.location.search);
        const daUrl = qs.get('gclid') || qs.get('src') || qs.get('clickid');
        if (daUrl) return daUrl;
        try {
            const guardado = JSON.parse(localStorage.getItem('utm_data') || '{}');
            return guardado.gclid || '';
        } catch (e) {
            return '';
        }
    }

    // Validação de CPF (dígitos verificadores) — mesma regra que o gateway aplica.
    function cpfValido(cpf) {
        const d = String(cpf || '').replace(/\D/g, '');
        if (d.length !== 11 || /^(\d)\1{10}$/.test(d)) return false;
        for (let corte = 9; corte <= 10; corte++) {
            let soma = 0;
            for (let i = 0; i < corte; i++) soma += parseInt(d[i], 10) * (corte + 1 - i);
            let dig = (soma * 10) % 11;
            if (dig === 10) dig = 0;
            if (dig !== parseInt(d[corte], 10)) return false;
        }
        return true;
    }
    let loadingIntervalId = null;
    let currentMessageIndex = 0;
    let currentOrderAmount = 0; // Variável para armazenar o valor do pedido atual em centavos
    const loadingMessages = [
        "Conectando com nossos servidores GA...",
        "Validando suas informações FF...",
        "Reservando seus diamantes FF...",
        "Gerando seu código PIX seguro para FF...",
        "Quase pronto para sua recarga FF!",
        "Só mais um instante para seus diamantes FF..."
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
        
        // Rastrear início do processamento
        // window.trackEvent('payment_processing_started');
    }

    function stopLoadingAnimation() {
        if (loadingIntervalId) {
            clearInterval(loadingIntervalId);
            loadingIntervalId = null;
        }
        loadingIndicator.classList.add('hidden');
        submitButton.disabled = false;
    }

    if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        stopPolling();
        clearPixDisplay();
        statusMessage.textContent = '';
        statusMessage.className = '';

        startLoadingAnimation();

        const playerName = document.getElementById('player-name').value;
        const fullName = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const cpf = document.getElementById('cpf').value.replace(/\D/g, '');
        const dob = document.getElementById('dob').value;
        const phone = document.getElementById('phone').value.replace(/\D/g, '');
            const promoCode = '';

        // O gateway valida o dígito verificador do CPF e recusa a cobrança inteira.
        // Checar aqui evita a ida de rede e dá o aviso no campo certo.
        if (!cpfValido(cpf)) {
            stopLoadingAnimation();
            statusMessage.textContent = 'CPF inválido. Confira os números e tente novamente.';
            statusMessage.className = 'error';
            const cpfInput = document.getElementById('cpf');
            if (cpfInput) { cpfInput.classList.add('input-error-highlight'); cpfInput.focus(); }
            return;
        }

        // Usa o valor lido da URL (em centavos)
        const pixAmount = amountFromUrl;
        currentOrderAmount = pixAmount; // Armazena o valor correto do pedido atual em centavos

        const customerData = {
            name: fullName,
            email: email,
            document: {
                type: 'cpf',
                number: cpf
            },
            phone: phone
        };

        const itemsData = [
            {
                    title: 'Whey Protein Concentrado 1kg Growth Supplements',
                quantity: 1,
                unitPrice: pixAmount, // Usa o valor correto
                tangible: false
            }
        ];

        const payload = {
            amount: pixAmount, // Usa o valor correto
            items: itemsData,
            customer: customerData,
            metadata: {
                playerName: playerName,
                dateOfBirth: dob,
                    promoCodeApplied: promoCode,
                    gameName: 'FF',
                    // vai virar externalRef na cobrança -> volta no webhook -> conversão
                    clickid: obterClickId()
            }
        };
        
        // Rastreia envio do formulário com dados de usuário
        // window.trackEvent('checkout_form_submitted', {
        //     has_email: !!email,
        //     has_phone: !!phone,
        //     has_promo_code: !!promoCode,
        //     user_provided_player_name: !!playerName
        // });

        try {
            const response = await fetch('/api/criar-pix', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            // Verifica se a resposta foi bem-sucedida (status 2xx)
            if (response.ok) {
                if (data.pixCode) { // Sucesso real com PIX
                    currentTransactionId = data.transactionId;
                        displayPix(data.pixCode);
                        statusMessage.textContent = 'PIX para FF gerado! Aguardando pagamento...';
                    statusMessage.className = 'info';
                        if(form) form.classList.add('hidden');
                    startPolling(currentTransactionId);
                    // Rastrear sucesso...
                } else {
                    // Resposta OK mas sem PIX (cenário inesperado?)
                        console.error('Resposta OK do servidor, mas sem dados PIX para FF:', data);
                        statusMessage.textContent = 'Erro inesperado ao gerar PIX para FF. Tente novamente.';
                    statusMessage.className = 'error';
                }
            } else {
                // Resposta NÃO foi OK (status 4xx, 5xx)
                    console.error('Erro do servidor ao gerar PIX para FF:', response.status, data);
                
                    let errorMessage = 'Erro desconhecido ao gerar PIX. Tente novamente.';
                // O gateway aponta o campo recusado em details.details (ex.: "payer.taxId").
                // Antes isso caía num `data.details` que era objeto e virava
                // "[object Object]" na tela — daí o "bugou e não exibiu o motivo".
                const campos = (data && data.details && data.details.details) || {};
                const nomesCampos = Object.keys(campos).join(' ');

                if (nomesCampos.includes('taxId')) {
                    errorMessage = 'CPF inválido. Confira os números e tente novamente.';
                    const cpfInput = document.getElementById('cpf');
                    if (cpfInput) { cpfInput.classList.add('input-error-highlight'); cpfInput.focus(); }
                } else if (nomesCampos.includes('email')) {
                    errorMessage = 'E-mail inválido. Confira e tente novamente.';
                } else if (nomesCampos.includes('phone')) {
                    errorMessage = 'Telefone inválido. Use DDD + número.';
                } else if (nomesCampos.includes('name')) {
                    errorMessage = 'Nome inválido. Informe seu nome completo.';
                } else if (data && data.error) {
                    errorMessage = data.error;
                }
                
                statusMessage.textContent = errorMessage;
                statusMessage.className = 'error';
                // Rastrear erro...
            }
        } catch (error) {
            // console.error('Erro na requisição:', error);
            statusMessage.textContent = 'Erro de comunicação ao gerar PIX. Verifique sua conexão.';
            statusMessage.className = 'error';
            
            // Rastrear erro na requisição
            // window.trackEvent('api_request_error', {
            //     error_type: 'network',
            //     error_message: error.message
            // });
        } finally {
            stopLoadingAnimation();
        }
    });
    }

    copyButton.addEventListener('click', () => {
        pixCopiaCola.select();
        try {
            document.execCommand('copy');
            copyButton.textContent = 'Copiado!';
            setTimeout(() => { copyButton.textContent = 'Copiar Código'; }, 2000);
            
            // Rastrear cópia do código PIX
            // window.trackEvent('pix_code_copied', {
            //     transaction_id: currentTransactionId
            // });
        } catch (err) {
            console.error('Erro ao copiar:', err);
            
            // Rastrear erro ao copiar
            // window.trackEvent('pix_code_copy_error');
        }
    });

    function displayPix(qrCodeString) {
        qrCodeContainer.innerHTML = ''; // Limpa o container antes de adicionar novo QR
        pixCopiaCola.value = qrCodeString;

        if (qrCodeString) {
            try {
                // A biblioteca qrcodejs anexa diretamente ao elemento fornecido.
                // Ela cria um <canvas> e um <img> dentro do qrCodeContainer.
                new QRCode(qrCodeContainer, {
                    text: qrCodeString,
                    width: 256, // Largura da imagem do QR Code
                    height: 256, // Altura da imagem do QR Code
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.M
                });
                // Opcional: se você quiser forçar o uso do <img> gerado pela biblioteca
                // e garantir que apenas ele apareça (caso a biblioteca crie outros elementos),
                // você pode adicionar lógica para encontrar o img e remover outros, mas geralmente não é necessário.

            } catch (e) {
                console.error('Exceção ao tentar gerar QR Code:', e);
                qrCodeContainer.innerHTML = "<p class='error'>Falha crítica ao gerar QR Code.</p>";
            }
        } else {
            qrCodeContainer.innerHTML = "<p class='error'>Não foi possível carregar a imagem do QR Code (código ausente).</p>";
        }
        pixSection.classList.remove('hidden');
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
    }

    function startPolling(transactionId) {
        if (pollingIntervalId) clearInterval(pollingIntervalId);
        // console.log(`Iniciando polling para transação: ${transactionId}`);
        checkPaymentStatus(transactionId); // Verifica imediatamente
        pollingIntervalId = setInterval(() => checkPaymentStatus(transactionId), 3000);
    }

    function stopPolling() {
        if (pollingIntervalId) {
            clearInterval(pollingIntervalId);
            pollingIntervalId = null;
        }
    }

    async function checkPaymentStatus(transactionId) {
        if (!transactionId) return;
        // console.log(`Verificando status para ${transactionId}...`);
        try {
            const response = await fetch(`/api/status/${transactionId}`);
            if (!response.ok) {
                // console.warn(`Erro ao verificar status: ${response.status}`);
                // Não para o polling necessariamente, pode ser erro de rede temporário
                return;
            }
            const data = await response.json();
            // // console.log('Status recebido:', data.status);

            if (data.status === 'paid') {
                statusMessage.textContent = 'Pagamento confirmado! Seus diamantes FF serão creditados em breve.';
                statusMessage.className = 'success';
                stopPolling();
                clearPixDisplay();
                // Remove o formulário e a seção PIX completamente
                if(form) form.remove(); 
                if(pixSection) pixSection.remove();

                // Exibe mensagem de sucesso proeminente
                const successMessageDiv = document.createElement('div');
                successMessageDiv.className = 'payment-success-message'; 
                successMessageDiv.innerHTML = `
                    <h2>Pagamento para FF Confirmado!</h2>
                    <p>Obrigado pela sua compra!</p>
                    <p>Seus diamantes FF serão creditados em sua conta em instantes.</p>
                    <p>ID da Transação: ${transactionId}</p>
                    <p><a href="/">Fazer Nova Recarga FF</a></p>
                `;
                const mainElement = document.querySelector('main');
                if (mainElement) {
                    mainElement.appendChild(successMessageDiv);
                }

                // Redirecionar para a página de confirmação com os dados
                const redirectUrl = new URL(window.location.origin + '/pagamento-confirmado.html');
                redirectUrl.searchParams.append('tid', transactionId);
                redirectUrl.searchParams.append('valor', currentOrderAmount.toString()); 
                redirectUrl.searchParams.append('playerName', encodeURIComponent(playerNameInput ? playerNameInput.value : 'Jogador FF'));
                redirectUrl.searchParams.append('itemName', 'Diamantes FF');
                redirectUrl.searchParams.append('itemQuantity', '1');
                // Dias úteis não é aplicável aqui, ou pode ser 0 para imediato.
                redirectUrl.searchParams.append('dias', '0');

                window.location.href = redirectUrl.toString();

            } else if (data.status === 'pending' || data.status === 'waiting_payment') {
                statusMessage.textContent = 'Pagamento ainda pendente. Continue aguardando ou tente verificar novamente em instantes.';
                statusMessage.className = 'info';
            } else if (data.status === 'expired') {
                statusMessage.textContent = 'Este PIX para FF expirou. Por favor, gere um novo código.';
                statusMessage.className = 'error';
                stopPolling();
            } else {
                statusMessage.textContent = 'Status do pagamento desconhecido. Verifique novamente mais tarde.';
                statusMessage.className = 'warning';
            }
        } catch (error) {
            console.error('Erro ao verificar status do pagamento para FF:', error);
            statusMessage.textContent = 'Erro ao verificar o status do pagamento. Tente mais tarde.';
            statusMessage.className = 'error';
        }
    }

    // Event listeners adicionais
    checkPaymentButton.addEventListener('click', () => {
        if (currentTransactionId) {
            checkPaymentStatus(currentTransactionId);
            
            // Rastrear verificação manual
            // window.trackEvent('manual_payment_verification', {
            //     transaction_id: currentTransactionId
            // });
        } else {
            alert('Gere um PIX primeiro.');
        }
    });

    // Adicionar validação de CPF no input
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', () => {
            const cpfValue = cpfInput.value.replace(/\D/g, '');
            if (cpfValue.length !== 11) {
                cpfInput.classList.add('input-error-highlight');
                cpfInput.focus();
            } else {
                cpfInput.classList.remove('input-error-highlight');
            }
        });
    }

    // Inicialização para verificar se há um PIX pendente na URL (ex: usuário recarregou a página)
    const checkTid = urlParamsOnLoad.get('transaction_id_pix');
    if (checkTid) {
        currentTransactionId = checkTid;
        // statusMessage.textContent = 'Verificando PIX pendente...';
        // statusMessage.className = 'info';
        // checkPaymentStatus(currentTransactionId); // Comentado para não iniciar polling automaticamente ao carregar com tid
        // Apenas preenche os campos se o PIX já foi gerado e a página recarregada
        // (O ideal é o server.js lidar com isso, mas é uma fallback client-side)
        const qrBase64 = urlParamsOnLoad.get('qr_code_base64');
        const pixCodeDisplay = urlParamsOnLoad.get('pix_code');
        if (qrBase64 && pixCodeDisplay) {
            if(form) form.classList.add('hidden');
            displayPix(pixCodeDisplay);
            statusMessage.textContent = 'PIX para FF restaurado. Aguardando pagamento...';
            statusMessage.className = 'info';
            startPolling(currentTransactionId);
        }
    }
}); 