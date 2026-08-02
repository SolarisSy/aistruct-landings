<?php
declare(strict_types=1);
/**
 * /contato/ler.php — LEITURA do registro de atendimento gravado por enviar.php.
 *
 * POR QUE ESTE ARQUIVO EXISTE
 * As mensagens do formulario sao gravadas em /var/www/dados/contatos.jsonl, FORA
 * do document root. Nao ha e-mail (a operacao roda sem SMTP, por decisao) e o
 * painel do servidor nao expoe terminal nem log deste servico. Sem este endpoint
 * as mensagens entram e ninguem le — o que tornaria FALSA a frase da politica de
 * privacidade e da pagina de reembolso: "usamos um canal so justamente para que
 * nenhum pedido se perca". Pedido de exclusao/correcao (LGPD) e pedido de
 * cancelamento chegam por aqui; alguem precisa conseguir ler.
 *
 * O QUE ELE SERVE: DADO PESSOAL REAL de visitantes (nome, e-mail, relato livre,
 * IP, navegador). Vazamento aqui e' incidente de protecao de dados, nao bug de
 * site. Dai as decisoes abaixo.
 *
 * DECISOES DE SEGURANCA
 *  1. SOMENTE LEITURA. Nao existe rota de escrita, edicao ou exclusao. Descartar
 *     registro e' operacao de servidor, nunca um clique numa pagina web.
 *  2. Token vem de VARIAVEL DE AMBIENTE (RJ_LER_TOKEN), nunca do codigo. Este
 *     arquivo e' versionado num repositorio publico: qualquer segredo escrito
 *     aqui ja' nasce vazado.
 *  3. Env AUSENTE/vazia => 404. Nao "500", nao "token nao configurado". Um erro
 *     falante confirma que o caminho existe e vira alvo.
 *  4. Token ERRADO => o MESMO 404, byte a byte igual ao 404 do proprio nginx.
 *     401/403 responderiam a pergunta que o atacante veio fazer ("existe algo
 *     aqui?"). Para quem varre o dominio, /contato/ler.php e' indistinguivel de
 *     /contato/qualquer-coisa.
 *  5. Comparacao com hash_equals() sobre o SHA-256 dos dois lados. hash_equals
 *     e' de tempo constante (== e strcmp saem no primeiro byte diferente e
 *     vazam o token por medicao de tempo); comparar os HASHES em vez dos valores
 *     iguala o comprimento das strings, fechando tambem o vazamento de tamanho.
 *  6. Forca bruta: 10 falhas por IP por hora e o endereco so recebe 404 pelo
 *     resto da janela. Mesmo mecanismo de arquivo deslizante que enviar.php ja'
 *     usa para o formulario, em balde separado (uma tentativa falha aqui nao
 *     consome a cota de quem escreve pelo formulario, e vice-versa).
 *  7. O token NAO e' logado, nem no error_log, nem ecoado na pagina. Nada neste
 *     arquivo escreve no log.
 *  8. Tudo que veio do visitante sai por htmlspecialchars(). O campo mensagem e'
 *     texto livre: sem escape, o proprio painel seria um XSS armazenado contra
 *     quem le. Reforcado por CSP default-src 'none' (a pagina nao carrega
 *     nenhum recurso externo e nao executa script algum).
 *  9. noindex/nofollow no meta e no cabecalho X-Robots-Tag, Cache-Control:
 *     no-store, Referrer-Policy: no-referrer. Fora do sitemap.xml e sem nenhum
 *     link apontando para ca' em qualquer pagina do site.
 *
 * COMO O TOKEN CHEGA (escolha uma):
 *   - cabecalho:  X-Atendimento-Token: <token>
 *   - query:      /contato/ler.php?k=<token>          (pratico no navegador)
 * O access_log do nginx esta' desligado nesta imagem, entao a query nao fica
 * gravada em disco; ainda assim o cabecalho e' o caminho preferido.
 *
 * FILTRO OPCIONAL: ?desde=AAAA-MM-DD  (mostra so' o que entrou dessa data em
 * diante). Formato invalido e' ignorado, com aviso na tela.
 *
 * ENV RECONHECIDAS
 *   RJ_LER_TOKEN   (obrigatoria — sem ela o endpoint nao existe: 404)
 *   RJ_STORE_DIR   (opcional, default /var/www/dados — o mesmo de enviar.php)
 */

