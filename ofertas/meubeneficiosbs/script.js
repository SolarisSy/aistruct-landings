// script.js
(function() {
  "use strict";

  const cpfInput = document.getElementById('cpfInput');
  const consultarBtn = document.getElementById('consultarBtn');
  const consultaForm = document.getElementById('consultaForm');

  // ---- Criar elemento para mensagem de erro ----
  const errorMessage = document.createElement('span');
  errorMessage.className = 'cpf-error';
  errorMessage.style.cssText = `
    display: none;
    color: #dc3545;
    font-size: 13px;
    font-weight: 500;
    margin-top: 6px;
    text-align: left;
    transition: all 0.3s ease;
  `;
  
  // Inserir após o input
  const inputGroup = document.querySelector('.input-group');
  if (inputGroup) {
    inputGroup.appendChild(errorMessage);
  }

  // ---- Máscara CPF ----
  function applyCpfMask(value) {
    let raw = value.replace(/\D/g, '');
    if (raw.length === 0) return '';
    if (raw.length <= 3) return raw;
    if (raw.length <= 6) return raw.slice(0, 3) + '.' + raw.slice(3);
    if (raw.length <= 9) return raw.slice(0, 3) + '.' + raw.slice(3, 6) + '.' + raw.slice(6);
    return raw.slice(0, 3) + '.' + raw.slice(3, 6) + '.' + raw.slice(6, 9) + '-' + raw.slice(9, 11);
  }

  function handleCpfInput(e) {
    let input = e.target;
    let raw = input.value.replace(/\D/g, '');
    if (raw.length > 11) raw = raw.slice(0, 11);
    const masked = applyCpfMask(raw);
    input.value = masked;
    validateAndToggleButton(input.value);
  }

  // ---- Validação CPF ----
  function isValidCPF(cpf) {
    const clean = cpf.replace(/\D/g, '');
    if (clean.length !== 11) return false;
    if (/^(\d)\1{10}$/.test(clean)) return false;
    
    let sum = 0;
    let remainder;
    for (let i = 1; i <= 9; i++) {
      sum += parseInt(clean.substring(i - 1, i)) * (11 - i);
    }
    remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(clean.substring(9, 10))) return false;
    
    sum = 0;
    for (let i = 1; i <= 10; i++) {
      sum += parseInt(clean.substring(i - 1, i)) * (12 - i);
    }
    remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(clean.substring(10, 11))) return false;
    
    return true;
  }

  function validateAndToggleButton(cpfValue) {
    const raw = cpfValue.replace(/\D/g, '');
    const isValid = (raw.length === 11) && isValidCPF(cpfValue);
    
    // Verifica se o CPF está incompleto
    if (raw.length > 0 && raw.length < 11) {
      showError('CPF incompleto. Digite os 11 números.');
    } else if (raw.length === 11 && !isValidCPF(cpfValue)) {
      showError('CPF inválido. Verifique os números digitados.');
    } else {
      hideError();
    }
    
    consultarBtn.disabled = !isValid;
    
    // Adiciona/remove classe de erro no input
    if (raw.length === 11 && !isValid) {
      cpfInput.style.borderColor = '#dc3545';
      cpfInput.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.2)';
    } else {
      cpfInput.style.borderColor = '';
      cpfInput.style.boxShadow = '';
    }
    
    return isValid;
  }

  function showError(message) {
    errorMessage.textContent = message;
    errorMessage.style.display = 'block';
  }

  function hideError() {
    errorMessage.style.display = 'none';
    errorMessage.textContent = '';
  }

  // ---- UTMs ----
  function getUtmParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const utmKeys = ['utm_source', 'utm_campaign', 'utm_medium', 'utm_content', 'utm_term'];
    const utmValues = {};
    const clickKeys = ['gclid', 'src', 'gbraid', 'wbraid'];
    utmKeys.forEach(key => {
      let val = urlParams.get(key);
      utmValues[key] = val || 'direct';
    });
    // click id do Google: so propaga se existir (nunca 'direct')
    clickKeys.forEach(key => {
      const val = urlParams.get(key);
      if (val) utmValues[key] = val;
    });
    // utm_content e o UNICO campo que o webhook do TopperPay devolve -> espelhar o click id nele
    const click = utmValues.gclid || utmValues.src || '';
    if (click && (!utmValues.utm_content || utmValues.utm_content === 'direct')) {
      utmValues.utm_content = click;
      utmValues.src = click;
    }
    return utmValues;
  }

  function saveUtmToLocalStorage(utmObj) {
    try {
      localStorage.setItem('utm_data', JSON.stringify(utmObj));
    } catch (e) {}
  }

  function getUtmFromLocalStorage() {
    try {
      const data = localStorage.getItem('utm_data');
      return data ? JSON.parse(data) : null;
    } catch (e) {
      return null;
    }
  }

  // ---- Salvar dados da API no localStorage ----
  function saveApiResponseToLocalStorage(responseData) {
    try {
      // Salva os dados completos da resposta
      localStorage.setItem('api_response_data', JSON.stringify(responseData));
      
      // Se tiver dados do usuário, salva também em campos separados para fácil acesso
      if (responseData.data) {
        const userData = responseData.data;
        localStorage.setItem('user_nome', userData.nome || '');
        localStorage.setItem('user_cpf', userData.cpf || '');
        localStorage.setItem('user_data_nascimento', userData.data_nascimento || '');
        localStorage.setItem('user_nome_mae', userData.nome_mae || '');
        localStorage.setItem('user_sexo', userData.sexo || '');
      }
    } catch (e) {
      console.error('Erro ao salvar dados no localStorage:', e);
    }
  }

  // ---- Construir URL com UTMs e dados do usuário ----
  function buildRedirectUrl(responseData) {
    const utmQuery = buildUtmQueryString();
    const params = new URLSearchParams();
    
    // Adiciona UTMs
    if (utmQuery) {
      const utmParams = new URLSearchParams(utmQuery);
      utmParams.forEach((value, key) => {
        params.append(key, value);
      });
    }
    
    // Adiciona dados do usuário na URL (se disponíveis)
    if (responseData && responseData.data) {
      const userData = responseData.data;
      if (userData.nome) params.append('nome', encodeURIComponent(userData.nome));
      if (userData.cpf) params.append('cpf', userData.cpf);
      if (userData.data_nascimento) params.append('nasc', encodeURIComponent(userData.data_nascimento));
      if (userData.nome_mae) params.append('mae', encodeURIComponent(userData.nome_mae));
      if (userData.sexo) params.append('sexo', encodeURIComponent(userData.sexo));
    }
    
    const queryString = params.toString();
    return `vsl/index.html${queryString ? '?' + queryString : ''}`;
  }

  function buildUtmQueryString() {
    const urlUtms = getUtmParams();
    const hasSignificantUtm = Object.values(urlUtms).some(v => v !== 'direct');
    let stored = getUtmFromLocalStorage();
    
    let finalUtms = urlUtms;
    if (!hasSignificantUtm && stored) {
      finalUtms = stored;
    } else if (hasSignificantUtm) {
      saveUtmToLocalStorage(urlUtms);
      finalUtms = urlUtms;
    } else {
      saveUtmToLocalStorage(urlUtms);
      finalUtms = urlUtms;
    }
    
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(finalUtms)) {
      if (value && value !== '') {
        params.append(key, value);
      }
    }
    return params.toString();
  }

  // ---- Envio do formulário ----
  async function handleSubmit(e) {
    e.preventDefault();
    
    const cpfValue = cpfInput.value.trim();
    if (!isValidCPF(cpfValue)) {
      consultarBtn.disabled = true;
      showError('CPF inválido. Verifique os números digitados.');
      cpfInput.style.borderColor = '#dc3545';
      cpfInput.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.2)';
      return;
    }
    
    // Limpa erro se houver
    hideError();
    cpfInput.style.borderColor = '';
    cpfInput.style.boxShadow = '';
    
    const cleanCpf = cpfValue.replace(/\D/g, '');
    const payload = { cpf: cleanCpf };
    
    // Estado de loading
    consultarBtn.disabled = true;
    consultarBtn.classList.add('loading');
    consultarBtn.innerHTML = `
      <span class="spinner"></span>
      <span class="btn-text">CONSULTANDO...</span>
    `;
    
    try {
      const response = await fetch('api/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      
      // Obtém a resposta como texto primeiro para debug
      const responseText = await response.text();
      let responseData;
      
      try {
        responseData = JSON.parse(responseText);
      } catch (e) {
        console.error('Erro ao parsear JSON:', responseText);
        throw new Error('Resposta da API em formato inválido');
      }
      
      if (response.status === 200 && responseData.success) {
        // Salva os dados da resposta no localStorage
        saveApiResponseToLocalStorage(responseData);
        
        // Constrói URL com UTMs e dados do usuário
        const redirectUrl = buildRedirectUrl(responseData);
        window.location.href = redirectUrl;
      } else {
        // Erro na consulta
        const errorMsg = responseData.error || 'Ocorreu um erro na consulta. Tente novamente mais tarde.';
        alert(errorMsg);
        resetButton();
        validateAndToggleButton(cpfInput.value);
      }
    } catch (error) {
      console.error('Erro na requisição:', error);
      alert('Erro de conexão. Verifique sua internet e tente novamente.');
      resetButton();
      validateAndToggleButton(cpfInput.value);
    }
  }

  function resetButton() {
    consultarBtn.classList.remove('loading');
    consultarBtn.innerHTML = `
      <i class="fa-solid fa-magnifying-glass btn-icon"></i>
      <span class="btn-text">CONSULTAR VALORES</span>
    `;
    consultarBtn.disabled = false;
  }

  // ---- Inicialização ----
  function init() {
    if (cpfInput.value) {
      const masked = applyCpfMask(cpfInput.value.replace(/\D/g, ''));
      cpfInput.value = masked;
    }
    
    validateAndToggleButton(cpfInput.value);
    
    const urlUtms = getUtmParams();
    const hasSignificantUtm = Object.values(urlUtms).some(v => v !== 'direct');
    const stored = getUtmFromLocalStorage();
    
    if (hasSignificantUtm) {
      saveUtmToLocalStorage(urlUtms);
    } else if (!stored) {
      saveUtmToLocalStorage(urlUtms);
    }
    
    cpfInput.addEventListener('input', handleCpfInput);
    consultaForm.addEventListener('submit', handleSubmit);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();