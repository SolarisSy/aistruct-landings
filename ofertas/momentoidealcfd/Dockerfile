FROM nginx:alpine
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY . /usr/share/nginx/html/
RUN rm -f /usr/share/nginx/html/Dockerfile /usr/share/nginx/html/nginx.conf \
    && sed -i 's|server_tokens on|server_tokens off|' /etc/nginx/nginx.conf 2>/dev/null || true
EXPOSE 80