// ------------------------------------------------------------------ ambiente

/**
 * O painel injeta a env no container, mas o php-fpm pode nascer com clear_env.
 * Ordem: getenv() -> $_SERVER -> $_ENV -> ambiente do PID 1 (o processo que o
 * Docker iniciou COM as variaveis). A imagem oficial php:*-fpm ja' vem com
 * clear_env = no, entao o primeiro degrau resolve; os outros sao rede de
 * seguranca para o dia em que a imagem base mudar.
 */
function rjl_env(string $chave): string
{
    static $proc = null;

    $v = getenv($chave);
    if ($v === false || $v === '') {
        $v = (string) ($_SERVER[$chave] ?? ($_ENV[$chave] ?? ''));
    }
    if ($v === '') {
        if ($proc === null) {
            $proc = [];
            if (@is_readable('/proc/1/environ')) {
                foreach (explode("\0", (string) @file_get_contents('/proc/1/environ')) as $par) {
                    $pos = strpos($par, '=');
                    if ($pos > 0) {
                        $proc[substr($par, 0, $pos)] = substr($par, $pos + 1);
                    }
                }
            }
        }
        $v = (string) ($proc[$chave] ?? '');
    }
    return trim((string) $v);
}

// ---------------------------------------------------------------------- 404

/**
 * Resposta identica a' que o proprio nginx devolve em qualquer caminho
 * inexistente deste site (try_files ... =404, com server_tokens off).
 * Mesmo corpo, mesmo Content-Type, sem cabecalho que denuncie PHP — por isso o
 * header_remove('X-Powered-By') e a ausencia de qualquer header extra: um
 * X-Robots-Tag num 404 seria justamente a pista de que ali mora algo.
 */
function rjl_404(): void
{
    header_remove('X-Powered-By');
    http_response_code(404);
    header('Content-Type: text/html');
    echo "<html>\r\n"
        . "<head><title>404 Not Found</title></head>\r\n"
        . "<body>\r\n"
        . "<center><h1>404 Not Found</h1></center>\r\n"
        . "<hr><center>nginx</center>\r\n"
        . "</body>\r\n"
        . "</html>\r\n";
    exit;
}

// ------------------------------------------------------------------ arquivos

function rjl_store_dir(): string
{
    $dir = rjl_env('RJ_STORE_DIR');
    return $dir !== '' ? $dir : '/var/www/dados';
}

function rjl_ip(): string
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

/** Balde de FALHAS por IP (janela deslizante de 1h) — separado do balde do formulario. */
function rjl_arq_balde(string $ip): string
{
    return rjl_store_dir() . '/rate-ler-' . substr(hash('sha256', $ip), 0, 24) . '.txt';
}

function rjl_falhas(string $ip): int
{
    $arq = rjl_arq_balde($ip);
    if (!is_file($arq)) {
        return 0;
    }
    $agora = time();
    $n = 0;
    foreach (explode("\n", (string) @file_get_contents($arq)) as $l) {
        $t = (int) trim($l);
        if ($t > 0 && $agora - $t < 3600) {
            $n++;
        }
    }
    return $n;
}

function rjl_registra_falha(string $ip): void
{
    $arq = rjl_arq_balde($ip);
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
    $marcas[] = $agora;
    @file_put_contents($arq, implode("\n", array_slice($marcas, -50)), LOCK_EX);
    @chmod($arq, 0600);
}

/**
 * Le so' o FIM do arquivo. O registro cresce por append e nunca e' rotacionado
 * aqui (este endpoint nao escreve): ler tudo de uma vez seria um estouro de
 * memoria esperando a oferta rodar tempo suficiente.
 */
