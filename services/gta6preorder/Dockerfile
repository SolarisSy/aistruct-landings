FROM php:8.2-fpm-alpine

# nginx + python3
RUN apk add --no-cache nginx python3 py3-pip \
    && install -d /run/nginx

# deps Python (FastAPI checkout)
RUN pip install --no-cache-dir --break-system-packages \
    fastapi==0.115.0 uvicorn[standard]==0.30.6 \
    httpx==0.27.2 python-multipart==0.0.12

# nginx config
COPY nginx.conf /etc/nginx/http.d/default.conf
RUN sed -i 's|server_tokens on|server_tokens off|' /etc/nginx/nginx.conf 2>/dev/null || true

# App files
WORKDIR /var/www/html
COPY app.py /app/app.py
COPY index.html .
COPY checkout.html .
COPY obrigado.html .
COPY blog.html .
COPY safe.html .
COPY assets/ ./assets/
COPY go/ ./go/

RUN rm -f Dockerfile nginx.conf

EXPOSE 80

# Inicia PHP-FPM + uvicorn (checkout) + nginx
CMD sh -c "php-fpm -D; \
    cd /app && uvicorn app:app --host 127.0.0.1 --port 8000 & \
    nginx -g 'daemon off;'"
