FROM richarvey/nginx-php-fpm:latest

# Copy all content to the correct location
COPY . /var/www/html

# Sobrescreve o vhost default com config direct_url + fix Bunny CF_CONNECTING_IP.
# Sem isso o Adspect vê o IP do nó Bunny (ASN 60068) e manda 100% pro safe.
# (richarvey/nginx-php-fpm carrega /etc/nginx/sites-enabled/default.conf)
COPY nginx-site.conf /etc/nginx/sites-available/default.conf

# Fix permissions (imagem usa user nginx, não www-data — mas ambos funcionam)
RUN chown -R nginx:nginx /var/www/html

# Limpa artefatos de build do docroot
RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-site.conf
