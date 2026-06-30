FROM php:8.2-fpm-alpine

# nginx + python3
RUN apk add --no-cache nginx python3 py3-pip \
    && install -d /run/nginx

# deps Python (FastAPI checkout)
RUN pip3 install --no-cache-dir --break-system-packages \
    fastapi==0.115.0 uvicorn==0.30.6 \
    httpx==0.27.2 python-multipart==0.0.12

# nginx config
COPY nginx.conf /etc/nginx/http.d/default.conf
RUN sed -i 's|server_tokens on|server_tokens off|' /etc/nginx/nginx.conf 2>/dev/null || true

# Todos os arquivos em /var/www/html (app.py lê __file__.parent para assets/html)
WORKDIR /var/www/html
COPY app.py .
COPY index.html .
COPY checkout.html .
COPY obrigado.html .
COPY blog.html .
COPY ofertas.html .
COPY assets/ ./assets/
COPY go/ ./go/

RUN rm -f Dockerfile nginx.conf

EXPOSE 8000

# PHP-FPM + uvicorn na 8001 (interno) + nginx na 8000 (Traefik)
CMD sh -c "php-fpm81 -D 2>/dev/null || php-fpm -D; \
    cd /var/www/html && python3 -m uvicorn app:app --host 127.0.0.1 --port 8001 & \
    sleep 2 && nginx -g 'daemon off;'"
