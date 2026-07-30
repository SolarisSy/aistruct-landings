<?php
/**
 * /taxa/ir.php — ultimo elo medido antes do checkout externo.
 *
 * Registra `checkout_click` e manda pro pay.asegupag.com preservando a query inteira
 * (src/utm_content = click id). E o que separa "a pagina /taxa/ nao convence" de
 * "o checkout do TopperPay perde a venda" — sem isso os dois sao o mesmo numero.
 *
 * ⚠️ Este arquivo esta no CAMINHO DO DINHEIRO. O redirect acontece SEMPRE:
 * a telemetria e best-effort e nunca pode impedir a ida pro checkout.
 */
require_once __DIR__ . '/../_evt.php';

$CHECKOUT = 'https://pay.asegupag.com/checkout/9e94cca3-f46e-4e45-a57b-e430f0aa6782';

function g($k) { return isset($_GET[$k]) ? trim((string)$_GET[$k]) : ''; }
$cid = g('src') ?: g('gclid') ?: g('gbraid') ?: g('wbraid');

// repassa so o que o checkout entende — nao propaga lixo da URL
$fwd = [];
foreach (['src','utm_content','utm_source','utm_medium','utm_campaign','utm_term'] as $k) {
    if (g($k) !== '') { $fwd[$k] = g($k); }
}
if ($cid !== '' && !isset($fwd['src']))         { $fwd['src'] = $cid; }
if ($cid !== '' && !isset($fwd['utm_content'])) { $fwd['utm_content'] = $cid; }

// telemetria protegida: qualquer falha aqui e' irrelevante perto de perder a venda
try { evt_log('checkout_click', $cid); } catch (Throwable $e) { /* ignora */ }

$destino = $CHECKOUT . (empty($fwd) ? '' : ('?' . http_build_query($fwd)));
header('Cache-Control: no-store');
header('Location: ' . $destino, true, 302);
exit;
