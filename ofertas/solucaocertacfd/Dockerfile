FROM richarvey/nginx-php-fpm:latest
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html \
 && cp /var/www/html/404.html /var/www/errors/404.html
