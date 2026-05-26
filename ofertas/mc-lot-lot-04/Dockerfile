FROM nginx:alpine
COPY . /usr/share/nginx/html
# Cabeçalho útil pra debug — remove em prod se quiser
RUN sed -i 's|server_tokens on|server_tokens off|' /etc/nginx/nginx.conf || true
