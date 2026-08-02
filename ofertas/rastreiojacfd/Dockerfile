FROM php:8.2-fpm

# PHP-FPM + nginx porque /go/index.php (o roteador) precisa de PHP.
# O destino principal e' EXTERNO e configurado no painel do roteador — esta arvore
# NAO tem /consulta, /chat, /rastreio nem qualquer path adjacente (Regra de Ouro do
# SETUP_CRR): tudo que responde 200 aqui e' pagina do proprio site ou o gate /go/.
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

# /var/www/dados: onde /contato/enviar.php grava as mensagens do atendimento.
# FORA do document root (nao e' servivel por URL) e do dono do php-fpm.
# Para sobreviver a redeploy, montar volume do painel neste caminho.
RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx-php.conf /var/www/html/.dockerignore \
    && mkdir -p /var/www/dados \
    && chown -R www-data:www-data /var/www/dados \
    && chmod 700 /var/www/dados \
    && sed -i 's|;opcache.enable=1|opcache.enable=1|' /usr/local/etc/php/php.ini-production \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx"]
