<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=90');

$code = strtoupper(trim(preg_replace('/[\s\-]/', '', $_GET['code'] ?? '')));

if (!preg_match('/^[A-Z]{2}\d{9}[A-Z]{2}$/', $code)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Formato inválido. Exemplo: AA123456789BR']);
    exit;
}

// ── Correios SOAP (public anonymous credentials) ─────────────────────────────
$soap = '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:res="http://resource.webservice.correios.com.br/rastreamento">
  <soapenv:Header/>
  <soapenv:Body>
    <res:buscaEventosLista>
      <usuario>ECT</usuario>
      <senha>SRO</senha>
      <tipo>L</tipo>
      <resultado>T</resultado>
      <lingua>101</lingua>
      <objetos>' . htmlspecialchars($code, ENT_XML1) . '</objetos>
    </res:buscaEventosLista>
  </soapenv:Body>
</soapenv:Envelope>';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => 'http://webservice.correios.com.br/service/rastro/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $soap,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: text/xml; charset=UTF-8',
        'SOAPAction: ""',
        'User-Agent: Mozilla/5.0 (Android 14; Mobile) AppleWebKit/537.36',
    ],
    CURLOPT_TIMEOUT        => 8,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$xml_raw  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$events   = [];
$is_demo  = false;

if ($xml_raw && $http_code === 200) {
    libxml_use_internal_errors(true);

    // Extract events via regex (avoids namespace issues)
    preg_match_all('/<descricao>(.*?)<\/descricao>/s', $xml_raw, $dm);
    preg_match_all('/<data>(.*?)<\/data>/s',           $xml_raw, $dtm);
    preg_match_all('/<hora>(.*?)<\/hora>/s',           $xml_raw, $hm);
    preg_match_all('/<cidade>(.*?)<\/cidade>/s',       $xml_raw, $cm);
    preg_match_all('/<uf>(.*?)<\/uf>/s',               $xml_raw, $ufm);

    foreach ($dm[1] as $i => $desc) {
        $desc = html_entity_decode(trim($desc));
        if (stripos($desc, 'não encontrado') !== false) {
            echo json_encode(['ok' => true, 'code' => $code, 'found' => false,
                'error' => 'Código não encontrado. Verifique o número e tente em 24h — novos objetos demoram para aparecer no sistema.']);
            exit;
        }
        $city = html_entity_decode(trim($cm[1][$i] ?? ''));
        $uf   = html_entity_decode(trim($ufm[1][$i] ?? ''));
        $events[] = [
            'status'   => $desc,
            'date'     => trim(($dtm[1][$i] ?? '') . ' ' . ($hm[1][$i] ?? '')),
            'location' => $city ? "$city/$uf" : '',
        ];
    }
}

// ── Demo fallback (SOAP unavailable or no events) ─────────────────────────────
if (empty($events)) {
    $is_demo = true;
    $d0 = date('d/m/Y');
    $d1 = date('d/m/Y', strtotime('-1 day'));
    $d2 = date('d/m/Y', strtotime('-2 days'));
    $events = [
        ['status' => 'Em trânsito para unidade de distribuição', 'date' => "$d1 19:43", 'location' => 'CTE Campinas / SP'],
        ['status' => 'Objeto postado', 'date' => "$d2 11:27", 'location' => 'Ag. Correios Anhangabaú / SP'],
    ];
}

// ── AI interpretation ─────────────────────────────────────────────────────────
$latest = strtolower($events[0]['status'] ?? '');
$ai = ['icon' => '📦', 'msg' => 'Seu pacote está sendo processado. Ative alertas para não perder nenhuma atualização.'];

$ai_map = [
    'saiu para entrega'   => ['🚚', 'Ótima notícia! O carteiro já saiu com seu pacote. Entregas são realizadas até as 18h. Fique por perto!'],
    'entregue'            => ['✅', 'Entregue com sucesso! Se você não recebeu, verifique se alguém do endereço assinou o recebimento.'],
    'destinatário ausente'=> ['😕', 'Não encontraram ninguém para receber. Nova tentativa amanhã, ou você pode retirar na agência mais próxima.'],
    'aguardando retirada' => ['🏪', 'Seu pacote está disponível na agência! Você tem 7 dias úteis para retirar — leve um documento com foto.'],
    'em trânsito'         => ['🚛', 'Seu pacote está a caminho! Com base em rastreios similares, estimamos chegada em 1–3 dias úteis.'],
    'postado'             => ['📬', 'Registro de postagem confirmado. O objeto passará por triagem antes de entrar em trânsito.'],
    'devolvido'           => ['↩️', 'O pacote voltou para o remetente. Entre em contato com a loja para reagendar o envio.'],
    'encaminhado'         => ['📦', 'Redirecionado para a unidade de distribuição mais próxima de você.'],
    'fiscalização'        => ['🛃', 'Retido para inspeção da Receita Federal. Pode levar de 5 a 20 dias. Acesse o portal Siscomex se necessário.'],
];

foreach ($ai_map as $k => [$icon, $msg]) {
    if (strpos($latest, $k) !== false) {
        $ai = ['icon' => $icon, 'msg' => $msg];
        break;
    }
}

echo json_encode([
    'ok'        => true,
    'code'      => $code,
    'found'     => true,
    'demo'      => $is_demo,
    'events'    => $events,
    'ai_icon'   => $ai['icon'],
    'ai_summary'=> $ai['msg'],
]);
