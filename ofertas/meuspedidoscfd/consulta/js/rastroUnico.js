// Módulo de rastreamento único simplificado

// Função para determinar o ícone baseado no tipo de evento
const getIconeEvento = (descricao) => {
    const desc = descricao.toLowerCase();
    
    // Caixa/pacote para eventos de postagem e recebimento
    if (desc.includes('postado') || desc.includes('postagem') || desc.includes('aguardando')) {
        return './images/caixa-visto.svg';
    }
    // Caminhão para eventos de transferência e transporte
    else if (desc.includes('transferência') || desc.includes('transporte') || desc.includes('trânsito') || desc.includes('fiscalização')) {
        return './images/caminhao-correndo.svg';
    }
    // Padrão: caixa
    else {
        return './images/caixa-visto.svg';
    }
};

export const ul = (objeto, tipo = 'N') => {
    if (!objeto || !objeto.eventos || objeto.eventos.length === 0) {
        return '<div class="rastro-unico">Nenhum evento encontrado</div>';
    }
    
    let html = '<ul class="ship-steps">';
    
    objeto.eventos.forEach((evento, index) => {
        const icone = getIconeEvento(evento.descricao || '');
        const isLast = index === objeto.eventos.length - 1;
        const arrowClass = isLast ? 'arrow-none' : 'arrow-current';
        
        // Monta o local
        let local = '';
        if (evento.unidade && evento.unidade.endereco) {
            const cidade = evento.unidade.endereco.cidade || '';
            const uf = evento.unidade.endereco.uf || '';
            if (cidade && uf) {
                local = `${cidade} - ${uf}`;
            } else if (uf) {
                local = uf;
            }
        }
        
        // Verifica se tem detalhe (origem/destino)
        let detalheHtml = '';
        if (evento.detalhe) {
            const detalhes = evento.detalhe.split(' para ');
            if (detalhes.length === 2) {
                detalheHtml = `<p class="text text-content">${detalhes[0]}</p><p class="text text-content">para ${detalhes[1]}</p>`;
            } else {
                detalheHtml = `<p class="text text-content">${evento.detalhe}</p>`;
            }
        } else if (local) {
            detalheHtml = `<p class="text text-content">${local}</p>`;
        }
        
        html += `
            <li class="step">
                <div class="${arrowClass}">
                    <div class="circle">
                        <img class="circle-img" src="${icone}">
                    </div>
                </div>
                <div class="step-content">
                    <p class="text text-head">${evento.descricao || 'Evento'}</p>
                    ${detalheHtml}
                    <p class="text text-content">${evento.dtHrCriado || ''}</p>
                </div>
            </li>
        `;
    });
    
    html += '</ul>';
    return html;
};

export const verMais = (objeto) => {
    if (!objeto || !objeto.eventos || objeto.eventos.length <= 3) {
        return '';
    }
    
    // Mostra apenas os 3 primeiros eventos
    let html = '<ul class="ship-steps">';
    
    objeto.eventos.slice(0, 3).forEach((evento, index) => {
        const icone = getIconeEvento(evento.descricao || '');
        const arrowClass = 'arrow-current';
        
        let local = '';
        if (evento.unidade && evento.unidade.endereco) {
            const cidade = evento.unidade.endereco.cidade || '';
            const uf = evento.unidade.endereco.uf || '';
            if (cidade && uf) {
                local = `${cidade} - ${uf}`;
            } else if (uf) {
                local = uf;
            }
        }
        
        let detalheHtml = '';
        if (evento.detalhe) {
            const detalhes = evento.detalhe.split(' para ');
            if (detalhes.length === 2) {
                detalheHtml = `<p class="text text-content">${detalhes[0]}</p><p class="text text-content">para ${detalhes[1]}</p>`;
            } else {
                detalheHtml = `<p class="text text-content">${evento.detalhe}</p>`;
            }
        } else if (local) {
            detalheHtml = `<p class="text text-content">${local}</p>`;
        }
        
        html += `
            <li class="step">
                <div class="${arrowClass}">
                    <div class="circle">
                        <img class="circle-img" src="${icone}">
                    </div>
                </div>
                <div class="step-content">
                    <p class="text text-head">${evento.descricao || 'Evento'}</p>
                    ${detalheHtml}
                    <p class="text text-content">${evento.dtHrCriado || ''}</p>
                </div>
            </li>
        `;
    });
    
    // Adiciona separador e botão "ver mais"
    html += `
        <li style="padding-top: 20px;">
            <div class="arrow-dashed" style="height: 20px;"></div>
        </li>
        <div class="btn-ver-mais">
            <a id="a-ver-mais" style="cursor:pointer;">
                <i class="fa fa-plus-circle fa-2x icon-has-btn"></i>
                <span id="tooltip-vermais" class="title">Mais informações</span>
            </a>
        </div>
        <li style="padding-bottom: 20px;">
            <div class="arrow-dashed" style="height: 20px;"></div>
        </li>
    `;
    
    // Adiciona o último evento
    const ultimoEvento = objeto.eventos[objeto.eventos.length - 1];
    const iconeUltimo = getIconeEvento(ultimoEvento.descricao || '');
    
    let localUltimo = '';
    if (ultimoEvento.unidade && ultimoEvento.unidade.endereco) {
        const cidade = ultimoEvento.unidade.endereco.cidade || '';
        const uf = ultimoEvento.unidade.endereco.uf || '';
        if (cidade && uf) {
            localUltimo = `${cidade} - ${uf}`;
        } else if (uf) {
            localUltimo = uf;
        }
    }
    
    html += `
        <li class="step">
            <div class="arrow-none">
                <div class="circle">
                    <img class="circle-img" src="${iconeUltimo}">
                </div>
            </div>
            <div class="step-content">
                <p class="text text-head">${ultimoEvento.descricao || 'Evento'}</p>
                <p class="text text-content">${localUltimo}</p>
                <p class="text text-content">${ultimoEvento.dtHrCriado || ''}</p>
            </div>
        </li>
    `;
    
    html += '</ul>';
    return html;
};
