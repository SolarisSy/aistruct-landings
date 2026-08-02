FROM richarvey/nginx-php-fpm:latest

# Copy all content to the correct location
COPY . /var/www/html

# Sobrescreve o vhost default: a RAIZ executa index.php (o roteador do metodo
# direct_url), /go/ e o mesmo integrador como caminho alternativo, estaticos e
# paginas seguem por path direto, + fix Bunny CF_CONNECTING_IP (sem ele o
# roteador ve o IP do no Bunny, ASN 60068, e manda 100% para a pagina de loja).
# (richarvey/nginx-php-fpm carrega /etc/nginx/sites-enabled/default.conf)
COPY nginx-site.conf /etc/nginx/sites-available/default.conf

# Fix permissions (imagem usa user nginx, não www-data — mas ambos funcionam)
RUN chown -R nginx:nginx /var/www/html

# Limpa artefatos de build do docroot.
# _MANIFEST.md descreve como o site foi montado — não pode ficar público.
# ATENCAO: index.php NAO entra nesta lista. A imagem base traz um index.php de
# phpinfo em /var/www/html, mas o `COPY . /var/www/html` acima ja o substitui
# pelo NOSSO roteador — apagar aqui deixaria a raiz sem roteador (a URL
# anunciada devolveria 404 e nenhum clique chegaria ao checkout).
RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-site.conf /var/www/html/_MANIFEST.md

# A pagina de compra sai do docroot: passa a viver em /var/private, onde o nginx
# nao alcanca por caminho nenhum. Quem entrega o HTML e api/pagina.php, e so
# depois de conferir a marca emitida pelo /ir/ — nem por erro de configuracao o
# servidor entrega esse arquivo direto.
RUN mkdir -p /var/private  && mv /var/www/html/_priv/checkout.html /var/private/checkout.html  && rmdir /var/www/html/_priv  && chown -R nginx:nginx /var/private
