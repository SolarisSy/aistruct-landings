FROM node:20-alpine

WORKDIR /app

COPY package.json ./
RUN npm install --no-audit --no-fund

COPY server.js ./

# /data = workspaces + cache npm + builds publicados (volume persistente)
ENV DATA_DIR=/data \
    PORT=8000

EXPOSE 8000
CMD ["node", "server.js"]
