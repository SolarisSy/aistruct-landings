FROM nginx:alpine
COPY . /usr/share/nginx/html
RUN rm -f /usr/share/nginx/html/Dockerfile /usr/share/nginx/html/.dockerignore
RUN sed -i 's|server_tokens on|server_tokens off|' /etc/nginx/nginx.conf || true
# gzip + cache: /_astro tem hash no nome (immutable 1 ano); html sempre revalida
RUN printf 'server {\n\
  listen 80;\n\
  server_name _;\n\
  root /usr/share/nginx/html;\n\
  index index.html;\n\
  gzip on;\n\
  gzip_comp_level 6;\n\
  gzip_min_length 512;\n\
  gzip_vary on;\n\
  gzip_types text/css application/javascript text/javascript application/json image/svg+xml text/plain;\n\
  location /_astro/ {\n\
    add_header Cache-Control "public, max-age=31536000, immutable";\n\
    try_files $uri =404;\n\
  }\n\
  location /img/ {\n\
    add_header Cache-Control "public, max-age=2592000";\n\
    try_files $uri =404;\n\
  }\n\
  location / {\n\
    add_header Cache-Control "no-cache";\n\
    try_files $uri $uri/ =404;\n\
  }\n\
}\n' > /etc/nginx/conf.d/default.conf
