FROM php:8.2-fpm

# Necessario PHP-FPM + nginx porque /go/index.php (Adspect cloaker) precisa de PHP.
# Money page agora é HTML estatico (sem PHP), mas o cloaker /go/ continua PHP.
# Debian (nao alpine): curl/json/session/openssl ja vem embutidos.
RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx libonig-dev \
    && docker-php-ext-install -j$(nproc) opcache mbstring \
    && rm -rf /var/lib/apt/lists/*

# nginx no Debian usa /etc/nginx/nginx.conf + conf em sites-enabled/;
# o php:8.2-fpm nao traz nginx.conf default — criamos um minimo
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
