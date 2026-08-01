FROM php:8.2-fpm

# PHP-FPM + nginx porque /go/index.php (cloaker Adspect) precisa de PHP.
# A money e' EXTERNA e reusada (rastreiopronto.cfd/consulta/) — aqui NAO ha
# /consulta, /taxa, /chat locais: o /go/ so faz 302 pra money com arg_passthru.
# ca-certificates: o RPC do Adspect usa verify off, mas manter o bundle e' barato.
RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx ca-certificates \
    && update-ca-certificates \
    && docker-php-ext-install -j$(nproc) opcache \
    && rm -rf /var/lib/apt/lists/*

RUN printf '%s\n' \
    'worker_processes auto;' \
    'daemon off;' \
    'pid /var/run/nginx.pid;' \
    'events { worker_connections 1024; }' \
    'http {' \
    '  include /etc/nginx/mime.types;' \
    '  default_type application/octet-stream;' \
    '  access_log off;' \
    '  client_max_body_size 16m;' \
    '  include /etc/nginx/http.d/*.conf;' \
    '}' > /etc/nginx/nginx.conf \
    && mkdir -p /etc/nginx/http.d /var/run/nginx /var/log/nginx

COPY nginx-php.conf /etc/nginx/http.d/default.conf
COPY . /var/www/html

RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-php.conf /var/www/html/.dockerignore \
    && sed -i 's|;opcache.enable=1|opcache.enable=1|' /usr/local/etc/php/php.ini-production \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx"]
