document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const managerDiv = document.getElementById('manager');
    const loginButton = document.getElementById('login-button');
    const tokenInput = document.getElementById('token');
    const loginError = document.getElementById('login-error');

    const loadingMessage = document.getElementById('loading-message');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message'); // Mensagem de sucesso

    // Inputs de configuração
    const googleAdsAccountIdInput = document.getElementById('googleAdsAccountId');
    const purchaseConversionIdInput = document.getElementById('purchaseConversionId');
    const beginCheckoutConversionIdInput = document.getElementById('beginCheckoutConversionId');
    const saveConfigButton = document.getElementById('save-config-button');

    // Elementos para Pixels de Compra
    const purchasePixelsListDiv = document.getElementById('purchase-pixels-list');
    const newPurchasePixelNameInput = document.getElementById('new-purchase-pixel-name');
    const newPurchasePixelIdInput = document.getElementById('new-purchase-pixel-id');
    const addPurchasePixelBtn = document.getElementById('add-purchase-pixel-btn');

    // Elementos para Pixels de Início de Checkout
    const beginCheckoutPixelsListDiv = document.getElementById('begin-checkout-pixels-list');
    const newBeginCheckoutPixelNameInput = document.getElementById('new-begin-checkout-pixel-name');
    const newBeginCheckoutPixelIdInput = document.getElementById('new-begin-checkout-pixel-id');
    const addBeginCheckoutPixelBtn = document.getElementById('add-begin-checkout-pixel-btn');
    
    const saveAllConfigButton = document.getElementById('save-all-config-button');

    // Mantém uma cópia local da configuração dos pixels
    let currentPixelConfig = {
        purchasePixels: [],
        beginCheckoutPixels: []
    };

    const ADMIN_TOKEN = 'kalashinikov'; // Token correto
    let currentSessionToken = null;

    // --- Funções Auxiliares ---
    function showLoading(message = 'Carregando...') {
        loadingMessage.textContent = message;
        loadingMessage.classList.remove('hidden');
        errorMessage.classList.add('hidden');
        successMessage.classList.add('hidden');
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('hidden');
        loadingMessage.classList.add('hidden');
        successMessage.classList.add('hidden');
    }

     function showSuccess(message) {
        successMessage.textContent = message;
        successMessage.classList.remove('hidden');
        loadingMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
        // Esconde a mensagem após alguns segundos
        setTimeout(() => {
            successMessage.classList.add('hidden');
        }, 3000);
    }

    function hideMessages() {
        loadingMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
        successMessage.classList.add('hidden');
    }

    // --- Lógica de Autenticação ---
    loginButton.addEventListener('click', () => {
        const enteredToken = tokenInput.value;
        if (enteredToken === ADMIN_TOKEN) {
            currentSessionToken = enteredToken;
            loginForm.classList.add('hidden');
            managerDiv.classList.remove('hidden');
            loginError.classList.add('hidden');
            fetchGoogleAdsConfig();
        } else {
            loginError.classList.remove('hidden');
            tokenInput.value = '';
        }
    });

    // --- Lógica de Gerenciamento de Pixels ---
    async function fetchGoogleAdsConfig() {
        showLoading('Carregando configurações de pixels...');
        try {
            const response = await fetch('/api/google-ads-config', {
                headers: { 'x-admin-token': currentSessionToken }
            });
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            const config = await response.json();
            currentPixelConfig = {
                purchasePixels: Array.isArray(config.purchasePixels) ? config.purchasePixels : [],
                beginCheckoutPixels: Array.isArray(config.beginCheckoutPixels) ? config.beginCheckoutPixels : []
            };
            renderAllPixelLists();
            hideMessages();
        } catch (error) {
            console.error('Erro ao buscar configurações de pixels:', error);
            showError(`Falha ao carregar: ${error.message}`);
            currentPixelConfig = { purchasePixels: [], beginCheckoutPixels: [] }; // Reseta em caso de erro
            renderAllPixelLists(); // Renderiza listas vazias
        }
    }

    function renderPixelList(listContainer, pixelArray, pixelType) {
        listContainer.innerHTML = ''; // Limpa a lista atual
        if (pixelArray.length === 0) {
            listContainer.innerHTML = '<p>Nenhum pixel configurado.</p>';
            return;
        }
        const ul = document.createElement('ul');
        ul.className = 'pixel-list';
        pixelArray.forEach((pixel, index) => {
            const li = document.createElement('li');
            const infoDiv = document.createElement('div');
            infoDiv.className = 'pixel-item-info';
            infoDiv.innerHTML = `<strong>${pixel.name}</strong><br><small>${pixel.sendToId}</small>`;
            
            const removeBtn = document.createElement('button');
            removeBtn.textContent = 'Remover';
            removeBtn.className = 'remove-pixel-btn';
            removeBtn.onclick = () => {
                if (confirm(`Remover pixel "${pixel.name}"?`)) {
                    if (pixelType === 'purchase') {
                        currentPixelConfig.purchasePixels.splice(index, 1);
                    } else if (pixelType === 'begin_checkout') {
                        currentPixelConfig.beginCheckoutPixels.splice(index, 1);
                    }
                    renderAllPixelLists(); // Re-renderiza para refletir a remoção
                    // Não salva automaticamente, o usuário deve clicar em "Salvar Todas as Configurações"
                }
            };
            li.appendChild(infoDiv);
            li.appendChild(removeBtn);
            ul.appendChild(li);
        });
        listContainer.appendChild(ul);
    }

    function renderAllPixelLists() {
        renderPixelList(purchasePixelsListDiv, currentPixelConfig.purchasePixels, 'purchase');
        renderPixelList(beginCheckoutPixelsListDiv, currentPixelConfig.beginCheckoutPixels, 'begin_checkout');
    }

    function addPixel(pixelType) {
        let nameInput, idInput, pixelArray;

        if (pixelType === 'purchase') {
            nameInput = newPurchasePixelNameInput;
            idInput = newPurchasePixelIdInput;
            pixelArray = currentPixelConfig.purchasePixels;
        } else if (pixelType === 'begin_checkout') {
            nameInput = newBeginCheckoutPixelNameInput;
            idInput = newBeginCheckoutPixelIdInput;
            pixelArray = currentPixelConfig.beginCheckoutPixels;
        } else {
            return;
        }

        const name = nameInput.value.trim();
        const sendToId = idInput.value.trim();

        if (!name || !sendToId) {
            showError('Nome do Pixel e ID Send To são obrigatórios.');
            return;
        }
        // Validação simples do formato do ID (AW- seguido por /)
        if (!sendToId.startsWith('AW-') || !sendToId.includes('/')) {
            showError('Formato do ID Send To inválido. Deve ser AW-XXXXXXXXXX/YYYYYYYYYYYYYYYYY.');
            return;
        }

        pixelArray.push({ name, sendToId });
        nameInput.value = ''; // Limpa o campo
        idInput.value = '';   // Limpa o campo
        renderAllPixelLists();
        hideMessages(); // Limpa mensagens de erro anteriores
        // Não salva automaticamente, o usuário deve clicar em "Salvar Todas as Configurações"
    }

    addPurchasePixelBtn.addEventListener('click', () => addPixel('purchase'));
    addBeginCheckoutPixelBtn.addEventListener('click', () => addPixel('begin_checkout'));

    async function saveAllGoogleAdsConfig() {
        showLoading('Salvando todas as configurações...');
        try {
            const response = await fetch('/api/google-ads-config', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'x-admin-token': currentSessionToken
                },
                body: JSON.stringify(currentPixelConfig) 
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || `Erro HTTP: ${response.status}`);
            }
            const result = await response.json();
            // Atualiza currentPixelConfig com o que foi salvo (se o backend filtrar/modificar)
            currentPixelConfig = {
                purchasePixels: Array.isArray(result.config.purchasePixels) ? result.config.purchasePixels : [],
                beginCheckoutPixels: Array.isArray(result.config.beginCheckoutPixels) ? result.config.beginCheckoutPixels : []
            };
            renderAllPixelLists(); // Re-renderiza com os dados confirmados
            showSuccess(result.message || 'Configurações salvas com sucesso!');
        } catch (error) {
            console.error('Erro ao salvar configurações:', error);
            showError(`Falha ao salvar: ${error.message}`);
        }
    }

    saveAllConfigButton.addEventListener('click', saveAllGoogleAdsConfig);
}); 