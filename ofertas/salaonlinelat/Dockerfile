FROM nginx:alpine
COPY . /usr/share/nginx/html
RUN rm -f /usr/share/nginx/html/Dockerfile /usr/share/nginx/html/.dockerignore
RUN cp -r /usr/share/nginx/html/white/home /usr/share/nginx/html/home 2>/dev/null || true
RUN sed -i 's|server_tokens on|server_tokens off|' /etc/nginx/nginx.conf || true
