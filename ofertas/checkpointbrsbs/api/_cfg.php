<?php
/**
 * Checkout da Checkpoint BR — base comum (config, tabela de preço, estado em disco).
 *
 * Roda no MESMO container da loja (nginx + php-fpm da imagem richarvey/nginx-php-fpm),
 * o mesmo que já executa go/index.php. Por isso o checkout responde em
 * https://checkpointbr.sbs/checkout.html e https://checkpointbr.sbs/api/*.php —
 * nenhum salto para outro domínio, que é justamente o que a revisão reprovou.
 *
 * REGRA CENTRAL DESTE ARQUIVO: o preço é definido AQUI, no servidor, a partir do SKU.
 * A tela do checkout não escolhe valor — ela PEDE o valor a este mesmo código
 * (api/catalogo.php) e exibe o que recebe; api/pix.php recalcula pela mesma função
 * antes de cobrar. Não existe caminho em que a tela mostre um número e a cobrança
 * saia com outro.
 */

// ── Env: o EasyPanel injeta no container, mas o php-fpm pode nascer com clear_env=on.
// Por isso a leitura tenta, em ordem: getenv() → $_SERVER → $_ENV → o ambiente do
// PID 1 (o processo que o Docker iniciou COM as variáveis). Sem esse último degrau,
// uma env configurada no painel pode simplesmente não existir para o PHP.
function cp_env(string $chave, string $padrao = ''): string
{
    static $proc = null;

    $v = getenv($chave);
    if ($v === false || $v === '') {
        $v = $_SERVER[$chave] ?? ($_ENV[$chave] ?? '');
    }
    if ($v === '' || $v === false) {
        if ($proc === null) {
            $proc = [];
            if (@is_readable('/proc/1/environ')) {
                $raw = @file_get_contents('/proc/1/environ');
                foreach (explode("\0", (string) $raw) as $par) {
                    $pos = strpos($par, '=');
                    if ($pos > 0) {
                        $proc[substr($par, 0, $pos)] = substr($par, $pos + 1);
                    }
                }
            }
        }
        $v = $proc[$chave] ?? '';
    }
    $v = trim((string) $v);
    return $v !== '' ? $v : $padrao;
}

function cp_api_base(): string
{
    return rtrim(cp_env('PAYSHARK_API_BASE', 'https://api.paysharkgateway.com.br'), '/');
}

