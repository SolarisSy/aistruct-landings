<?php
declare(strict_types=1);
/**
 * /contato/enviar.php — recebe de verdade a mensagem do formulario de atendimento.
 *
 * O formulario antigo so fazia preventDefault() e guardava um numero no localStorage:
 * dizia "mensagem registrada" sem nada sair do navegador. Este endpoint grava de
 * verdade, em dois destinos que NAO dependem de configuracao nenhuma:
 *
 *   1. arquivo JSONL no servidor  (RJ_STORE_DIR, default /var/www/dados)
 *   2. log do processo (stderr)   -> aparece no log do container, sem credencial
 *
 * Um terceiro destino (aviso por e-mail) existe no codigo mas fica INERTE por
 * padrao: so entra em acao se as variaveis SMTP estiverem setadas no servico.
 * A operacao roda hoje sem SMTP, e esse e' o estado NORMAL — nao e' falha, nao
 * gera log de erro, e nada no site depende dele. Para ligar o aviso depois basta
 * setar RJ_MAIL_TO + RJ_SMTP_HOST/USER/PASS: nenhuma linha de codigo muda.
 *
 * Por isso o texto do site fala em mensagem REGISTRADA com protocolo, e nunca em
 * resposta enviada — o endpoint jamais afirma o que nao aconteceu.
 *
 * Nenhum segredo mora neste arquivo: host/usuario/senha/destino, quando existirem,
 * vem de variavel de ambiente do servico.
 *
 * Variaveis de ambiente reconhecidas (todas opcionais):
 *   RJ_MAIL_TO        destino das notificacoes (ex.: caixa central de verificacoes)
 *   RJ_SMTP_HOST      ex.: smtp.gmail.com
 *   RJ_SMTP_PORT      465 (TLS implicito) ou 587 (STARTTLS). Default 465.
 *   RJ_SMTP_TLS       'ssl' ou 'starttls' (default: pela porta)
 *   RJ_SMTP_CAFILE    CA propria, se o servidor de e-mail exigir
 *   RJ_SMTP_USER      usuario SMTP
 *   RJ_SMTP_PASS      senha de aplicativo
 *   RJ_SMTP_FROM      remetente (default: RJ_SMTP_USER)
 *   RJ_STORE_DIR      diretorio de gravacao (default /var/www/dados)
 */

// ---------------------------------------------------------------- utilidades

/**
 * Corte/contagem em caracteres UTF-8 sem depender de mbstring estar habilitado
 * (a imagem base pode nao ter a extensao; fatal error aqui derrubaria o unico
 * canal de atendimento do site).
 */
function rj_cut(string $v, int $max): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($v, 0, $max, 'UTF-8');
    }
    $p = preg_split('//u', $v, -1, PREG_SPLIT_NO_EMPTY);
    return $p === false ? substr($v, 0, $max) : implode('', array_slice($p, 0, $max));
}

function rj_len(string $v): int
{
    if (function_exists('mb_strlen')) {
        return (int) mb_strlen($v, 'UTF-8');
    }
    $n = preg_match_all('/./us', $v);
    return $n === false ? strlen($v) : $n;
}

/** Remove CR/LF: impede injecao de cabecalho no envelope SMTP. */
function rj_1linha(string $v, int $max = 200): string
{
    $v = str_replace(["\r", "\n", "\0"], ' ', $v);
    $v = trim(preg_replace('/\s+/u', ' ', $v) ?? '');
    return rj_cut($v, $max);
}

function rj_texto(string $v, int $max = 4000): string
{
    $v = str_replace(["\r\n", "\r"], "\n", $v);
    $v = preg_replace('/[^\P{C}\n]+/u', '', $v) ?? '';
    return rj_cut(trim($v), $max);
}

function rj_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (empty($_SERVER[$k])) {
            continue;
        }
        $v = trim(explode(',', (string) $_SERVER[$k])[0]);
        if (filter_var($v, FILTER_VALIDATE_IP)) {
            return $v;
        }
    }
    return '0.0.0.0';
}

function rj_store_dir(): ?string
{
    $dir = getenv('RJ_STORE_DIR') ?: '/var/www/dados';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    return is_dir($dir) && is_writable($dir) ? $dir : null;
}

