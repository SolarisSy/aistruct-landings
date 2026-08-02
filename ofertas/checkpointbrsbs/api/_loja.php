<?php
/**
 * Linha de produtos fisicos da loja — base comum.
 *
 * Roda no MESMO container e no MESMO dominio das outras rotas de dados, e usa a
 * mesma base (_cfg.php): leitura de ambiente, corte de texto UTF-8, mascara de
 * dado pessoal, mapa de origem do pedido e contadores.
 *
 * REGRA CENTRAL: o valor cobrado e decidido AQUI, no servidor, a partir do
 * codigo do produto. A tela PEDE o valor (loja_catalogo.php) e mostra o que
 * recebe; loja_pix.php recalcula pela mesma funcao antes de cobrar. Nao existe
 * caminho em que o cliente informe preco: qualquer numero que venha no corpo do
 * pedido e ignorado.
 *
 * Esta linha e ABERTA: qualquer visitante compra, sem precisar trazer marca
 * nenhuma. E por isso que ela tem tabela, rotas e registro proprios — nada aqui
 * toca a outra linha de venda.
 */
require_once __DIR__ . '/_cfg.php';
require_once __DIR__ . '/_loja_tabela.php';

const CP_LOJA_UFS = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS',
                     'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC',
                     'SP', 'SE', 'TO'];

// Teto de pedidos por endereco de origem, por hora. A rota e aberta na internet:
// sem isto, um script conseguiria abrir cobranca em serie no gateway.
const CP_LOJA_TETO_HORA = 20;

// ── Preco: UNICA funcao que decide quanto se cobra ───────────────────────────
// Devolve null para codigo que a loja nao vende (o pedido e recusado).
function cp_loja_item(string $sku): ?array
{
    if (!isset(CP_LOJA_ITENS[$sku])) {
        return null;
    }
    $i     = CP_LOJA_ITENS[$sku];
    $preco = (int) $i['preco'];
    $frete = (int) CP_LOJA_FRETE;

    return [
        'sku'          => $sku,
        'nome'         => $i['nome'],
        'resumo'       => $i['resumo'],
        'ficha'        => $i['ficha'],
        'opcao_label'  => $i['opcao_label'],
        'opcoes'       => $i['opcoes'],
        'preco'        => $preco,
        'preco_br'     => cp_centavos_br($preco),
        'frete'        => $frete,
        'frete_br'     => cp_centavos_br($frete),
        'total'        => $preco + $frete,
        'total_br'     => cp_centavos_br($preco + $frete),
        'prazo'        => CP_LOJA_PRAZO,
    ];
}

function cp_loja_catalogo(): array
{
    $out = [];
    foreach (array_keys(CP_LOJA_ITENS) as $sku) {
        $it = cp_loja_item($sku);
        if ($it) {
            $out[] = $it;
        }
    }
    return $out;
}

/** A escolha de tamanho/variante e conferida contra a lista publicada. */
function cp_loja_opcao(array $item, string $enviada): ?string
{
    if (!$item['opcoes']) {
        return '';
    }
    $e = strtoupper(trim($enviada));
    foreach ($item['opcoes'] as $o) {
        if (strtoupper($o) === $e) {
            return $o;
        }
    }
    return null;
}

// ── Dados de entrega ─────────────────────────────────────────────────────────
// Produto fisico precisa chegar em algum lugar. Cada campo e conferido no
// servidor: o que o formulario valida no browser vale como comodidade, nao como
// garantia — o pedido so nasce se passar por aqui.
function cp_loja_texto($v, int $max): string
{
    return cp_corta(trim((string) $v), $max);
}

/** Devolve [dados limpos, erros por campo]. */
function cp_loja_valida(array $cliente, array $entrega): array
{
    $d = [
        'nome'        => cp_loja_texto($cliente['nome'] ?? '', 120),
        'email'       => cp_loja_texto($cliente['email'] ?? '', 160),
        'cpf'         => preg_replace('/\D/', '', (string) ($cliente['cpf'] ?? '')),
        'telefone'    => preg_replace('/\D/', '', (string) ($cliente['telefone'] ?? '')),
        'cep'         => preg_replace('/\D/', '', (string) ($entrega['cep'] ?? '')),
        'rua'         => cp_loja_texto($entrega['rua'] ?? '', 120),
        'numero'      => cp_loja_texto($entrega['numero'] ?? '', 12),
        'complemento' => cp_loja_texto($entrega['complemento'] ?? '', 60),
        'bairro'      => cp_loja_texto($entrega['bairro'] ?? '', 80),
        'cidade'      => cp_loja_texto($entrega['cidade'] ?? '', 80),
        'uf'          => strtoupper(cp_loja_texto($entrega['uf'] ?? '', 2)),
    ];

    $e = [];
    if (cp_tamanho($d['nome']) < 3) {
        $e['nome'] = 'Escreva seu nome completo.';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $e['email'] = 'Escreva um e-mail válido — é nele que o código de acompanhamento chega.';
    }
    if (strlen($d['cpf']) !== 11) {
        $e['cpf'] = 'O CPF precisa ter 11 dígitos.';
    }
    if (strlen($d['telefone']) < 10 || strlen($d['telefone']) > 11) {
        $e['telefone'] = 'Informe DDD e número, com 10 ou 11 dígitos.';
    }
    if (strlen($d['cep']) !== 8) {
        $e['cep'] = 'O CEP precisa ter 8 dígitos.';
    }
    if (cp_tamanho($d['rua']) < 3) {
        $e['rua'] = 'Escreva o nome da rua ou avenida.';
    }
    if ($d['numero'] === '') {
        $e['numero'] = 'Informe o número (ou escreva S/N).';
    }
    if (cp_tamanho($d['bairro']) < 2) {
        $e['bairro'] = 'Informe o bairro.';
    }
    if (cp_tamanho($d['cidade']) < 2) {
        $e['cidade'] = 'Informe a cidade.';
    }
    if (!in_array($d['uf'], CP_LOJA_UFS, true)) {
        $e['uf'] = 'Informe a UF com duas letras (ex.: SP).';
    }
    return [$d, $e];
}

