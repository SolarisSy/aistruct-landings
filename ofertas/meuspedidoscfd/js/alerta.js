// Módulo de alertas simplificado
export const abre = (mensagem, tempo = 0, botao = '') => {
    const alerta = document.getElementById('alerta');
    const msg = alerta.querySelector('.msg');
    const act = alerta.querySelector('.act');
    
    msg.textContent = mensagem;
    alerta.classList.add('visivel');
    
    if (botao) {
        act.innerHTML = `<button onclick="document.getElementById('alerta').classList.remove('visivel')">${botao}</button>`;
    } else {
        act.innerHTML = '';
    }
    
    if (tempo > 0) {
        setTimeout(() => {
            fecha();
        }, tempo * 1000);
    }
};

export const fecha = () => {
    const alerta = document.getElementById('alerta');
    alerta.classList.remove('visivel');
};
