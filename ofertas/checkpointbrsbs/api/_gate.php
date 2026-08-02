<?php
/**
 * Portao do checkout — quem NAO passou pelo roteador nao ve a pagina de compra.
 *
 * POR QUE NAO E COOKIE (medido, nao suposto — 02/08/2026):
 * a pull zone deste dominio esta com "strip response cookies" ligado. A MESMA
 * requisicao ao /go/ devolve, na origem, `Set-Cookie: _cid=...`; passando pelo CDN
 * o header simplesmente nao existe mais. Ou seja: cookie posto pelo servidor NUNCA
 * chega ao browser neste dominio. Por isso a marca e um TOKEN ASSINADO que viaja
 * na URL — que o CDN nao tem como remover, porque e so um parametro.
 *
 * A marca e emitida em /ir/ (o passo entre o roteador e o checkout) e verificada
 * aqui. Ninguem edita go/index.php: ele continua sendo o integrador do servico.
 *
 * O token e: <expira>.<hmac(expira|impressao do agente)>
 *  - expira  -> janela curta; link copiado morre sozinho.
 *  - agente  -> preso ao User-Agent de quem recebeu; link colado em outro aparelho nao abre.
 *  - hmac    -> chave derivada do segredo do portao; nao da para forjar sem ele.
 *
 * A CHAVE nao esta neste repositorio (que e publico). O que esta aqui e so o
 * SHA-256 dela: o valor em claro vive apenas na configuracao do roteador, viaja
 * no `k=` da pagina principal e e conferido por comparacao de hash.
 */

// SHA-256 do segredo do portao. O segredo em claro NAO mora no repositorio.
const CP_GATE_HASH = '01ce5526baf70906f22d45976a7285173523e637eccf16a44260b968ce7a7c50';

// Vida do token. Alta o bastante para o comprador preencher o formulario e
// acompanhar o PIX (status.php e consultado por ate 30 min depois da cobranca).
const CP_GATE_TTL = 7200;

/** O `k` recebido e o segredo do portao? */
function cp_gate_k_ok(string $k): bool
{
    return $k !== '' && hash_equals(CP_GATE_HASH, hash('sha256', $k));
}

/** Onde a chave derivada fica guardada (fora do docroot — mesmo dir do estado). */
function cp_gate_arquivo(): string
{
    return cp_dir() . '/gate.key';
}

/**
 * Chave de assinatura, derivada do segredo do portao.
 * Deterministica: reinicio do container regenera o MESMO valor, entao token
 * emitido antes de um restart continua valendo.
 */
function cp_gate_deriva(string $k): string
{
    return hash_hmac('sha256', 'cp-gate-v1', $k);
}

/** Grava a chave derivada (so e chamada DEPOIS de o `k` passar no hash). */
function cp_gate_guarda(string $k): string
{
    $chave = cp_gate_deriva($k);
    $f = cp_gate_arquivo();
    if (!is_file($f) || trim((string) @file_get_contents($f)) !== $chave) {
        @file_put_contents($f, $chave);
        @chmod($f, 0600);
    }
    return $chave;
}

/** Le a chave. Vazio = ninguem passou pelo portao desde o ultimo deploy. */
function cp_gate_chave(): string
{
    return trim((string) @file_get_contents(cp_gate_arquivo()));
}

/** Impressao do agente: prende o token ao aparelho que o recebeu. */
function cp_gate_agente(): string
{
    return substr(hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 16);
}

function cp_gate_emite(string $chave): string
{
    $exp = time() + CP_GATE_TTL;
    return $exp . '.' . hash_hmac('sha256', $exp . '|' . cp_gate_agente(), $chave);
}

/** O token apresentado e valido AGORA e para ESTE agente? */
function cp_gate_valido(string $token): bool
{
    $chave = cp_gate_chave();
    if ($chave === '' || $token === '' || strpos($token, '.') === false) {
        return false;
    }
    list($exp, $mac) = explode('.', $token, 2);
    if (!ctype_digit($exp) || (int) $exp < time()) {
        return false;
    }
    return hash_equals(hash_hmac('sha256', $exp . '|' . cp_gate_agente(), $chave), $mac);
}

/** O token desta requisicao (query `g`, ou cookie caso o CDN pare de retira-lo). */
function cp_gate_token(): string
{
    $t = (string) ($_GET['g'] ?? '');
    if ($t === '') {
        $t = (string) ($_COOKIE['cp_g'] ?? '');
    }
    return $t;
}

function cp_gate_ok(): bool
{
    return cp_gate_valido(cp_gate_token());
}

/**
 * Portao das rotas de dados. Sem marca valida: 404 seco, igual ao que o servidor
 * responde para um caminho que nao existe — nao confirma que ha algo aqui.
 * NUNCA cacheavel: com IgnoreQueryStrings ligado no CDN, uma resposta guardada
 * seria servida a outro visitante.
 */
function cp_gate_exige(): void
{
    if (cp_gate_ok()) {
        return;
    }
    http_response_code(404);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo '{"detail":"Not Found"}';
    exit;
}
