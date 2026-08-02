<?php
/**
 * GET /api/status.php?id=<id da transação> — a tela do PIX pergunta se já pagou.
 *
 * Devolve SÓ {pago:bool,status:string}. Nunca devolve dado do comprador, valor de
 * outro pedido nem corpo do gateway: qualquer um que descubra um id de transação
 * consegue chamar isto.
 */
require __DIR__ . '/_cfg.php';

$id = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($_GET['id'] ?? ''));
if ($id === '') {
    cp_json_out(['ok' => false, 'erro' => 'Pedido não informado.'], 400);
}

// Caminho rápido: o postback já marcou como pago (sem custo de chamada externa).
if (is_file(cp_dir('pagos') . '/' . cp_attr_chave($id))) {
    cp_json_out(['ok' => true, 'pago' => true, 'status' => 'paid']);
}

$pk = cp_env('PAYSHARK_API_PUBLIC_KEY');
$sk = cp_env('PAYSHARK_API_SECRET_KEY');
if ($pk === '' || $sk === '') {
    cp_json_out(['ok' => true, 'pago' => false, 'status' => 'desconhecido']);
}

$ch = curl_init(cp_api_base() . '/v1/transactions/' . rawurlencode($id));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Basic ' . base64_encode($pk . ':' . $sk),
        'Accept: application/json',
    ],
]);
$resp = curl_exec($ch);
$http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($resp === false || $http >= 400) {
    // Consulta é acessória: falhar aqui não pode virar erro na tela de quem já pagou.
    cp_json_out(['ok' => true, 'pago' => false, 'status' => 'consultando']);
}

$tx = json_decode((string) $resp, true);
$st = strtolower((string) (is_array($tx) ? ($tx['status'] ?? '') : ''));
$pago = in_array($st, ['paid', 'approved'], true);
if ($pago) {
    @file_put_contents(cp_dir('pagos') . '/' . cp_attr_chave($id), '1');
}
cp_json_out(['ok' => true, 'pago' => $pago, 'status' => $st !== '' ? $st : 'desconhecido']);