function rjl_linhas_finais(string $arq, int $maxBytes = 2097152): array
{
    $fp = @fopen($arq, 'rb');
    if (!$fp) {
        return [];
    }
    $tam = (int) @filesize($arq);
    $cortou = false;
    if ($tam > $maxBytes) {
        @fseek($fp, -$maxBytes, SEEK_END);
        $cortou = true;
    }
    $bruto = (string) @stream_get_contents($fp);
    @fclose($fp);
    $linhas = preg_split('/\n/', $bruto) ?: [];
    if ($cortou) {
        array_shift($linhas);   // primeira linha veio cortada ao meio
    }
    return $linhas;
}

// -------------------------------------------------------------------- saida

function e(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// --------------------------------------------------------------------- fluxo

// Metodo diferente de leitura nao ganha resposta diferente: 404 igual.
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    rjl_404();
}

$esperado = rjl_env('RJ_LER_TOKEN');
if ($esperado === '' || strlen($esperado) < 16) {
    // Env nao configurada (ou fraca demais para valer como segredo): o endpoint
    // simplesmente NAO EXISTE. Nada de mensagem explicando o que falta.
    rjl_404();
}

$ip = rjl_ip();
if (rjl_falhas($ip) >= 10) {
    rjl_404();
}

$informado = (string) ($_SERVER['HTTP_X_ATENDIMENTO_TOKEN'] ?? ($_GET['k'] ?? ''));
// hash_equals em tempo constante, sobre o SHA-256 dos dois lados: o hash tem
// sempre 64 caracteres, entao nem o TAMANHO do token vaza pela comparacao.
if ($informado === '' || !hash_equals(hash('sha256', $esperado), hash('sha256', $informado))) {
    rjl_registra_falha($ip);
    rjl_404();
}
unset($esperado, $informado);   // fora da memoria antes de montar qualquer saida

// ------------------------------------------------------------------- leitura

$desdeBruto = trim((string) ($_GET['desde'] ?? ''));
$desde = '';
$desdeInvalido = false;
if ($desdeBruto !== '') {
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $desdeBruto, $m)
        && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
        $desde = $desdeBruto;
    } else {
        $desdeInvalido = true;
    }
}

$arq = rjl_store_dir() . '/contatos.jsonl';
$registros = [];
$invalidas = 0;
foreach (rjl_linhas_finais($arq) as $linha) {
    $linha = trim($linha);
    if ($linha === '') {
        continue;
    }
    $r = json_decode($linha, true);
    if (!is_array($r) || !isset($r['protocolo'])) {
        $invalidas++;
        continue;
    }
    if ($desde !== '' && substr((string) ($r['em'] ?? ''), 0, 10) < $desde) {
        continue;
    }
    $registros[] = $r;
}

// Mais recente primeiro. A ordenacao do PHP 8 e' estavel, entao empate de
// carimbo mantem a ordem inversa do arquivo (que ja' e' cronologica).
$registros = array_reverse($registros);
usort($registros, static fn(array $a, array $b): int => strcmp((string) ($b['em'] ?? ''), (string) ($a['em'] ?? '')));

$total = count($registros);

// ------------------------------------------------------------------- resposta

header_remove('X-Powered-By');
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('Pragma: no-cache');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; base-uri 'none'; form-action 'none'");

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
    exit;
}

?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow,noarchive">
<title>Registro de atendimento</title>
<style>
:root{color-scheme:light}
*{box-sizing:border-box}
body{margin:0;padding:22px 16px 60px;background:#f4f3ef;color:#22261f;
     font:15px/1.55 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif}