/** Limite simples por IP: 5 mensagens por hora. Evita que o canal vire relay de spam. */
function rj_excedeu_limite(string $ip): bool
{
    $dir = rj_store_dir();
    if ($dir === null) {
        return false;
    }
    $arq = $dir . '/rate-' . substr(hash('sha256', $ip), 0, 24) . '.txt';
    $agora = time();
    $marcas = [];
    if (is_file($arq)) {
        foreach (explode("\n", (string) @file_get_contents($arq)) as $l) {
            $t = (int) trim($l);
            if ($t > 0 && $agora - $t < 3600) {
                $marcas[] = $t;
            }
        }
    }
    if (count($marcas) >= 5) {
        return true;
    }
    $marcas[] = $agora;
    @file_put_contents($arq, implode("\n", $marcas), LOCK_EX);
    return false;
}

function rj_protocolo(): string
{
    return sprintf('SP-%s-%04d', date('Ymd'), random_int(1000, 9999));
}

// ------------------------------------------------------------------- e-mail

/**
 * O aviso por e-mail so existe se o servico tiver as quatro variaveis setadas.
 * Sem elas o bloco inteiro fica dormente: e' o estado esperado da operacao, nao
 * um erro — por isso nada e' escrito no log quando isto devolve false.
 */
function rj_smtp_configurado(): bool
{
    foreach (['RJ_SMTP_HOST', 'RJ_SMTP_USER', 'RJ_SMTP_PASS', 'RJ_MAIL_TO'] as $v) {
        if (trim((string) getenv($v)) === '') {
            return false;
        }
    }
    return true;
}

/** Le a resposta do servidor SMTP (trata continuacao "250-"). */
function rj_smtp_le($fp): array
{
    $linhas = [];
    while (($l = fgets($fp, 1024)) !== false) {
        $linhas[] = rtrim($l, "\r\n");
        if (strlen($l) < 4 || $l[3] !== '-') {
            break;
        }
    }
    $cod = $linhas ? (int) substr($linhas[0], 0, 3) : 0;
    return [$cod, implode(' | ', $linhas)];
}

function rj_smtp_cmd($fp, string $cmd, array $ok): array
{
    fwrite($fp, $cmd . "\r\n");
    [$cod, $txt] = rj_smtp_le($fp);
    return [in_array($cod, $ok, true), $cod, $txt];
}

/**
 * Envia por SMTP autenticado (465 SSL ou 587 STARTTLS), sem dependencia externa.
 * Devolve [bool enviado, string detalhe].
 */
