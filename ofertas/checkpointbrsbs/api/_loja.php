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

/**
 * CPF: confere os DOIS digitos verificadores.
 *
 * Contar 11 digitos nao e validar. Documento digitado errado passa no tamanho,
 * a cobranca nasce, e o processador de pagamento — que valida de verdade —
 * substitui em silencio o numero pelo do cadastro que ele ja tem para aquele
 * e-mail. Medido: a cobranca foi gravada no documento de OUTRA pessoa, e a
 * compra seguinte com o mesmo e-mail herdou o cadastro errado. Por isso o
 * documento tem de ser recusado AQUI, antes de qualquer chamada externa.
 *
 * As sequencias de digito repetido (000…, 111…, 999…) fecham a conta do
 * verificador por coincidencia aritmetica — sao recusadas a parte.
 */
function cp_loja_cpf_ok(string $cpf): bool
{
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    // Primeiro verificador usa os 9 primeiros digitos (pesos 10..2); o segundo
    // usa os 10 primeiros (pesos 11..2). Resto 10 vale 0.
    foreach ([9, 10] as $pos) {
        $soma = 0;
        for ($i = 0; $i < $pos; $i++) {
            $soma += ((int) $cpf[$i]) * (($pos + 1) - $i);
        }
        if (((($soma * 10) % 11) % 10) !== (int) $cpf[$pos]) {
            return false;
        }
    }
    return true;
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
    // O e-mail identifica o pedido no atendimento e vai junto da cobranca. NAO
    // prometer envio por aqui: esta oferta nao tem caminho de envio de e-mail
    // (ver o bloco "Aviso ao comprador" no fim deste arquivo).
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $e['email'] = 'Escreva um e-mail válido — é por ele que o atendimento localiza seu pedido.';
    }
    if (!cp_loja_cpf_ok($d['cpf'])) {
        $e['cpf'] = strlen($d['cpf']) !== 11
            ? 'O CPF precisa ter 11 dígitos.'
            : 'Confira o CPF: esses números não formam um documento válido.';
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
// pagamento. O que fica gravado aqui e um resumo NAO identificavel, para
// auditoria: quem le este arquivo nao consegue saber quem comprou nem onde mora.
//
// A mascara e aplicada ANTES de gravar, nunca na leitura — assim nao depende de
// quem le estar do lado certo de nenhum portao.
//
// TENSAO RESOLVIDA — "se o registro nasce mascarado, como despachar?"
// ---------------------------------------------------------------------------
// Quem despacha precisa do endereco REAL; este registro, de proposito, nunca o
// tem. As duas exigencias so colidem se o endereco tiver de estar GUARDADO
// deste lado — e ele nao precisa estar.
//
//   • este arquivo continua sendo o INDICE do pedido: numero, produto, valor,
//     cidade/UF e iniciais. Serve para listar e conferir, nunca para
//     identificar. Vazamento de disco continua nao entregando comprador.
//   • o endereco completo continua vivendo no pedido registrado no processador
//     de pagamento (campo `metadata`), de onde e LIDO NA HORA do despacho por
//     loja_pedidos.php — rota fechada por segredo, que so devolve endereco a
//     quem traz o segredo de operacao.
//
// Resultado: PII em transito para o operador autenticado, nunca PII em repouso
// deste lado. Nenhuma linha abaixo grava endereco, nome, documento ou telefone
// legiveis — e nenhuma deve passar a gravar.
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
// Enquanto isso o despacho e MANUAL: o operador abre loja_pedidos.php com o
// segredo de operacao, le o endereco e posta. Nada nesta funcao esta ligado.
//
// Quando a credencial existir, esta funcao passa a ser chamada no evento de
// pagamento confirmado e le o endereco pela MESMA porta que o operador usa
// (cp_loja_entrega_remota), sem precisar guardar endereco em disco.
function cp_loja_producao(array $pedido): bool
{
    return false;   // dormente: sem credencial, nada e enviado
}

// ── Aviso ao comprador: ponto de integracao PENDENTE ─────────────────────────
// NAO EXISTE caminho de envio de e-mail nesta oferta. Verificado em 03/08/2026,
// antes de escrever qualquer linha que dependesse disso:
//
//   • nenhum segredo de SMTP em profiles/*.env (o modelo .env.example tem os
//     campos, nenhum perfil os preenche);
//   • o servico de e-mail da conta de nuvem esta em modo restrito
//     (ProductionAccessEnabled=false) e sem nenhum remetente verificado — so
//     conseguiria escrever para endereco previamente cadastrado, nunca para o
//     e-mail de um comprador qualquer.
//
// Por isso NENHUMA tela promete e-mail: a pagina do pedido manda guardar o
// numero e falar com o atendimento, que e o que o sistema de fato faz. Prometer
// o que nao acontece e pior que nao prometer.
//
// Quando existir um caminho de envio, e AQUI que ele entra — chamado a partir
// do evento de pagamento confirmado (api/webhook.php), com o numero do pedido,
// o que foi comprado e o prazo. Ligar isto obriga a rever, na mesma mudanca, o
// texto da tela de pagamento confirmado em scripts/_checkpoint_loja_fisica.py.
function cp_loja_aviso_comprador(array $pedido): bool
{
    return false;   // dormente: sem caminho de envio, nada e prometido nem enviado
}

// ── Endereco de entrega: lido do pedido, nunca guardado ──────────────────────
// Le o pedido no processador de pagamento e devolve o endereco que o comprador
// digitou (que viaja em `metadata`, porque o formato de endereco do proprio
// gateway descarta bairro e complemento e vem vazio na consulta).
//
// Devolve null quando nao ha credencial, quando o pedido nao existe ou quando a
// resposta nao traz o bloco de entrega. Nada e gravado em disco aqui.
function cp_loja_entrega_remota(string $tx_id): ?array
{
    $pk = cp_env('PAYSHARK_API_PUBLIC_KEY');
    $sk = cp_env('PAYSHARK_API_SECRET_KEY');
    if ($pk === '' || $sk === '' || $tx_id === '') {
        return null;
    }

    $ch = curl_init(cp_api_base() . '/v1/transactions/' . rawurlencode($tx_id));
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
        return null;
    }
    $tx = json_decode((string) $resp, true);
    if (!is_array($tx)) {
        return null;
    }

    $meta = $tx['metadata'] ?? null;
    if (!is_array($meta)) {
        $meta = json_decode((string) $meta, true);
    }
    $ent = is_array($meta) ? ($meta['entrega'] ?? null) : null;
    if (!is_array($ent)) {
        return null;
    }

    $cli = is_array($tx['customer'] ?? null) ? $tx['customer'] : [];
    return [
        'status'      => strtolower((string) ($tx['status'] ?? '')),
        'nome'        => (string) ($cli['name'] ?? ''),
        'email'       => (string) ($cli['email'] ?? ''),
        'telefone'    => (string) ($ent['telefone'] ?? ($cli['phone'] ?? '')),
        'cep'         => (string) ($ent['cep'] ?? ''),
        'rua'         => (string) ($ent['rua'] ?? ''),
        'numero'      => (string) ($ent['numero'] ?? ''),
        'complemento' => (string) ($ent['complemento'] ?? ''),
        'bairro'      => (string) ($ent['bairro'] ?? ''),
        'cidade'      => (string) ($ent['cidade'] ?? ''),
        'uf'          => (string) ($ent['uf'] ?? ''),
        'sku'         => (string) ($meta['sku'] ?? ''),
        'opcao'       => (string) ($meta['opcao'] ?? ''),
        'pedido'      => (string) ($meta['pedido'] ?? ''),
    ];
}
