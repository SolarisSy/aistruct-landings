FROM nginx:alpine

# v2 vTurb-filter: site estatico puro. O default do nginx:alpine serve
# /usr/share/nginx/html com index.html — nao precisa de vhost custom.
COPY . /usr/share/nginx/html

# Limpa artefatos de build do docroot
RUN rm -f /usr/share/nginx/html/Dockerfile /usr/share/nginx/html/nginx-site.conf
