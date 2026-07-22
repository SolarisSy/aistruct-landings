FROM richarvey/nginx-php-fpm:latest

# Porteiro Adspect puro: só o index.php do stream roda na raiz.
# Qualificado -> 302 artigo abundanciaahora.com | bot/revisor -> 302 raiz.
COPY . /var/www/html

# vhost: Adspect na RAIZ (Taboola aponta pra https://notasaldia.cfd/)
COPY nginx-site.conf /etc/nginx/sites-available/default.conf

RUN chown -R nginx:nginx /var/www/html
RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-site.conf