// ── Registro do pedido: mascara aplicada na ESCRITA ──────────────────────────
// O endereco que entrega o pacote viaja no corpo enviado ao processador de
// pagamento, que e onde o pedido e conferido e despachado. O que fica gravado
// aqui e um resumo NAO identificavel, para auditoria: quem le este arquivo nao
// consegue saber quem comprou nem onde mora.
//
// A mascara e aplicada ANTES de gravar, nunca na leitura — assim nao depende de
// quem le estar do lado certo de nenhum portao.
function cp_loja_inicial(string $t): string
{
    $ps = preg_split('/\s+/u', trim($t), -1, PREG_SPLIT_NO_EMPTY);
    if (!$ps) {
        return '';
    }
    $out = [];
    foreach ($ps as $p) {
        $c = preg_split('//u', $p, -1, PREG_SPLIT_NO_EMPTY);
        $out[] = strtoupper((string) ($c[0] ?? '')) . '.';
    }
    return implode(' ', array_slice($out, 0, 4));
}

function cp_loja_registra(string $tx_id, array $extra, array $dados): void
{
    $rec = array_merge($extra, [
        'quando'  => gmdate('c'),
        'entrega' => [
            // cidade/UF ficam legiveis: servem para conferir cobertura de envio e
            // nao identificam ninguem sozinhos.
            'cidade'   => $dados['cidade'],
            'uf'       => $dados['uf'],
            'cep'      => cp_mascara($dados['cep']),
            'rua'      => cp_loja_inicial($dados['rua']),
            'numero'   => '#',
            'bairro'   => cp_loja_inicial($dados['bairro']),
        ],
        'comprador' => [
            'nome'     => cp_loja_inicial($dados['nome']),
            'email'    => cp_mascara($dados['email']),
            'cpf'      => cp_mascara($dados['cpf']),
            'telefone' => cp_mascara($dados['telefone']),
        ],
    ]);
    @file_put_contents(cp_dir('pedidos') . '/' . cp_attr_chave($tx_id) . '.json',
                       json_encode($rec, JSON_UNESCAPED_UNICODE));
}

/** Este numero de cobranca foi aberto por esta linha de produtos? */
function cp_loja_conhece(string $tx_id): bool
{
    return $tx_id !== '' && is_file(cp_dir('pedidos') . '/' . cp_attr_chave($tx_id) . '.json');
}

// ── Teto por origem ──────────────────────────────────────────────────────────
// Guarda so o hash da origem e um contador. Nenhum endereco de rede em claro.
function cp_loja_origem(): string
{
    $ip = (string) ($_SERVER['HTTP_CF_CONNECTING_IP']
        ?? ($_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '')));
    return substr(hash('sha256', $ip . '|' . gmdate('YmdH')), 0, 32);
}

function cp_loja_teto_ok(): bool
{
    $f  = cp_dir('ritmo') . '/' . cp_loja_origem();
    $n  = (int) @file_get_contents($f);
    if ($n >= CP_LOJA_TETO_HORA) {
        return false;
    }
    @file_put_contents($f, (string) ($n + 1));
    return true;
}

// ── Producao e envio: ponto de integracao PENDENTE ───────────────────────────
// O parceiro que estampa e despacha expoe uma API para abrir a ordem de
// producao a partir do pedido pago. A credencial ainda NAO foi provisionada e o
// codigo de cada peca no catalogo do parceiro ainda nao existe — entao aqui nao
// se inventa identificador nem se chama endereco nenhum.
//
// Enquanto isso o despacho e feito a partir do painel do processador de
// pagamento, que recebe o endereco completo no corpo da cobranca. Quando a
// credencial existir, esta funcao passa a ser chamada no evento de pagamento
// confirmado — e sera preciso, nesse momento, decidir de onde ela le o endereco
// (o registro local acima e mascarado de proposito).
function cp_loja_producao(array $pedido): bool
{
    return false;   // dormente: sem credencial, nada e enviado
}
