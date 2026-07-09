FROM richarvey/nginx-php-fpm:latest

# Expose port 80 explicitly
EXPOSE 80

# Copy all content to the correct location
COPY . /var/www/html

# Fix permissions
RUN chown -R www-data:www-data /var/www/html
