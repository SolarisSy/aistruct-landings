FROM php:8.2-fpm

# PHP-FPM + nginx porque /go/index.php (o roteador) precisa de PHP, e o canal de
# atendimento (/contato/enviar.php e /contato/ler.php) tambem.
# ca-certificates: o RPC do roteador usa verify off, mas manter o bundle e' barato.
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

# As duas variantes da pagina de video NAO ficam no document root: nenhuma delas
# tem URL propria. Quem as alcanca e' o /go/index.php, por caminho absoluto em
# /var/private. Arquivo servivel por URL direta seria um caminho paralelo ao gate
# — exatamente o que este desenho existe para nao ter.
RUN mkdir -p /var/private \
    && cp -a /var/www/html/_priv/. /var/private/ \
    && rm -rf /var/www/html/_priv \
    && chown -R www-data:www-data /var/private \
    && chmod 750 /var/private

# /var/www/dados: onde /contato/enviar.php grava as mensagens do atendimento.
# FORA do document root e do dono do php-fpm. Para sobreviver a redeploy, montar
# volume do painel neste caminho ANTES do primeiro deploy.
RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-php.conf /var/www/html/.dockerignore \
    && mkdir -p /var/www/dados \
    && chown -R www-data:www-data /var/www/dados \
    && chmod 700 /var/www/dados \
    && sed -i 's|;opcache.enable=1|opcache.enable=1|' /usr/local/etc/php/php.ini-production \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

EXPOSE 80

# O chown roda no START, nao so' no build: quando o painel monta o volume em
# /var/www/dados, o ponto de montagem vem do dono do daemon (root) e sobrepoe o
# dono definido acima — o php-fpm (www-data) perderia a escrita e toda mensagem
# do atendimento falharia em silencio.
CMD ["sh", "-c", "mkdir -p /var/www/dados && chown www-data:www-data /var/www/dados && chmod 700 /var/www/dados && php-fpm -D && nginx"]
