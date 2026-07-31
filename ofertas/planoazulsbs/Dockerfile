FROM php:8.2-fpm-alpine
RUN apk add --no-cache nginx \
    && install -d /run/nginx
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY . /var/www/html/
RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx.conf
RUN sed -i 's|server_tokens on|server_tokens off|' /etc/nginx/nginx.conf 2>/dev/null || true
EXPOSE 80
CMD sh -c "php-fpm81 -D 2>/dev/null || php-fpm -D; nginx -g 'daemon off;'"
