FROM php:8.2-fpm-alpine

# nginx + python3 + gcc (necessário pra uvloop/httptools do uvicorn[standard])
RUN apk add --no-cache nginx python3 py3-pip gcc musl-dev libffi-dev \
    && install -d /run/nginx

# deps Python (FastAPI checkout) — sem [standard] pra evitar compilação pesada
RUN pip3 install --no-cache-dir --break-system-packages \
    fastapi==0.115.0 "uvicorn[standard]==0.30.6" \
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
COPY ofertas.html .
COPY assets/ ./assets/
COPY go/ ./go/

RUN rm -f Dockerfile nginx.conf

EXPOSE 80

# Inicia PHP-FPM + uvicorn (checkout) + nginx
# php-fpm81 = alpine 3.18, php-fpm = fallback (igual sorteja.cfd)
CMD sh -c "php-fpm81 -D 2>/dev/null || php-fpm -D; \
    cd /app && python3 -m uvicorn app:app --host 127.0.0.1 --port 8000 & \
    sleep 2 && nginx -g 'daemon off;'"
