FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx curl-dev libcurl sqlite-dev \
    && docker-php-ext-install -j$(nproc) curl opcache pdo pdo_sqlite

COPY nginx-php.conf /etc/nginx/http.d/default.conf
COPY . /var/www/html

RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-php.conf /var/www/html/.dockerignore \
    && sed -i 's|;opcache.enable=1|opcache.enable=1|' /usr/local/etc/php/php.ini-production \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && mkdir -p /var/run/nginx /var/log/nginx \
    && mkdir -p /var/data && chmod 777 /var/data \
    && mkdir -p /tmp/sessions && chmod 777 /tmp/sessions

VOLUME ["/var/data"]

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
