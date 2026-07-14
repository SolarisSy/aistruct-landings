FROM richarvey/nginx-php-fpm:latest
COPY . /var/www/html

# Sobrescreve o vhost default com config direct_url + fix Bunny CF_CONNECTING_IP.
# Sem isso o Adspect vê o IP do nó Bunny (ASN 60068) e manda 100% pro safe.
COPY nginx-site.conf /etc/nginx/sites-available/default.conf

RUN chown -R www-data:www-data /var/www/html \
 && cp /var/www/html/404.html /var/www/errors/404.html \
 && rm -f /var/www/html/Dockerfile /var/www/html/nginx-site.conf