function rj_envia_email(string $assunto, string $corpo, string $replyTo): array
{
    if (!rj_smtp_configurado()) {
        return [false, 'smtp nao configurado'];
    }
    $host = trim((string) getenv('RJ_SMTP_HOST'));
    $user = trim((string) getenv('RJ_SMTP_USER'));
    $pass = (string) getenv('RJ_SMTP_PASS');
    $para = trim((string) getenv('RJ_MAIL_TO'));
    $porta = (int) (getenv('RJ_SMTP_PORT') ?: 465);
    $de = trim((string) getenv('RJ_SMTP_FROM')) ?: $user;
    // TLS: 'ssl' = implicito (porta 465) · 'starttls' = negociado (587).
    // Default pela porta, mas explicitavel — ha host com TLS implicito fora da 465.
    $modo = strtolower(trim((string) getenv('RJ_SMTP_TLS')));
    if ($modo !== 'ssl' && $modo !== 'starttls') {
        $modo = $porta === 465 ? 'ssl' : 'starttls';
    }

    $dsn = ($modo === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $porta;
    $ssl = ['SNI_enabled' => true, 'peer_name' => $host];
    $cafile = trim((string) getenv('RJ_SMTP_CAFILE'));   // CA propria, quando o host exigir
    if ($cafile !== '' && is_readable($cafile)) {
        $ssl['cafile'] = $cafile;
    }
    $ctx = stream_context_create(['ssl' => $ssl]);
    $fp = @stream_socket_client($dsn, $errno, $errstr, 8, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        // diagnostico util no log do container: sem o transporte ssl nao ha como
        // falar com a porta 465 (extensao openssl ausente na imagem base).
        $tr = implode(',', stream_get_transports());
        return [false, "conexao falhou: $errno $errstr (transportes: $tr)"];
    }
    stream_set_timeout($fp, 12);

    try {
        [$cod] = rj_smtp_le($fp);
        if ($cod !== 220) {
            return [false, "banner $cod"];
        }
        $ehlo = 'EHLO ' . (gethostname() ?: 'localhost');
        [$ok, $c, $t] = rj_smtp_cmd($fp, $ehlo, [250]);
        if (!$ok) {
            return [false, "ehlo $c $t"];
        }
        if ($modo === 'starttls') {
            [$ok, $c] = rj_smtp_cmd($fp, 'STARTTLS', [220]);
            if (!$ok) {
                return [false, "starttls $c"];
            }
            stream_context_set_option($fp, ['ssl' => $ssl]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                return [false, 'handshake tls falhou'];
            }
            [$ok, $c] = rj_smtp_cmd($fp, $ehlo, [250]);
            if (!$ok) {
                return [false, "ehlo pos-tls $c"];
            }
        }
        [$ok, $c] = rj_smtp_cmd($fp, 'AUTH LOGIN', [334]);
        if (!$ok) {
            return [false, "auth $c"];
        }
        [$ok, $c] = rj_smtp_cmd($fp, base64_encode($user), [334]);
        if (!$ok) {
            return [false, "user $c"];
        }
        [$ok, $c] = rj_smtp_cmd($fp, base64_encode($pass), [235]);
        if (!$ok) {
            return [false, "senha $c"];
        }
        [$ok, $c] = rj_smtp_cmd($fp, "MAIL FROM:<$de>", [250]);
        if (!$ok) {
            return [false, "mail from $c"];
        }
        [$ok, $c] = rj_smtp_cmd($fp, "RCPT TO:<$para>", [250, 251]);
        if (!$ok) {
            return [false, "rcpt $c"];
        }
        [$ok, $c] = rj_smtp_cmd($fp, 'DATA', [354]);
        if (!$ok) {
            return [false, "data $c"];
        }

        $cab = [
            'From: Saude Plena <' . $de . '>',
            'To: <' . $para . '>',
            'Subject: =?UTF-8?B?' . base64_encode($assunto) . '?=',
            'Date: ' . date('r'),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        if ($replyTo !== '') {
            array_splice($cab, 2, 0, ['Reply-To: <' . $replyTo . '>']);
        }
        $msg = implode("\r\n", $cab) . "\r\n\r\n" . chunk_split(base64_encode($corpo), 76, "\r\n");
        fwrite($fp, $msg . "\r\n.\r\n");
        [$cod, $t] = rj_smtp_le($fp);
        if ($cod !== 250) {
            return [false, "envio $cod $t"];
        }
        rj_smtp_cmd($fp, 'QUIT', [221]);
        return [true, 'entregue ao servidor smtp'];
    } finally {
        @fclose($fp);
    }
}

// -------------------------------------------------------------------- saida

function rj_quer_json(): bool
{
    $a = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $x = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    return str_contains($a, 'application/json') || $x === 'fetch';
}

function rj_responde(int $status, array $dados): void
{
    http_response_code($status);
    header('Cache-Control: no-store');
    if (rj_quer_json()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
    $ok = (bool) ($dados['ok'] ?? false);
    $titulo = $ok ? 'Mensagem registrada' : 'Não foi possível registrar';
    $texto = (string) ($dados['mensagem'] ?? '');
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="robots" content="noindex">'
        . '<title>' . htmlspecialchars($titulo, ENT_QUOTES) . ' — Saúde Plena</title>'
        . '<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' '
        . 'viewBox=\'0 0 64 64\'%3E%3Crect width=\'64\' height=\'64\' rx=\'10\' fill=\'%232f4f3e\'/%3E'
        . '%3Cpath d=\'M12 22l20-9 20 9-20 9z\' fill=\'%23f0ece1\'/%3E%3Cpath d=\'M12 22v20l20 9V31z\' '
        . 'fill=\'%23c8d2c6\'/%3E%3Cpath d=\'M52 22v20l-20 9V31z\' fill=\'%239c4a2f\'/%3E%3C/svg%3E">'
        . '<link rel="stylesheet" href="/ativos/base.css"></head><body>'
        . '<div class="rule"><span></span></div>'
        . '<div class="wrap lay"><div class="col" style="padding:38px 0 60px">'
        . '<h1>' . htmlspecialchars($titulo, ENT_QUOTES) . '</h1>'
        . '<p class="lead">' . htmlspecialchars($texto, ENT_QUOTES) . '</p>'
        . '<p><a class="btn" href="/central-de-ajuda/">Voltar ao início</a></p>'
        . '<p class="up"><a href="/contato/">Escrever outra mensagem</a> · '
        . '<a href="/perguntas-frequentes/">Perguntas frequentes</a></p>'
        . '</div></div></body></html>';
    exit;
}

// ------------------------------------------------------------------ fluxo

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    rj_responde(405, [
        'ok' => false,
        'mensagem' => 'Esta página só recebe o envio do formulário de atendimento.',
    ]);
}

$nome = rj_1linha((string) ($_POST['nome'] ?? ''), 120);
$email = rj_1linha((string) ($_POST['email'] ?? ''), 160);
$assunto = rj_1linha((string) ($_POST['assunto'] ?? 'Abrir uma solicitação'), 80);
$mensagem = rj_texto((string) ($_POST['mensagem'] ?? ''));
$isca = trim((string) ($_POST['empresa'] ?? ''));   // honeypot: humano nao preenche

if ($isca !== '') {
    // Nao devolve pista para o robo; nada e' gravado nem enviado.
    rj_responde(200, ['ok' => true, 'protocolo' => rj_protocolo(), 'mensagem' => 'Mensagem recebida.']);
}

$erros = [];
if (rj_len($nome) < 2) {
    $erros[] = 'informe o seu nome';
}
// E-mail e' opcional: fica junto do registro para identificar quem escreveu. So
// e' recusado quando foi preenchido e esta' claramente invalido.
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'confira o e-mail digitado (ou deixe o campo em branco)';
}
if (rj_len($mensagem) < 12) {
    $erros[] = 'descreva o que aconteceu em algumas linhas';
}
if ($erros) {
    rj_responde(422, [
        'ok' => false,
        'mensagem' => 'Antes de enviar: ' . implode('; ', $erros) . '.',
    ]);
}

$ip = rj_ip();
if (rj_excedeu_limite($ip)) {
    rj_responde(429, [
        'ok' => false,
        'mensagem' => 'Recebemos várias mensagens deste acesso na última hora. '
            . 'Aguarde um pouco antes de escrever de novo — nenhuma delas se perdeu.',
    ]);
}

$protocolo = rj_protocolo();
$quando = date('c');

$registro = [
    'protocolo' => $protocolo,
    'em' => $quando,
    'nome' => $nome,
    'email' => $email,
    'assunto' => $assunto,
    'mensagem' => $mensagem,
    'ip' => $ip,
    'ua' => rj_1linha((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 180),
];

// 1) arquivo no servidor
$gravou = false;
$dir = rj_store_dir();
if ($dir !== null) {
    $arq = $dir . '/contatos.jsonl';
    $linha = json_encode($registro, JSON_UNESCAPED_UNICODE) . "\n";
    $gravou = @file_put_contents($arq, $linha, FILE_APPEND | LOCK_EX) !== false;
    if ($gravou) {
        @chmod($arq, 0600);
    }
}

// 2) log do processo (chega no log do container mesmo sem nenhuma variavel setada)
error_log(sprintf(
    '[contato] %s | %s | %s <%s> | %s',
    $protocolo,
    $assunto,
    $nome,
    $email !== '' ? $email : 'sem e-mail',
    str_replace("\n", ' / ', $mensagem)
));

// 3) aviso por e-mail — DORMENTE por padrao (ver cabecalho do arquivo).
// Sem as variaveis SMTP nada acontece aqui e nada vai para o log: e' o estado
// normal da operacao, nao uma falha. Log de erro so quando o SMTP ESTA setado e
// mesmo assim o envio nao completou — ai' sim ha o que investigar.
$enviou = false;
if (rj_smtp_configurado()) {
    $corpo = "Nova mensagem pelo formulário do site.\n\n"
        . "Protocolo: $protocolo\n"
        . "Recebida em: $quando\n"
        . "Assunto: $assunto\n"
        . "Nome: $nome\n"
        . 'E-mail informado: ' . ($email !== '' ? $email : 'não informado') . "\n\n"
        . "Relato:\n$mensagem\n";
    [$enviou, $detalhe] = rj_envia_email("[$protocolo] $assunto — $nome", $corpo, $email);
    if (!$enviou) {
        error_log("[contato] $protocolo aviso por e-mail nao saiu: $detalhe");
    }
}

if (!$gravou && !$enviou) {
    rj_responde(503, [
        'ok' => false,
        'mensagem' => 'O atendimento está indisponível neste momento e a sua mensagem não foi '
            . 'registrada. Tente de novo em alguns minutos.',
    ]);
}

rj_responde(200, [
    'ok' => true,
    'protocolo' => $protocolo,
    'mensagem' => "Mensagem registrada no atendimento sob o protocolo $protocolo. Guarde este "
        . 'número: é por ele que o seu caso é localizado quando você escrever de novo pelo '
        . 'formulário. A leitura do relato acontece em até 1 dia útil.',
]);
