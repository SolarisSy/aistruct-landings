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
RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-site.conf /var/www/html/_MANIFEST.md
