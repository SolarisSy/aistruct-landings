<?php
/**
 * Telemetria server-side do funil -> topperpay-bridge (POST /event).
 *
 * Por que existe: entre "chegou na money" (Adspect) e "pagou" (webhook do TopperPay)
 * nao havia NENHUM registro. O gargalo (620 CPFs digitados -> 2 vendas) so podia ser
 * INFERIDO pelo consumo de credito da API de CPF. Isso mede de verdade.
 *
 * Regras que este arquivo NUNCA quebra:
 *   1. NAO manda dado pessoal. So evento + click id + um rotulo curto.
 *   2. NAO atrasa nem derruba a pagina. Timeout curto e falha em silencio —
 *      telemetria quebrada jamais pode custar uma venda.
 *   3. NAO e pixel de conversao. Roda server-side, invisivel no HTML, mantendo a
 *      regra de ouro do CRR (nada de pixel na money cloakada).
 */

function evt_log(string $event, string $gclid = '', string $meta = ''): void {
    $url = getenv('EVENT_URL') ?: '';
    $tok = getenv('EVENT_TOKEN') ?: '';
    if ($url === '' || $tok === '') { return; }   // nao configurado = no-op silencioso

    $payload = json_encode([
        'event' => $event,
        'gclid' => substr($gclid, 0, 200),
        'meta'  => substr($meta, 0, 60),
    ]);

    $ch = curl_init($url . '?token=' . urlencode($tok));
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        // agressivo de proposito: a pagina nao espera telemetria.
        CURLOPT_CONNECTTIMEOUT_MS => 400,
        CURLOPT_TIMEOUT_MS        => 900,
        CURLOPT_SSL_VERIFYPEER    => true,
    ]);
    @curl_exec($ch);
    @curl_close($ch);
}
