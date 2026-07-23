// Dados de demonstração para rastreamento

// Função para obter localização do usuário pelo IP
const obterLocalizacaoUsuario = async () => {
    try {
        const response = await fetch('https://ipapi.co/json/');
        const data = await response.json();
        return {
            cidade: data.city || 'SÃO PAULO',
            uf: data.region_code || 'SP'
        };
    } catch (error) {
        console.error('Erro ao obter localização:', error);
        // Retorna localização padrão em caso de erro
        return {
            cidade: 'SÃO PAULO',
            uf: 'SP'
        };
    }
};

// Função para validar CPF
const validarCPF = (cpf) => {
    cpf = cpf.replace(/[^\d]/g, '');
    
    if (cpf.length !== 11) return false;
    
    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1{10}$/.test(cpf)) return false;
    
    // Valida primeiro dígito verificador
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(9))) return false;
    
    // Valida segundo dígito verificador
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(10))) return false;
    
    return true;
};

// Função para gerar dados de rastreamento aleatórios
const gerarDadosRastreamento = async (codigo) => {
    // Obtém a localização do usuário
    const localizacaoUsuario = await obterLocalizacaoUsuario();
    
    const tiposPostais = [
        'Encomenda SEDEX',
        'Encomenda PAC',
        'SEDEX',
        'PAC'
    ];
    
    const cidadesOrigem = [
        { cidade: 'RIO DE JANEIRO', uf: 'RJ' },
        { cidade: 'BELO HORIZONTE', uf: 'MG' },
        { cidade: 'CURITIBA', uf: 'PR' },
        { cidade: 'BRASÍLIA', uf: 'DF' },
        { cidade: 'SALVADOR', uf: 'BA' },
        { cidade: 'FORTALEZA', uf: 'CE' },
        { cidade: 'RECIFE', uf: 'PE' }
    ];
    
    // Remove a cidade do usuário da lista de origens se estiver lá
    const cidadesOrigemFiltradas = cidadesOrigem.filter(
        c => c.cidade !== localizacaoUsuario.cidade
    );
    
    const cidadeOrigem = cidadesOrigemFiltradas[Math.floor(Math.random() * cidadesOrigemFiltradas.length)];
    
    const dataAtual = new Date();
    const eventos = [];
    
    // Evento 1: Aguardando pagamento (cidade do usuário)
    const data1 = new Date(dataAtual);
    eventos.push({
        descricao: 'Aguardando pagamento',
        dtHrCriado: `${String(data1.getDate()).padStart(2, '0')}/${String(data1.getMonth() + 1).padStart(2, '0')}/${data1.getFullYear()} ${String(Math.floor(Math.random() * 24)).padStart(2, '0')}:${String(Math.floor(Math.random() * 60)).padStart(2, '0')}`,
        unidade: {
            endereco: {
                cidade: `Unidade de Tratamento, ${localizacaoUsuario.cidade}`,
                uf: localizacaoUsuario.uf
            }
        }
    });
    
    // Evento 2: Encaminhado para fiscalização (cidade do usuário)
    const data2 = new Date(dataAtual);
    data2.setHours(data2.getHours() - 1);
    eventos.push({
        descricao: 'Encaminhado para fiscalização aduaneira',
        dtHrCriado: `${String(data2.getDate()).padStart(2, '0')}/${String(data2.getMonth() + 1).padStart(2, '0')}/${data2.getFullYear()} ${String(Math.floor(Math.random() * 24)).padStart(2, '0')}:${String(Math.floor(Math.random() * 60)).padStart(2, '0')}`,
        unidade: {
            endereco: {
                cidade: `Unidade de Tratamento, ${localizacaoUsuario.cidade}`,
                uf: localizacaoUsuario.uf
            }
        }
    });
    
    // Evento 3: Transferência para cidade do usuário
    const data3 = new Date(dataAtual);
    data3.setDate(data3.getDate() - 1);
    eventos.push({
        descricao: 'Objeto em transferência - por favor aguarde',
        dtHrCriado: `${String(data3.getDate()).padStart(2, '0')}/${String(data3.getMonth() + 1).padStart(2, '0')}/${data3.getFullYear()} ${String(Math.floor(Math.random() * 24)).padStart(2, '0')}:${String(Math.floor(Math.random() * 60)).padStart(2, '0')}`,
        detalhe: `de Unidade de Tratamento, ${cidadeOrigem.cidade} - ${cidadeOrigem.uf} para Unidade de Tratamento, ${localizacaoUsuario.cidade} - ${localizacaoUsuario.uf}`,
        unidade: {
            endereco: cidadeOrigem
        }
    });
    
    // Evento 4: Transferência intermediária
    const data4 = new Date(dataAtual);
    data4.setDate(data4.getDate() - 1);
    data4.setHours(data4.getHours() - 6);
    eventos.push({
        descricao: 'Objeto em transferência - por favor aguarde',
        dtHrCriado: `${String(data4.getDate()).padStart(2, '0')}/${String(data4.getMonth() + 1).padStart(2, '0')}/${data4.getFullYear()} ${String(Math.floor(Math.random() * 24)).padStart(2, '0')}:${String(Math.floor(Math.random() * 60)).padStart(2, '0')}`,
        detalhe: `de Agência dos Correios, ${cidadeOrigem.cidade} - ${cidadeOrigem.uf} para Unidade de Tratamento, ${cidadeOrigem.cidade} - ${cidadeOrigem.uf}`,
        unidade: {
            endereco: cidadeOrigem
        }
    });
    
    // Evento 5: Objeto postado
    const data5 = new Date(dataAtual);
    data5.setDate(data5.getDate() - 2);
    eventos.push({
        descricao: 'Objeto postado',
        dtHrCriado: `${String(data5.getDate()).padStart(2, '0')}/${String(data5.getMonth() + 1).padStart(2, '0')}/${data5.getFullYear()} ${String(Math.floor(Math.random() * 24)).padStart(2, '0')}:${String(Math.floor(Math.random() * 60)).padStart(2, '0')}`,
        unidade: {
            endereco: cidadeOrigem
        }
    });
    
    // Evento 6: Aguardando postagem
    const data6 = new Date(dataAtual);
    data6.setDate(data6.getDate() - 3);
    eventos.push({
        descricao: 'Aguardando postagem pelo remetente',
        dtHrCriado: `${String(data6.getDate()).padStart(2, '0')}/${String(data6.getMonth() + 1).padStart(2, '0')}/${data6.getFullYear()} 22:41`,
        unidade: {
            endereco: {
                cidade: '',
                uf: 'BR'
            }
        }
    });
    
    const tipoPostal = tiposPostais[Math.floor(Math.random() * tiposPostais.length)];
    
    return {
        codObjeto: codigo,
        tipoPostal: {
            categoria: tipoPostal
        },
        situacao: 'T',
        atrasado: false,
        dataPrevista: '17/12/2025',
        dtPrevista: '17/12/2025',
        eventos: eventos
    };
};

