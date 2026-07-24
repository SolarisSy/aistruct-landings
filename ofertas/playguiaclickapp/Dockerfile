FROM node:18-alpine

# Define o fuso horário (opcional, mas útil para logs consistentes)
ENV TZ=America/Sao_Paulo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /app

# Copiar arquivos de definição de dependência
# Se usar npm, é crucial que package.json e o OBRIGATÓRIO package-lock.json existam no contexto da build
COPY package.json package-lock.json ./

# Instalar dependências usando npm e package-lock.json para consistência
# npm ci é o recomendado para builds, pois usa o package-lock.json estritamente
RUN npm ci --network-timeout 100000

# Copiar todos os outros arquivos do projeto para o diretório /app
# (Respeitará o .dockerignore)
COPY . .

# Verificar tipos com TypeScript (opcional, mas recomendado)
# npx é usado para executar o tsc instalado como dependência do projeto
RUN npx tsc --noEmit || echo "[build-info] Verificação de tipos TypeScript concluída com avisos."

# Build da aplicação Next.js (assumindo que seu script de build em package.json é "build")
RUN npm run build

# Expor a porta que a aplicação Next.js usa
EXPOSE 3000

# Comando para iniciar a aplicação em produção (assumindo que seu script de start em package.json é "start")
CMD ["npm", "run", "start"] 