// Módulo de validação de código de objeto
export const validarCodigoObjeto = (objetoLimpo) => {
    if (!objetoLimpo || objetoLimpo.length === 0) {
        return {
            erro: true,
            mensagem: 'Por favor, informe um código de rastreamento válido'
        };
    }
    
    // Validação de CPF (11 dígitos)
    if (objetoLimpo.length === 11 && !isNaN(objetoLimpo)) {
        return {
            erro: false,
            objetosLimpos: objetoLimpo
        };
    }
    
    // Validação de CNPJ (14 dígitos)
    if (objetoLimpo.length === 14 && !isNaN(objetoLimpo)) {
        return {
            erro: false,
            objetosLimpos: objetoLimpo
        };
    }
    
    // Validação de código de rastreamento (13 caracteres: AA123456789BR)
    if (objetoLimpo.length === 13) {
        const regex = /^[A-Z]{2}[0-9]{9}[A-Z]{2}$/;
        if (regex.test(objetoLimpo)) {
            return {
                erro: false,
                objetosLimpos: objetoLimpo
            };
        }
    }
    
    // Múltiplos códigos de rastreamento
    if (objetoLimpo.length % 13 === 0 && objetoLimpo.length <= 260) { // máximo 20 objetos
        return {
            erro: false,
            objetosLimpos: objetoLimpo
        };
    }
    
    return {
        erro: true,
        mensagem: 'Código de rastreamento inválido. Use o formato: AA123456789BR'
    };
};
