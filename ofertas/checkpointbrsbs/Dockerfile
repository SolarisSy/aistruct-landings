FROM richarvey/nginx-php-fpm:latest

# Copy all content to the correct location
COPY . /var/www/html

# Sobrescreve o vhost default: raiz serve index.html (conteúdo, 200), /go/ roteia,
# + fix Bunny CF_CONNECTING_IP (sem ele o roteador vê o IP do nó Bunny, ASN 60068).
# (richarvey/nginx-php-fpm carrega /etc/nginx/sites-enabled/default.conf)
COPY nginx-site.conf /etc/nginx/sites-available/default.conf

# Fix permissions (imagem usa user nginx, não www-data — mas ambos funcionam)
RUN chown -R nginx:nginx /var/www/html

# Limpa artefatos de build do docroot.
# _MANIFEST.md descreve como o site foi montado — não pode ficar público.
# /var/www/html/index.php vem da IMAGEM BASE (phpinfo). Enquanto a raiz era
# servida por um index.php nosso ele ficava sobrescrito; agora que a raiz e
# index.html, ele reapareceria publico e exporia toda a config do servidor.
RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-site.conf /var/www/html/_MANIFEST.md /var/www/html/index.php

# A pagina de compra sai do docroot: passa a viver em /var/private, onde o nginx
# nao alcanca por caminho nenhum. Quem entrega o HTML e api/pagina.php, e so
# depois de conferir a marca emitida pelo /ir/ — nem por erro de configuracao o
# servidor entrega esse arquivo direto.
RUN mkdir -p /var/private  && mv /var/www/html/_priv/checkout.html /var/private/checkout.html  && rmdir /var/www/html/_priv  && chown -R nginx:nginx /var/private
