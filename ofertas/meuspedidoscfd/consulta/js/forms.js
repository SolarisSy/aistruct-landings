// Módulo de formulários simplificado
export const setValidade = (input, mensagem) => {
    const campo = input.closest('.campo');
    if (!campo) return;
    
    const mensagemDiv = campo.querySelector('.mensagem');
    if (!mensagemDiv) return;
    
    if (mensagem) {
        mensagemDiv.textContent = mensagem;
        mensagemDiv.style.color = 'red';
        input.classList.add('invalido');
    } else {
        mensagemDiv.textContent = '';
        input.classList.remove('invalido');
    }
};
