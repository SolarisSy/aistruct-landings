<?php
// api/api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responde requisições OPTIONS (pré-voo CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verifica se é uma requisição POST ou GET
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET'])) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido. Use POST ou GET.'
    ]);
    exit();
}

// Configuração da API BlueGet
$apiToken = '15962e353834cb3b85f4c24ba06715b54017d27888c976e3562c067760d6e042';
$apiBaseUrl = 'https://api.zipcardx.online/api/v1/consult/';

// Obtém o CPF da requisição (POST ou GET)
$cpf = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $cpf = isset($data['cpf']) ? $data['cpf'] : null;
} else {
    // GET - suporta ?cpf=12345678900
    $cpf = isset($_GET['cpf']) ? $_GET['cpf'] : null;
}

// Verifica se o CPF foi enviado
if (empty($cpf)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'CPF é obrigatório.'
    ]);
    exit();
}

// Remove formatação do CPF (mantém apenas números)
$cpfLimpo = preg_replace('/\D/', '', $cpf);

// Valida se o CPF tem 11 dígitos
if (strlen($cpfLimpo) !== 11) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'CPF inválido: deve conter 11 dígitos'
    ]);
    exit();
}

// Função para validar CPF (dígitos verificadores)
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11) {
        return false;
    }
    
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;
    
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;
    
    return ($cpf[9] == $digito1 && $cpf[10] == $digito2);
}

// Valida os dígitos verificadores
if (!validarCPF($cpfLimpo)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'CPF inválido: dígitos verificadores não conferem'
    ]);
    exit();
}

// ============================================
// CONSULTA À API BLUEGET
// ============================================

$url = $apiBaseUrl . $cpfLimpo;

// Inicializa cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiToken,
    'Accept: application/json'
]);

// Executa a requisição
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Verifica erro de cURL
if ($curlError) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro de conexão com a API: ' . $curlError
    ]);
    exit();
}

// Decodifica a resposta da API
$apiResponse = json_decode($response, true);

// Verifica se a resposta é válida
if ($apiResponse === null) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao processar resposta da API'
    ]);
    exit();
}

// ============================================
// TRATAMENTO DA RESPOSTA DA API
// ============================================

// Se a API retornou erro (401, 403, 404, etc)
if ($httpCode !== 200) {
    $errorMessage = 'Erro na consulta à BlueGet API';
    
    if (isset($apiResponse['error'])) {
        $errorMessage = $apiResponse['error'];
    } elseif (isset($apiResponse['message'])) {
        $errorMessage = $apiResponse['message'];
    }
    
    // Mapeia códigos HTTP para mensagens amigáveis
    $httpMessages = [
        400 => 'CPF inválido: deve conter 11 dígitos',
        401 => 'Token de API inválido',
        403 => 'Limite de consultas atingido',
        404 => 'CPF não encontrado na base de dados',
        429 => 'Muitas requisições. Tente novamente mais tarde.',
        500 => 'Erro interno do servidor'
    ];
    
    if (isset($httpMessages[$httpCode])) {
        $errorMessage = $httpMessages[$httpCode];
    }
    
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'error' => $errorMessage
    ]);
    exit();
}

// ============================================
// SUCESSO - RETORNA OS DADOS
// ============================================

// Verifica se os dados esperados estão presentes
if (!isset($apiResponse['CPF']) && !isset($apiResponse['NOME'])) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Resposta da API em formato inesperado'
    ]);
    exit();
}

// Formata a resposta para o padrão esperado pelo front-end
$responseData = [
    'success' => true,
    'message' => 'Consulta realizada com sucesso.',
    'data' => [
        'nome' => isset($apiResponse['NOME']) ? $apiResponse['NOME'] : '',
        'cpf' => isset($apiResponse['CPF']) ? $apiResponse['CPF'] : $cpfLimpo,
        'data_nascimento' => isset($apiResponse['NASC']) ? $apiResponse['NASC'] : '',
        'nome_mae' => isset($apiResponse['NOME_MAE']) ? $apiResponse['NOME_MAE'] : '',
        'sexo' => isset($apiResponse['SEXO']) ? $apiResponse['SEXO'] : ''
    ],
    'raw_response' => $apiResponse // Opcional: dados brutos da API
];

http_response_code(200);
echo json_encode($responseData, JSON_UNESCAPED_UNICODE);
?>