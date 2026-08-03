<?php
/**
 * GET /api/loja_pedidos.php?token=… — a lista que quem despacha usa.
 *
 * PARA QUE EXISTE
 * ---------------
 * O pedido nasce com o endereco completo, mas o processador de pagamento nao
 * devolve endereco nos campos proprios (`address`, `shipping` e `delivery`
 * voltam vazios): ele so sobrevive dentro do `metadata` do pedido. Sem esta
 * rota, despachar exigiria abrir o painel do processador e ler JSON cru a mao.
 *
 * PORTAO — FAIL-CLOSED
 * --------------------
 * Sem segredo no ambiente NINGUEM entra, nem com token vazio: a rota some (404).
 * Aceita LOJA_PEDIDOS_TOKEN (segredo proprio, se o gestor separar) e, na falta
 * dele, CHECKOUT_DEBUG_TOKEN — que ja e o segredo de operacao desta oferta e ja
 * esta no ambiente do servico. 404 e nao 403 de proposito: nao confirma para
 * quem sonda que existe algo atras.
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
 */
require __DIR__ . '/_loja.php';

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

$segredo = cp_env('LOJA_PEDIDOS_TOKEN');
if ($segredo === '') {
    $segredo = cp_env('CHECKOUT_DEBUG_TOKEN');
}
$veio = (string) ($_GET['token'] ?? ($_SERVER['HTTP_X_TOKEN'] ?? ''));
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
            . 'consulte este pedido sozinho com &id=' . $tx;
    }

    $itens[] = $linha;
}

cp_stat('loja_pedidos_consultado');

cp_json_out([
    'ok'       => true,
    'modo'     => $completo ? 'completo' : 'mascarado',
    'total'    => count($itens),
    'como_usar' => [
        'mascarado' => '/api/loja_pedidos.php?token=SEGREDO',
        'completo'  => '/api/loja_pedidos.php?token=SEGREDO&completo=1',
        'um_pedido' => '/api/loja_pedidos.php?token=SEGREDO&completo=1&id=NUMERO_DA_COBRANCA',
    ],
    'pedidos'  => $itens,
]);
