<?php
/**
 * Tabela da linha de produtos fisicos — valores em CENTAVOS.
 *
 * ARQUIVO GERADO junto com as paginas da loja, a partir da mesma fonte.
 * Editar so aqui faz a vitrine anunciar um numero e a cobranca sair com outro:
 * mexa na fonte que monta o site e gere os dois de novo.
 */

// Frete unico, declarado na pagina. Nao ha calculo por distancia nem por peso.
const CP_LOJA_FRETE = 1990;

// Producao sob demanda + envio. Prazo escrito com folga, como e anunciado.
const CP_LOJA_PRAZO = 'até 20 dias úteis';

const CP_LOJA_ITENS = [
    'camiseta-save-point' => [
        'nome'        => 'Camiseta Save Point',
        'resumo'      => 'Camiseta de algodão com a estampa Save Point, desenhada pela equipe da loja.',
        'preco'       => 8990,
        'opcao_label' => 'Tamanho',
        'opcoes'      => ['P', 'M', 'G', 'GG'],
        'ficha'       => [
            'Algodão penteado 30.1, gola com reforço em ribana',
            'Estampa frontal centralizada, composição própria da loja',
            'Modelagem unissex, do P ao GG',
            'Estampada depois que o pedido é confirmado',
        ],
    ],
    'caneca-continue' => [
        'nome'        => 'Caneca Continue?',
        'resumo'      => 'Caneca de cerâmica de 325 ml com a arte Continue?, da linha própria da loja.',
        'preco'       => 5990,
        'opcao_label' => '',
        'opcoes'      => [],
        'ficha'       => [
            'Cerâmica branca, 325 ml, alça reforçada',
            'Impressão por sublimação em volta de toda a peça',
            'Pode ir ao micro-ondas; lavar à mão preserva a arte por mais tempo',
            'Impressa depois que o pedido é confirmado',
        ],
    ],
    'poster-fase-01' => [
        'nome'        => 'Pôster A3 Fase 01',
        'resumo'      => 'Pôster A3 em papel fosco de 250 g com a arte Fase 01, composta pela loja.',
        'preco'       => 4990,
        'opcao_label' => '',
        'opcoes'      => [],
        'ficha'       => [
            'Formato A3: 29,7 × 42 cm',
            'Papel fosco de 250 g, impressão digital',
            'Enviado em tubo rígido, sem moldura',
            'Impresso depois que o pedido é confirmado',
        ],
    ],
];
