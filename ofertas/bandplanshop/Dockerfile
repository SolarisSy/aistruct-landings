FROM php:8.2-fpm-alpine
RUN apk add --no-cache nginx && mkdir -p /run/nginx
COPY nginx-site.conf /etc/nginx/http.d/default.conf
COPY . /var/www/html
WORKDIR /var/www/html
EXPOSE 80
CMD php-fpm -D && nginx -g 'daemon off;'
