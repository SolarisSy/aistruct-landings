FROM php:8.2-fpm-alpine

# nginx + extensoes PHP obrigatorias para a money do rastreiofacil.cfd:
#   curl     -> cpf.php (API hubdodesenvolvedor) + rastreio.php (API payoutbr PIX)
#   mbstring -> functions.php (mb_strtolower)
#   opcache  -> performance
# (json/session/openssl ja vem embutidos no php:8.2-fpm-alpine)
# Deps Alpine necessarias pra compilar as exts:
#   libcurl-dev  -> ext curl (nao basta docker-php-ext-install, precisa da lib)
#   oniguruma-dev -> ext mbstring
RUN apk add --no-cache nginx libcurl-dev oniguruma-dev \
    && docker-php-ext-install -j$(nproc) opcache curl mbstring

COPY nginx-php.conf /etc/nginx/http.d/default.conf
COPY . /var/www/html

RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-php.conf /var/www/html/.dockerignore \
    && sed -i 's|;opcache.enable=1|opcache.enable=1|' /usr/local/etc/php/php.ini-production \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && mkdir -p /var/run/nginx /var/log/nginx

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
