FROM richarvey/nginx-php-fpm:latest

# Site estático multipágina (index + pre/ + vsl/ + back/)
COPY . /var/www/html

# vhost estático servindo /var/www/html (index.html)
COPY nginx-site.conf /etc/nginx/sites-available/default.conf

RUN chown -R nginx:nginx /var/www/html \
 && rm -f /var/www/html/Dockerfile /var/www/html/nginx-site.conf
