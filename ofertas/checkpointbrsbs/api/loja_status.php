<?php
/**
 * GET /api/loja_status.php?id=<numero da cobranca> — a tela do PIX pergunta se
 * o pagamento ja caiu.
 *
 * Devolve SO {pago:bool,status:string}. Nunca devolve dado do comprador, valor,
 * endereco nem corpo do gateway.
 *
 * So responde sobre cobranca que ESTA rota abriu: numero que nao esta no
 * registro desta linha recebe 404 seco. Sem isso, a rota viraria consulta de
 * status de qualquer cobranca da conta para quem adivinhasse um numero.
 */
require __DIR__ . '/_loja.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    cp_json_out(['ok' => false, 'erro' => 'Método não permitido.'], 405);
}

function cp_loja_404(): void
{
    http_response_code(404);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo '{"detail":"Not Found"}';
    exit;
}

$id = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($_GET['id'] ?? ''));
if ($id === '' || !cp_loja_conhece($id)) {
    cp_loja_404();
}

// Caminho rapido: o postback ja marcou como pago (sem custo de chamada externa).
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
    // Consulta e acessoria: falhar aqui nao pode virar erro na tela de quem ja pagou.
    cp_json_out(['ok' => true, 'pago' => false, 'status' => 'consultando']);
}

$tx   = json_decode((string) $resp, true);
$st   = strtolower((string) (is_array($tx) ? ($tx['status'] ?? '') : ''));
$pago = in_array($st, ['paid', 'approved'], true);
if ($pago) {
    @file_put_contents(cp_dir('pagos') . '/' . cp_attr_chave($id), '1');
}
cp_json_out(['ok' => true, 'pago' => $pago, 'status' => $st !== '' ? $st : 'desconhecido']);
