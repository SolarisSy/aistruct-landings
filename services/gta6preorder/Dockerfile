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

# ── Aplicacao Python: FORA do docroot ────────────────────────────────
# Antes o app morava no mesmo diretorio que o nginx serve, e o `location /`
# (try_files) entregava o proprio fonte por HTTP. Agora o app vive em /srv/app,
# que o nginx nao conhece: nao ha como pedir esses arquivos pela web.
# Os HTML renderizados pelo app ficam ao lado dele porque ele os resolve por
# Path(__file__).parent na importacao.
WORKDIR /srv/app
COPY app.py .
COPY index.html .
COPY checkout.html .
COPY obrigado.html .
COPY blog.html .

# ── Docroot publico: SO o que precisa ser lido por HTTP ──────────────
# index.html (pagina comercial, servida em / e /ofertas) + assets estaticos.
# Nada de fonte, config, dependencia ou metadado entra aqui.
RUN install -d /var/www/html
COPY index.html /var/www/html/index.html
COPY assets/ /var/www/html/assets/

EXPOSE 8000

# uvicorn na 8001 (interno, so localhost) + nginx na 8000 (Traefik)
CMD sh -c "cd /srv/app && python3 -m uvicorn app:app --host 127.0.0.1 --port 8001 & \
    sleep 2 && nginx -g 'daemon off;'"
