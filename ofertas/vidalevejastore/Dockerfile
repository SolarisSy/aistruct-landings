FROM php:8.2-fpm-alpine
RUN apk add --no-cache nginx && install -d /run/nginx
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY . /var/www/html/
RUN rm -f /var/www/html/Dockerfile /var/www/html/nginx.conf
EXPOSE 80
CMD sh -c "php-fpm82 -D 2>/dev/null || php-fpm -D; nginx -g 'daemon off;'"
