<?php
/**
 * GET /api/loja_pedidos.php — a lista que quem despacha usa.
 *
 * PARA QUE EXISTE
 * ---------------
 * O pedido nasce com o endereco completo, mas o processador de pagamento nao
 * devolve endereco nos campos proprios (`address`, `shipping` e `delivery`
 * voltam vazios): ele so sobrevive dentro do `metadata` do pedido. Sem esta
 * rota, despachar exigiria abrir o painel do processador e ler JSON cru a mao.
 *
 * DUAS TRANCAS, EM SERIE — FAIL-CLOSED
 * ------------------------------------
 * 1. a MARCA de passagem que as outras rotas de dados ja exigem. Sem ela a rota
 *    nao existe, venha qual segredo vier. Nao basta saber uma string: e preciso
 *    ter passado pelo caminho de entrada, do mesmo aparelho e dentro da janela.
 * 2. o segredo PROPRIO desta rota, LOJA_PEDIDOS_TOKEN, so por cabecalho.
 *
 * O segredo e proprio de proposito e NAO tem substituto. O segredo de
 * diagnostico da operacao abre contadores sem dado pessoal; se ele tambem
 * abrisse esta porta, um escopo de auditoria viraria, de graca, escopo de
 * endereco de comprador. Sem LOJA_PEDIDOS_TOKEN no ambiente a rota nasce
 * FECHADA — e fechada e o estado correto, nao um defeito a contornar.
 *
 * SO CABECALHO, NUNCA NA URL
 * --------------------------
 * O segredo viaja em `X-Token`. Endereco pedido entra no registro de acesso do
 * servidor e de qualquer intermediario no caminho: segredo em query string vira
 * segredo em log, em historico de navegador e em referenciador. Por isso o
 * caminho por `?token=` deixou de existir — com o valor certo tambem responde
 * 404.
 *
 * 404 e nao 403 nas duas trancas de proposito: nao confirma para quem sonda que
 * existe algo atras.
 *
 * MASCARA POR PADRAO
 * ------------------
 *   sem `completo=1`  → indice mascarado: numero do pedido, produto, valor,
 *                       cidade/UF e iniciais. Nao identifica ninguem.
 *   com `completo=1`  → endereco de entrega em claro, LIDO NA HORA do pedido
 *                       registrado no processador de pagamento.
 *
 * Ou seja: o segredo abre a porta, e mesmo dentro dela o padrao continua sendo
 * o dado mascarado — endereco em claro so quando pedido explicitamente.
 *
 * ONDE O DADO MORA
 * ----------------
 * O indice vem do registro local, que nasce mascarado e assim continua. O
 * endereco NAO e guardado deste lado em momento nenhum: `completo=1` faz uma
 * leitura ao vivo por pedido (cp_loja_entrega_remota) e devolve sem gravar.
 * Vazamento do disco do servidor nao entrega endereco de comprador.
 *
 * COMO O OPERADOR ENTRA (as duas trancas nao o deixam de fora)
 * -----------------------------------------------------------
 * A marca de passagem e emitida pelo mesmo passo de entrada que o comprador
 * usa, e quem tem o segredo de entrada a obtem quando quiser: pedir esse passo
 * devolve um salto cuja URL ja traz a marca em `g=`. Copiar esse valor e
 * chamar esta rota com ele MAIS o cabecalho `X-Token`, do MESMO aparelho
 * (a marca e presa ao agente) e dentro da janela de validade. Vencida a
 * janela, repetir o passo de entrada emite outra.
 */
require __DIR__ . '/_loja.php';
require_once __DIR__ . '/_gate.php';

// Teto de leituras ao vivo por chamada: cada uma e uma ida ao processador de
// pagamento. Quem precisar de um pedido especifico passa `id=`.
const CP_LOJA_PED_TETO_VIVO = 12;
const CP_LOJA_PED_TETO_LISTA = 60;

function cp_loja_ped_404(): void
{
    http_response_code(404);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo '{"detail":"Not Found"}';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    cp_loja_ped_404();
}

// ── Tranca 1: a marca de passagem, igual as outras rotas de dados ────────────
// Vem antes do segredo de proposito: quem chega sem marca some daqui sem que o
// ambiente chegue a ser consultado.
if (!cp_gate_ok()) {
    cp_stat('loja_pedidos_recusado');
    cp_gate_exige();   // 404 seco
}

