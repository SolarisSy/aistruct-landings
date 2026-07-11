<?php
declare(strict_types=1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, X-API-Key, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

const API_KEY = '15962e353834cb3b85f4c24ba06715b54017d27888c976e3562c067760d6e042';

function provedor_url(string $cpf, bool $tokenInQuery): string
{
    $base = 'https://api.zipcardx.online/api/v1/cpf/' . $cpf;
    if ($tokenInQuery) {
        $base .= (str_contains($base, '?') ? '&' : '?') . 'token=' . rawurlencode(API_KEY);
    }
    return $base;
}

function only_digits(?string $s): string
{
    if ($s === null || $s === '') {
        return '';
    }
    return preg_replace('/\D+/', '', $s) ?? '';
}

/** @param mixed $json */
function normalize_payload($json): array
{
    if (is_array($json)) {
        if (isset($json['data']) && is_array($json['data'])) {
            $p = $json['data'];
        } elseif (isset($json['dados'])) {
            $p = $json['dados'];
        } else {
            $p = $json;
        }
    } else {
        $p = [];
    }

    if (is_array($p) && array_is_list($p)) {
        $p = $p[0] ?? [];
    }
    if (!is_array($p)) {
        $p = [];
    }

    return [
        'CPF' => $p['CPF'] ?? null,
        'NOME' => $p['NOME'] ?? null,
        'SEXO' => $p['SEXO'] ?? null,
        'NASC' => $p['NASC'] ?? null,
        'NOME_MAE' => $p['NOME_MAE'] ?? null,
        'NOME_PAI' => $p['NOME_PAI'] ?? null,
    ];
}

function http_get(string $url, array $headers): array
{
    $ch = curl_init($url);
    if ($ch === false) {
        return ['code' => 0, 'body' => ''];
    }

    $headerLines = [];
    foreach ($headers as $k => $v) {
        $headerLines[] = $k . ': ' . $v;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_HTTPHEADER => $headerLines,
    ]);

    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $code,
        'body' => is_string($body) ? $body : '',
    ];
}

$cpf = only_digits($_GET['cpf'] ?? '');

if (strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode([
        'statusCode' => 400,
        'error' => 'CPF inválido',
        'data' => null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$attempts = [
    [
        'name' => 'X-API-Key',
        'headers' => ['Accept' => 'application/json', 'X-API-Key' => API_KEY],
        'tokenInQuery' => false,
    ],
    [
        'name' => 'Bearer',
        'headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . API_KEY],
        'tokenInQuery' => false,
    ],
    [
        'name' => 'AuthorizationToken',
        'headers' => ['Accept' => 'application/json', 'Authorization' => 'Token ' . API_KEY],
        'tokenInQuery' => false,
    ],
    [
        'name' => 'QueryToken',
        'headers' => ['Accept' => 'application/json'],
        'tokenInQuery' => true,
    ],
];

$lastHttp = 0;
$lastErrorMessage = 'Falha ao consultar provedor';

foreach ($attempts as $auth) {
    $url = provedor_url($cpf, (bool) $auth['tokenInQuery']);
    $resp = http_get($url, $auth['headers']);
    $code = $resp['code'];
    $body = $resp['body'];
    $lastHttp = $code;

    if ($body !== '') {
        $dec = json_decode($body, true);
        if (!is_array($dec)) {
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', $body) ?? $body;
            $dec = json_decode($clean, true);
        }

        if (is_array($dec)) {
            if (!empty($dec['error'])) {
                $lastErrorMessage = (string) $dec['error'];
            }
            $payload = normalize_payload($dec);
            $hasData = $payload['CPF'] || $payload['NOME'] || $payload['SEXO'] || $payload['NASC'];
            if (($code >= 200 && $code < 300) || $hasData) {
                http_response_code(200);
                echo json_encode([
                    'statusCode' => $code ?: 200,
                    'data' => $payload,
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }

    if ($code === 401 || $code === 403) {
        continue;
    }
}

$status = $lastHttp ?: 401;
http_response_code($status);
echo json_encode([
    'statusCode' => $status,
    'error' => $lastErrorMessage,
    'data' => [
        'CPF' => null,
        'NOME' => null,
        'SEXO' => null,
        'NASC' => null,
        'NOME_MAE' => null,
        'NOME_PAI' => null,
    ],
], JSON_UNESCAPED_UNICODE);