/** URL pública desta loja. Vem do Host da requisição; env só para sobrescrever. */
function cp_self_base(): string
{
    $env = rtrim(cp_env('SELF_BASE_URL'), '/');
    if ($env !== '') {
        return $env;
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'checkpointbr.sbs';
    $host = preg_replace('/[^A-Za-z0-9.\-:]/', '', (string) $host);
    $prot = (($_SERVER['HTTPS'] ?? '') === 'on'
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
    return $prot . '://' . $host;
}

// ── Catálogo: o que a loja publica, em centavos ───────────────────────────────
// Os valores de tabela são os do catálogo que já está no ar (index.html e
// catalogo.html). O desconto de 5% no PIX à vista também é do catálogo — é a
// condição anunciada para esta forma de pagamento, então é ela que vale na
// cobrança. Arredondamento SEMPRE para baixo (intdiv), nunca a favor da loja.
const CP_SKUS = [
    'reserva-padrao' => [
        'label'    => 'Reserva de Lançamento — Padrão',
        'tabela'   => 24990,
        'desconto' => 5,
        'entrega'  => 'Chave enviada no dia em que o título libera a pré-carga, com aviso por e-mail 48 horas antes.',
    ],
    'reserva-ampliada' => [
        'label'    => 'Reserva de Lançamento — Ampliada',
        'tabela'   => 34990,
        'desconto' => 5,
        'entrega'  => 'Chave enviada no dia da pré-carga, com o conteúdo adicional da edição intermediária quando a distribuidora oferece.',
    ],
    'reserva-colecao' => [
        'label'    => 'Reserva de Lançamento — Coleção',
        'tabela'   => 44990,
        'desconto' => 5,
        'entrega'  => 'Chave enviada no dia da pré-carga, com prioridade na fila de envio.',
    ],
    'vale-presente' => [
        'label'     => 'Vale-presente Checkpoint',
        'variantes' => [5000, 10000, 20000],
        'desconto'  => 0,
        'entrega'   => 'Código de crédito por e-mail em até quinze minutos depois da confirmação.',
    ],
];

// ── Texto UTF-8 sem depender de mbstring ─────────────────────────────────────
// substr()/strlen() contam BYTES: cortar em 120 no meio de um caractere acentuado
// produz UTF-8 invalido, e json_encode devolve false — o pedido morreria sem erro
// visivel. PCRE com /u conta e corta por CARACTERE e e nucleo do PHP.
function cp_tamanho(string $s): int
{
    $n = preg_match_all('/./us', $s);
    return $n === false ? strlen($s) : (int) $n;
}

function cp_corta(string $s, int $max): string
{
    if (cp_tamanho($s) <= $max) {
        return $s;
    }
    $ps = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
    return is_array($ps) ? implode('', array_slice($ps, 0, $max)) : substr($s, 0, $max);
}

function cp_centavos_br(int $c): string
{
    return 'R$ ' . number_format($c / 100, 2, ',', '.');
}

/**
 * Preço do pedido — ÚNICA função que decide quanto se cobra.
 * Devolve null para SKU/variante que a loja não vende (o pedido é recusado).
 */
function cp_preco(string $sku, $variante = null): ?array
{
    if (!isset(CP_SKUS[$sku])) {
        return null;
    }
    $item = CP_SKUS[$sku];

    if (isset($item['variantes'])) {
        $v = (int) $variante;
        if (!in_array($v, $item['variantes'], true)) {
            return null;   // valor de vale-presente fora da tabela publicada
        }
        $tabela = $v;
        $rotulo = $item['label'] . ' — ' . cp_centavos_br($v);
    } else {
        $tabela = (int) $item['tabela'];
        $rotulo = $item['label'];
    }

    $pct     = (int) $item['desconto'];
    $cobrado = $pct > 0 ? intdiv($tabela * (100 - $pct), 100) : $tabela;

    return [
        'sku'            => $sku,
        'variante'       => isset($item['variantes']) ? (int) $variante : null,
        'label'          => $item['label'],
        'titulo_pedido'  => $rotulo,
        'entrega'        => $item['entrega'],
        'tabela'         => $tabela,
        'tabela_br'      => cp_centavos_br($tabela),
        'desconto_pct'   => $pct,
        'desconto_valor' => $tabela - $cobrado,
        'desconto_br'    => cp_centavos_br($tabela - $cobrado),
        'cobrado'        => $cobrado,
        'cobrado_br'     => cp_centavos_br($cobrado),
    ];
}

// ── Estado em disco (FORA do docroot: nada aqui é servido pelo nginx) ─────────
function cp_dir(string $sub = ''): string
{
    $base = rtrim(cp_env('CHECKOUT_STATE_DIR', sys_get_temp_dir() . '/checkpointbr'), '/');
    $p = $sub === '' ? $base : $base . '/' . $sub;
    if (!is_dir($p)) {
        @mkdir($p, 0700, true);
    }
    return $p;
}

function cp_json_out($dados, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function cp_body_json(): array
{
    $raw = file_get_contents('php://input');
    $d = json_decode((string) $raw, true);
    return is_array($d) ? $d : [];
}

// ── Identificador do clique ──────────────────────────────────────────────────
const CP_CLICK_KEYS = ['gclid', 'gbraid', 'wbraid', 'src', 'clickid', 'cid'];
const CP_UTM_KEYS   = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id'];
const CP_CLICK_RE   = '/^[A-Za-z0-9_.\-]{6,400}$/';

function cp_limpa_attr($attr): array
{
    if (!is_array($attr)) {
        return [];
    }
    $out = [];
    foreach (array_merge(CP_CLICK_KEYS, CP_UTM_KEYS) as $k) {
        if (!isset($attr[$k]) || is_array($attr[$k])) {
            continue;
        }
        $v = cp_corta(trim((string) $attr[$k]), 400);
        if ($v !== '' && !in_array(strtolower($v), ['none', 'null', 'undefined'], true)) {
            $out[$k] = $v;
        }
    }
    return $out;
}

/** Devolve [identificador, campo de origem]. Vazio = visita sem clique pago. */
function cp_click(array $attr, string $click_id = '', string $click_type = ''): array
{
    $c = trim($click_id);
    if ($c !== '' && preg_match(CP_CLICK_RE, $c)) {
        $tipo = in_array($click_type, CP_CLICK_KEYS, true) ? $click_type : '';
        if ($tipo === '') {
            foreach (CP_CLICK_KEYS as $k) {
                if (($attr[$k] ?? '') === $c) {
                    $tipo = $k;
                    break;
                }
            }
        }
        return [$c, $tipo !== '' ? $tipo : 'clickId'];
    }
    foreach (CP_CLICK_KEYS as $k) {
        $v = trim((string) ($attr[$k] ?? ''));
        if ($v !== '' && preg_match(CP_CLICK_RE, $v)) {
            return [$v, $k];
        }
    }
    return ['', ''];
}

// ── Dado do comprador: mascarar ANTES de gravar ──────────────────────────────
// Nome, e-mail e CPF ficam SÓ no corpo enviado ao gateway. Nada disso entra em
// arquivo, log ou resposta de diagnóstico — a máscara é aplicada na ESCRITA,
// não na leitura, para não depender de quem lê estar do lado certo do portão.
function cp_mascara(string $t): string
{
    $t = preg_replace_callback('/[\w.+\-]+@[\w\-]+\.[\w.\-]+/', function ($m) {
        $p = explode('@', $m[0]);
        return substr($p[0], 0, 1) . '***@' . $p[1];
    }, $t);
    return (string) preg_replace_callback('/\d{8,14}/', function ($m) {
        return str_repeat('*', max(0, strlen($m[0]) - 2)) . substr($m[0], -2);
    }, (string) $t);
}

function cp_log(string $msg): void
{
    error_log('[checkout] ' . cp_mascara($msg));
}

// ── Contadores (auditoria da marcação, sem dado pessoal) ─────────────────────
function cp_stat(string $chave, int $n = 1): void
{
    $f = cp_dir() . '/stats.json';
    $fh = @fopen($f, 'c+');
    if (!$fh) {
        return;
    }
    if (flock($fh, LOCK_EX)) {
        $atual = json_decode((string) stream_get_contents($fh), true);
        if (!is_array($atual)) {
            $atual = [];
        }
        $atual[$chave] = ((int) ($atual[$chave] ?? 0)) + $n;
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($atual));
        fflush($fh);
        flock($fh, LOCK_UN);
    }
    fclose($fh);
}

function cp_stats(): array
{
    $d = json_decode((string) @file_get_contents(cp_dir() . '/stats.json'), true);
    return is_array($d) ? $d : [];
}

// ── Origem do pedido: guardada por chave do gateway, SEM dado pessoal ────────
// Rede de segurança do mapa: o identificador do clique também viaja no
// externalRef e no metadata da transação, que a PayShark devolve no postback —
// então a atribuição sobrevive mesmo se este disco for perdido num redeploy.
function cp_attr_chave(string $k): string
{
    return substr(hash('sha256', $k), 0, 40);
}

function cp_attr_grava(array $chaves, array $rec): void
{
    foreach ($chaves as $k) {
        if ($k === '') {
            continue;
        }
        @file_put_contents(cp_dir('attr') . '/' . cp_attr_chave($k) . '.json',
                           json_encode($rec, JSON_UNESCAPED_UNICODE));
    }
}

function cp_attr_le(array $chaves): ?array
{
    foreach ($chaves as $k) {
        if ($k === '') {
            continue;
        }
        $f = cp_dir('attr') . '/' . cp_attr_chave($k) . '.json';
        if (is_file($f)) {
            $d = json_decode((string) @file_get_contents($f), true);
            if (is_array($d)) {
                return $d;
            }
        }
    }
    return null;
}

// ── Conversão: a venda de volta ao Google, pelo servidor ─────────────────────
// A loja NÃO carrega pixel nem tag do Google. Entregar a conversão pelo browser
// exigiria expor esta página ao Google; aqui o caminho é o inverso — o servidor
// envia o identificador do clique para o destino de importação offline.
// DORMENTE por padrão: sem GADS_SHEET_WEBHOOK_URL nada sai daqui, a venda só
// fica registrada. É proposital — dá para provar que o identificador chega antes
// de ligar o envio.

/** Hora da conversão no formato do import offline: AAAA-MM-DD HH:MM:SS-03:00 */
function cp_hora_conversao(string $pago_em = ''): string
{
    $off = cp_env('TZ_OFFSET', '-03:00');
    try {
        $dt = $pago_em !== '' ? new DateTime($pago_em) : new DateTime('now', new DateTimeZone('UTC'));
    } catch (Exception $e) {
        $dt = new DateTime('now', new DateTimeZone('UTC'));
    }
    try {
        $dt->setTimezone(new DateTimeZone($off));
    } catch (Exception $e) {
        $dt->setTimezone(new DateTimeZone('-03:00'));
    }
    return $dt->format('Y-m-d H:i:s') . $off;
}

/** Registro local da conversão (sem dado do comprador) — auditoria da marcação. */
function cp_conv_grava(array $conv): void
{
    @file_put_contents(cp_dir() . '/conversoes.jsonl',
                       json_encode($conv, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
}

function cp_conversoes(int $n = 50): array
{
    $f = cp_dir() . '/conversoes.jsonl';
    if (!is_file($f)) {
        return [];
    }
    $linhas = array_slice(array_filter(explode("\n", (string) @file_get_contents($f))), -$n);
    $out = [];
    foreach (array_reverse($linhas) as $l) {
        $d = json_decode($l, true);
        if (is_array($d)) {
            $out[] = $d;
        }
    }
    return $out;
}

/** Envia a conversão ao destino de importação offline. false = não enviou. */
function cp_envia_conversao(array $conv): bool
{
    if (($conv['click_type'] ?? '') !== 'gclid') {
        // gbraid/wbraid não entram na coluna "Google Click ID" — exigem coluna própria.
        cp_stat('conv_sem_coluna');
        return false;
    }
    $url = cp_env('GADS_SHEET_WEBHOOK_URL');
    if ($url === '') {
        cp_stat('conv_sink_desligado');
        return false;
    }
    $corpo = [
        'secret'              => cp_env('GADS_SHEET_SECRET'),
        'gclid'               => $conv['click_id'],
        'conversion_name'     => $conv['acao'],
        'conversion_time'     => $conv['hora_conv'],
        'conversion_value'    => number_format((float) $conv['valor'], 2, '.', ''),
        'conversion_currency' => $conv['moeda'],
        'order_id'            => (string) $conv['pedido'],
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($corpo),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    cp_stat($http > 0 && $http < 300 ? 'conv_sink_ok' : 'conv_sink_erro');
    return $http > 0 && $http < 300;
}
