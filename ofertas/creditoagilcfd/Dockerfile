FROM richarvey/nginx-php-fpm:latest

# Copy content to nginx html
COPY . /usr/share/nginx/html

# Fix permissions
RUN chown -R www-data:www-data /usr/share/nginx/html
