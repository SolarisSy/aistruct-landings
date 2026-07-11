FROM richarvey/nginx-php-fpm:latest
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html \
 && cp /var/www/html/404.html /usr/share/nginx/html/404.html 2>/dev/null || true
