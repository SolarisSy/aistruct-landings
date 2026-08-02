<?php
/**
 * GET /api/catalogo.php[?sku=...]
 *
 * O que a tela do checkout exibe. Existe para que o valor MOSTRADO ao comprador
 * saia da mesma função que a cobrança usa (cp_preco), e não de um número escrito
 * no HTML. Se o preço mudar aqui, muda na tela e na cobrança no mesmo instante.
 * Não recebe nem devolve nada do comprador.
 */
require __DIR__ . '/_cfg.php';
require __DIR__ . '/_gate.php';
cp_gate_exige();

header('Cache-Control: no-store');

$sku = isset($_GET['sku']) ? preg_replace('/[^a-z\-]/', '', (string) $_GET['sku']) : '';

$itens = [];
foreach (array_keys(CP_SKUS) as $s) {
    if (isset(CP_SKUS[$s]['variantes'])) {
        foreach (CP_SKUS[$s]['variantes'] as $v) {
            $p = cp_preco($s, $v);
            if ($p) {
                $p['id'] = $s . ':' . $v;
                $itens[] = $p;
            }
        }
    } else {
        $p = cp_preco($s);
        if ($p) {
            $p['id'] = $s;
            $itens[] = $p;
        }
    }
}

if ($sku !== '') {
    $itens = array_values(array_filter($itens, function ($i) use ($sku) {
        return $i['sku'] === $sku;
    }));
    if (!$itens) {
        cp_json_out(['ok' => false, 'erro' => 'Item não encontrado no catálogo.'], 404);
    }
}

cp_json_out([
    'ok'        => true,
    'pagamento' => 'PIX à vista',
    'itens'     => $itens,
]);