export const dadosDemo = {
    "AA123456789BR": {
        codObjeto: "AA123456789BR",
        tipoPostal: {
            categoria: "SEDEX"
        },
        situacao: "E",
        atrasado: false,
        dataPrevista: "",
        dtPrevista: "",
        eventos: [
            {
                descricao: "Objeto entregue ao destinatário",
                dtHrCriado: "10/05/2026 14:30",
                unidade: {
                    endereco: {
                        cidade: "SÃO PAULO",
                        uf: "SP"
                    }
                }
            },
            {
                descricao: "Objeto saiu para entrega ao destinatário",
                dtHrCriado: "10/05/2026 08:15",
                unidade: {
                    endereco: {
                        cidade: "SÃO PAULO",
                        uf: "SP"
                    }
                }
            },
            {
                descricao: "Objeto postado",
                dtHrCriado: "09/05/2026 10:00",
                unidade: {
                    endereco: {
                        cidade: "RIO DE JANEIRO",
                        uf: "RJ"
                    }
                }
            }
        ]
    }
};

export const buscarRastreamento = async (codigo, captcha) => {
    // Simula delay de rede
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    // Valida captcha (aceita qualquer coisa para demo)
    if (!captcha || captcha.length < 3) {
        return {
            erro: true,
            mensagem: "Captcha inválido"
        };
    }
    
    // Verifica se é um CPF válido
    if (codigo.length === 11 && /^\d+$/.test(codigo)) {
        if (validarCPF(codigo)) {
            // Gera dados aleatórios para CPF válido
            return await gerarDadosRastreamento(codigo);
        } else {
            return {
                erro: true,
                mensagem: "CPF inválido"
            };
        }
    }
    
    // Verifica se é um código de rastreamento
    if (codigo.length === 13) {
        // Busca nos dados fixos ou gera aleatório
        const dados = dadosDemo[codigo];
        if (dados) {
            return dados;
        }
        // Gera dados aleatórios para código de rastreamento
        return await gerarDadosRastreamento(codigo);
    }
    
    return {
        erro: true,
        mensagem: "Formato inválido. Use um CPF válido ou código de rastreamento (AA123456789BR)"
    };
};

