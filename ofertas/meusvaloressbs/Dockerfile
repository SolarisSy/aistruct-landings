FROM nginx:alpine

COPY . /usr/share/nginx/html
COPY nginx-site.conf /etc/nginx/conf.d/default.conf

# Limpa artefatos de build do docroot
RUN rm -f /usr/share/nginx/html/Dockerfile /usr/share/nginx/html/nginx-site.conf
