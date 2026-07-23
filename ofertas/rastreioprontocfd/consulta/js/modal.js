// Módulo de modal simplificado
export const abre = (id) => {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('visivel');
    }
};

export const fecha = (id) => {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('visivel');
    }
};

// Event listeners para fechar modais
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.modal .fechar, .modal .close').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            if (modal) {
                modal.classList.remove('visivel');
            }
        });
    });
});