.wrap{max-width:880px;margin:0 auto}
h1{font-size:20px;margin:0 0 4px}
.sub{color:#5d6357;font-size:13px;margin:0 0 18px}
.aviso{background:#fff6e2;border:1px solid #e6cf9a;padding:9px 12px;border-radius:6px;
       font-size:13px;margin:0 0 16px}
.vazio{background:#fff;border:1px solid #dcdad2;border-radius:8px;padding:26px;text-align:center;color:#5d6357}
.msg{background:#fff;border:1px solid #dcdad2;border-left:4px solid #2f4f3e;border-radius:8px;
     padding:14px 16px;margin:0 0 12px}
.msg.sonda{border-left-color:#9c8b4a;background:#fdfbf3}
.cab{display:flex;flex-wrap:wrap;gap:8px;align-items:baseline;margin-bottom:8px}
.proto{font:600 14px/1.2 ui-monospace,SFMono-Regular,Menlo,Consolas,monospace}
.quando{color:#5d6357;font-size:13px}
.tag{font-size:11px;letter-spacing:.04em;text-transform:uppercase;background:#eceade;
     border:1px solid #d8d4c2;border-radius:99px;padding:2px 8px;color:#5d5233}
.de{font-size:13px;color:#3c4237;margin:0 0 8px}
.de b{font-weight:600}
.corpo{white-space:normal;margin:0;padding:10px 12px;background:#faf9f6;border:1px solid #e6e4dc;border-radius:6px}
.rodape{color:#7a7f73;font-size:12px;margin-top:8px}
.nota{color:#7a7f73;font-size:12px;margin-top:26px;border-top:1px solid #dcdad2;padding-top:12px}
</style>
</head>
<body>
<div class="wrap">
  <h1>Registro de atendimento</h1>
  <p class="sub"><?= $total ?> mensagem<?= $total === 1 ? '' : 's' ?><?php
    if ($desde !== '') {
        echo ' a partir de ' . e($desde);
    } ?> · mais recente primeiro · somente leitura</p>

<?php if ($desdeInvalido): ?>
  <p class="aviso">O filtro <code>desde</code> foi ignorado: use o formato <code>AAAA-MM-DD</code>.</p>
<?php endif; ?>
<?php if ($invalidas > 0): ?>
  <p class="aviso"><?= $invalidas ?> linha<?= $invalidas === 1 ? '' : 's' ?> do arquivo não pôde ser lida e foi pulada.</p>
<?php endif; ?>

<?php if ($total === 0): ?>
  <div class="vazio">Nenhuma mensagem<?= $desde !== '' ? ' nesse período' : '' ?>.</div>
<?php else: ?>
<?php foreach ($registros as $r):
    $mensagem = (string) ($r['mensagem'] ?? '');
    $sonda = stripos($mensagem, 'SONDA') !== false;
    $email = trim((string) ($r['email'] ?? ''));
    $quando = (string) ($r['em'] ?? '');
    $ts = strtotime($quando);
    $quandoLegivel = $ts ? date('d/m/Y H:i', $ts) : $quando;
?>
  <article class="msg<?= $sonda ? ' sonda' : '' ?>">
    <div class="cab">
      <span class="proto"><?= e((string) $r['protocolo']) ?></span>
      <span class="quando"><?= e($quandoLegivel) ?></span>
      <?php if ($sonda): ?><span class="tag">sonda de QA</span><?php endif; ?>
    </div>
    <p class="de"><b><?= e((string) ($r['nome'] ?? '—')) ?></b>
      · <?= $email !== '' ? e($email) : '<span class="quando">sem e-mail</span>' ?>
      <?php if (!empty($r['assunto'])): ?> · <?= e((string) $r['assunto']) ?><?php endif; ?>
    </p>
    <p class="corpo"><?= nl2br(e($mensagem)) ?></p>
    <p class="rodape">IP <?= e((string) ($r['ip'] ?? '—')) ?><?php
      if (!empty($r['ua'])): ?> · <?= e((string) $r['ua']) ?><?php endif; ?></p>
  </article>
<?php endforeach; ?>
<?php endif; ?>

  <p class="nota">Página de leitura. Não altera nem apaga registro — descarte de dados,
  inclusive por pedido de exclusão, é feito no servidor. Filtro por data:
  acrescente <code>&amp;desde=AAAA-MM-DD</code> ao endereço.</p>
</div>
</body>
</html>
