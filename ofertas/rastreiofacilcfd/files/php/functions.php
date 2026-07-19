<?php

function imagem($nome_arquivo, $classe_img = '', $id_img = '', $atributos_img = '', $retorno_tipo = 'echo')
{
    $caminho_arquivo = FILES . 'images/' . $nome_arquivo;
    $tag_imagem = '<img class="' . $classe_img . '" id="' . $id_img . '" ' . $atributos_img . ' src="' . $caminho_arquivo . '" alt="">';
    if ($retorno_tipo == 'echo' || $retorno_tipo == '') {
        echo $tag_imagem;
    } else {
        return $tag_imagem;
    }
}

function limparTexto($texto)
{
    $texto = trim(strip_tags($texto));
    $texto = preg_replace('/\s+/', ' ', $texto);
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function limitarPalavras($texto, $limite)
{
    $palavras = explode(' ', $texto);
    if (count($palavras) > $limite) {
        $texto = implode(' ', array_slice($palavras, 0, $limite)) . '...';
    }
    return $texto;
}


function arquivo($nome_arquivo, $retornar_tag = true, $retorno_tipo = 'echo', $atributos = '')
{
    $extensao_arquivo = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
    if (
        $extensao_arquivo == 'jpg' || $extensao_arquivo == 'jpeg' || $extensao_arquivo == 'png' || $extensao_arquivo == 'webp' || $extensao_arquivo == 'gif' ||
        $extensao_arquivo == 'bmp' || $extensao_arquivo == 'avif' || $extensao_arquivo == 'tiff' || $extensao_arquivo == 'tif' || $extensao_arquivo == 'ico'
    ) {
        $extensao_arquivo = 'img';
    }

    switch ($extensao_arquivo) {
        case 'css':
            $caminho_arquivo = FILES . 'css/' . $nome_arquivo;
            $tag_arquivo = '<link ' . $atributos . ' rel="stylesheet" href="' . $caminho_arquivo . '">';
            break;

        case 'js':
            $caminho_arquivo = FILES . 'javascript/' . $nome_arquivo;
            $tag_arquivo = '<script ' . $atributos . ' src="' . $caminho_arquivo . '"></script>';
            break;

        case 'img':
            $caminho_arquivo = FILES . 'images/' . $nome_arquivo;
            $tag_arquivo = '<img ' . $atributos . ' src="' . $caminho_arquivo . '" alt="">';
            break;

        case 'svg':
            $caminho_arquivo =  __DIR__ . '/../svg/' . $nome_arquivo;
            if (file_exists($caminho_arquivo)) {
                $tag_arquivo = file_get_contents($caminho_arquivo);
            } else {
                $tag_arquivo = '<!-- Erro ao exibir SVG -->';
            }
            break;

        default:
            $caminho_arquivo = '';
            $tag_arquivo = '';
            break;
    }

    if ($retornar_tag) {
        $retorno = $tag_arquivo;
    } else {
        $retorno = $caminho_arquivo;
    }
    if ($retorno_tipo == 'echo' || $retorno_tipo == '') {
        echo $retorno;
    } else {
        return $retorno;
    }
}

function dataHora($retorno_tipo = '', $retorno_formato = '', $somaSub = '')
{
    if ($somaSub != '') {
        $dataHoraAtual = new DateTime();
        if (!empty($somaSub)) {
            $somaSub = trim(preg_replace('/\s+/', '', $somaSub));
            if (preg_match('/^([+-])(\d+)([a-zA-Z])$/', $somaSub, $matches)) {
                $sinal = $matches[1];
                $quantidade = $matches[2];
                $unidade = strtolower($matches[3]);
                $unidadesMapeadas = [
                    'd' => 'day',
                    'm' => 'month',
                    'y' => 'year',
                    'h' => 'hour',
                    'i' => 'minute'
                ];
                if (array_key_exists($unidade, $unidadesMapeadas)) {
                    $modificador = $sinal . $quantidade . ' ' . $unidadesMapeadas[$unidade];
                    $dataHoraAtual->modify($modificador);
                }
            } else {
                return "Resultado Inválido";
            }
        }
        $dataHoraAtual = $dataHoraAtual->format('d/m/Y H:i:s');
        $dataHoraAtual = DateTime::createFromFormat('d/m/Y H:i:s', $dataHoraAtual);
    } else {
        $dataHoraAtual = date('d/m/Y H:i:s');
        $dataHoraAtual = DateTime::createFromFormat('d/m/Y H:i:s', $dataHoraAtual);
    }

    $meses = [
        '01' => 'janeiro',
        '02' => 'fevereiro',
        '03' => 'março',
        '04' => 'abril',
        '05' => 'maio',
        '06' => 'junho',
        '07' => 'julho',
        '08' => 'agosto',
        '09' => 'setembro',
        '10' => 'outubro',
        '11' => 'novembro',
        '12' => 'dezembro'
    ];

    if ($retorno_formato == '') {
        $resultado = $dataHoraAtual->format('d/m/Y');
    } else if ($retorno_formato == 'full') {
        $resultado = $dataHoraAtual->format('d/m/y H:i:s');
    } else if ($retorno_formato == 'extenso') {
        $dia = $dataHoraAtual->format('d');
        $mes = $meses[$dataHoraAtual->format('m')];
        $ano = $dataHoraAtual->format('Y');
        $resultado = $dia . ' de ' . $mes . ' de ' . $ano;
    } else if ($retorno_formato == 'full_extenso') {
        $dia = $dataHoraAtual->format('d');
        $mes = $meses[$dataHoraAtual->format('m')];
        $ano = $dataHoraAtual->format('Y');
        $resultado = $dia . ' de ' . $mes . ' de ' . $ano . ' ás ' . $dataHoraAtual->format('H:i:s');
    } else if ($retorno_formato == 'dia') {
        $resultado = $dataHoraAtual->format('d');
    } else if ($retorno_formato == 'mes') {
        $resultado = $dataHoraAtual->format('m');
    } else if ($retorno_formato == 'ano') {
        $resultado = $dataHoraAtual->format('Y');
    } else if ($retorno_formato == 'mes_extenso') {
        $resultado = $meses[$dataHoraAtual->format('m')];
    } else {
        $resultado = $dataHoraAtual->format($retorno_formato);
    }

    if ($retorno_tipo == '' || $retorno_tipo == 'echo') {
        echo $resultado;
    } else if ($retorno_tipo == 'return') {
        return $resultado;
    } else {
        return $resultado;
    }
}

function formatarMoedaFront($valor, $tipo_retorno = 'echo')
{
    $valor_formatado = "R$ " .  number_format($valor, 2, ',', '.');
    if ($tipo_retorno == 'echo') {
        echo $valor_formatado;
    } else {
        return $valor_formatado;
    }
}

function recarregarPagina()
{
    echo "<meta HTTP-EQUIV='refresh' CONTENT='0'>";
}

function redirecionar($link)
{
    echo '<script>window.location.replace("' . $link . '")</script>';
}

function definirTituloPagina($titulo)
{
    echo "<script>document.title='" . $titulo . "'</script>";
}

function validaCPF($cpf)
{
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);
    if (strlen($cpf) != 11) {
        return false;
    }
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

function formatarCPF($cpf)
{
    if (strlen($cpf) != 11) {
        return false;
    }
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
}

function gerarSlug($str)
{
    $str = mb_strtolower($str);
    $str = preg_replace('/(â|á|ã)/', 'a', $str);
    $str = preg_replace('/(ê|é)/', 'e', $str);
    $str = preg_replace('/(í|Í)/', 'i', $str);
    $str = preg_replace('/(ú)/', 'u', $str);
    $str = preg_replace('/(ó|ô|õ|Ô)/', 'o', $str);
    $str = preg_replace('/(_|\/|!|\?|#)/', '', $str);
    $str = preg_replace('/( )/', '-', $str);
    $str = preg_replace('/ç/', 'c', $str);
    $str = preg_replace('/(-[-]{1,})/', '-', $str);
    $str = preg_replace('/(,)/', '-', $str);
    $str = strtolower($str);
    return $str;
}

function reverterSlug($str)
{
    $str = preg_replace('/-/', ' ', $str);
    return $str;
}

function formatarDataInternacional($data)
{
    return date("d/m/Y", strtotime($data));
}
