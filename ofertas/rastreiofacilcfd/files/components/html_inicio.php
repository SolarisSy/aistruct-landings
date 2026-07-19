<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo TITULO_SITE; ?></title>
    <?php arquivo('style.css') ?>
    <link rel="shortcut icon" href="<?php arquivo('correios-icon-1.png', false); ?>" type="image/x-icon">

    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="">
    <link rel="stylesheet" href="<?php echo FILES . 'css/' ?>bootstrap.css">
    <link rel="stylesheet" href="<?php echo FILES . 'css/' ?>app.css">
    <!-- <link rel="stylesheet" href="<?php echo FILES . 'css/' ?>all-1.css"> -->
    <!-- <link rel="stylesheet" href="<?php echo FILES . 'css/' ?>all.css"> -->
    <link rel="stylesheet" href="<?php echo FILES . 'css/' ?>yellow.css">

    <!-- SEO -->
    <meta property="og:type" content="website">
    <meta name="title" content="<?php echo TITULO_SITE; ?>">
    <meta name="description" content="<?php echo $config_descricao_site ?>">
    <meta name="keywords" content="<?php echo $config_keywords_site ?>">
    <meta property="og:title" content="<?php echo TITULO_SITE; ?>">
    <meta property="twitter:title" content="<?php echo TITULO_SITE; ?>">
    <meta property="og:description" content="<?php echo $config_descricao_site ?>">
    <meta property="twitter:description" content="<?php echo $config_descricao_site ?>">
    <meta property="og:url" content="<?php echo LINK_DOMINIO ?>">
    <meta property="twitter:url" content="<?php echo LINK_DOMINIO ?>">
    <meta property="og:image" content="<?php arquivo('favicon.webp', false); ?>">
    <meta property="twitter:image" content="<?php arquivo('favicon.webp', false); ?>">
    <meta property="twitter:card" content="summary_large_image">

    <?php include('files/codigos-personalizados/head.php'); ?>

    <?php include('files/components/verificar_inspecionar_desktop.php'); ?>
    <?php include('files/components/verificar_clonagem.php'); ?>
</head>

<body <?php echo $atributos_body; ?>>
    <?php include('files/codigos-personalizados/body.php'); ?>