# Base oficial do Playwright: já traz Node 20 + Chromium + todas as libs de
# sistema (a captura de site precisa de um browser de verdade).
FROM mcr.microsoft.com/playwright:v1.48.0-jammy

WORKDIR /app

COPY package.json ./
RUN npm install --no-audit --no-fund

COPY server.js capture.js browser-session.js ./

# /data = workspaces + cache npm + builds publicados (volume persistente)
ENV DATA_DIR=/data \
    PORT=8000

EXPOSE 8000
CMD ["node", "server.js"]
