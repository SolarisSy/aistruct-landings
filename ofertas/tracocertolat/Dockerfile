FROM richarvey/nginx-php-fpm:latest
COPY . /var/www/html
COPY nginx-site.conf /etc/nginx/sites-available/default.conf
ENV WEBROOT=/var/www/html
EXPOSE 80
