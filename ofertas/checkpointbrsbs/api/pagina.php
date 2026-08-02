<?php
/**
 * Serve a pagina de compra — e SO para quem traz a marca do portao.
 *
 * O arquivo da pagina nao fica em lugar servivel: o Dockerfile o move para
 * /var/private, fora do docroot. Nem por engano de configuracao o nginx entrega
 * o HTML direto — para chegar nele e preciso passar por aqui.
 *
 * Sem marca valida a resposta e a HOME, com 302. Nao e 403 nem "acesso negado":
 * quem sondar o endereco ve exatamente o que veria em qualquer caminho comum do
 * site, sem aprender que existe um checkout.
 *
 * O token e injetado nas tres chamadas de dados da pagina no momento de servir —
 * por isso checkout.html continua sem nenhuma logica de portao dentro dele.
 */
require __DIR__ . '/_cfg.php';
require __DIR__ . '/_gate.php';

header('Cache-Control: no-store');

if (!cp_gate_ok()) {
    header('Location: /', true, 302);
    exit;
}

$candidatos = [
    '/var/private/checkout.html',        // producao (movido pelo Dockerfile)
    __DIR__ . '/../_priv/checkout.html', // arvore do repositorio / teste local
];
$arquivo = '';
foreach ($candidatos as $c) {
    if (is_file($c)) {
        $arquivo = $c;
        break;
    }
}
if ($arquivo === '') {
    cp_log('pagina de compra ausente no container');
    header('Location: /', true, 302);
    exit;
}

$html = (string) file_get_contents($arquivo);

// Marca nas chamadas de dados. A pagina pede /api/*.php por caminho relativo;
// aqui cada uma recebe o token, para que catalogo/pix/status reconhecam a mesma
// visita. Sem isto a pagina abriria e a tabela de preco viria fechada.
$g = rawurlencode(cp_gate_token());
$html = str_replace(
    ["'api/catalogo.php?sku=' +", "fetch('api/pix.php'", "'api/status.php?id=' +"],
    ["'api/catalogo.php?g=" . $g . "&sku=' +", "fetch('api/pix.php?g=" . $g . "'", "'api/status.php?g=" . $g . "&id=' +"],
    $html
);

header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
echo $html;