// ── Tranca 2: o segredo PROPRIO desta rota, so por cabecalho ─────────────────
// Sem LOJA_PEDIDOS_TOKEN no ambiente ninguem entra — e nao ha segredo de outro
// escopo que sirva no lugar dele. A URL nao e lida aqui: o valor certo vindo por
// query string nao abre nada, para que segredo nenhum caia no registro de acesso.
$segredo = cp_env('LOJA_PEDIDOS_TOKEN');
$veio    = (string) ($_SERVER['HTTP_X_TOKEN'] ?? '');
if ($segredo === '' || !hash_equals($segredo, $veio)) {
    cp_stat('loja_pedidos_recusado');
    cp_loja_ped_404();
}

$completo = ((string) ($_GET['completo'] ?? '')) === '1';
$so_id    = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($_GET['id'] ?? ''));

// ── Indice: o registro local, que ja nasce mascarado ─────────────────────────
$dir  = cp_dir('pedidos');
$arqs = glob($dir . '/*.json') ?: [];
usort($arqs, function ($a, $b) {
    return filemtime($b) <=> filemtime($a);
});

$pagos = cp_dir('pagos');
$itens = [];
$vivos = 0;

foreach ($arqs as $arq) {
    if (count($itens) >= CP_LOJA_PED_TETO_LISTA) {
        break;
    }
    $rec = json_decode((string) @file_get_contents($arq), true);
    if (!is_array($rec)) {
        continue;
    }
    $tx = (string) ($rec['tx_id'] ?? '');
    if ($so_id !== '' && $tx !== $so_id) {
        continue;
    }

    $linha = [
        'tx_id'    => $tx,
        'pedido'   => (string) ($rec['pedido'] ?? ''),
        'quando'   => (string) ($rec['quando'] ?? ''),
        'sku'      => (string) ($rec['sku'] ?? ''),
        'opcao'    => (string) ($rec['opcao'] ?? ''),
        'total_br' => cp_centavos_br((int) ($rec['total'] ?? 0)),
        'prazo'    => (string) ($rec['prazo'] ?? ''),
        'pago'     => is_file($pagos . '/' . cp_attr_chave($tx)),
        // Continua mascarado, mesmo depois do portao: iniciais, cidade/UF e
        // contato encoberto. Basta para reconhecer o pedido na lista.
        'resumo'   => [
            'comprador' => $rec['comprador'] ?? [],
            'entrega'   => $rec['entrega'] ?? [],
        ],
    ];

    // Endereco em claro: so quando pedido, e sempre por leitura ao vivo.
    if ($completo && $vivos < CP_LOJA_PED_TETO_VIVO) {
        $vivos++;
        $ent = cp_loja_entrega_remota($tx);
        if ($ent === null) {
            $linha['entrega_completa'] = null;
            $linha['entrega_nota']     = 'Não foi possível ler o endereço no processador de '
                . 'pagamento agora. Tente de novo em alguns instantes.';
        } else {
            $linha['entrega_completa'] = $ent;
        }
    } elseif ($completo) {
        $linha['entrega_nota'] = 'Fora do teto de leituras ao vivo desta chamada — '
            . 'consulte este pedido sozinho com id=' . $tx;
    }

    $itens[] = $linha;
}

cp_stat('loja_pedidos_consultado');

cp_json_out([
    'ok'       => true,
    'modo'     => $completo ? 'completo' : 'mascarado',
    'total'    => count($itens),
    // O segredo NAO aparece em nenhum destes enderecos: ele vai no cabecalho
    // `X-Token`, junto da marca de passagem que ja trouxe voce ate aqui (`g=`).
    'como_usar' => [
        'cabecalho' => 'X-Token: <segredo desta rota>',
        'mascarado' => '/api/loja_pedidos.php?g=MARCA',
        'completo'  => '/api/loja_pedidos.php?g=MARCA&completo=1',
        'um_pedido' => '/api/loja_pedidos.php?g=MARCA&completo=1&id=NUMERO_DA_COBRANCA',
    ],
    'pedidos'  => $itens,
]);
