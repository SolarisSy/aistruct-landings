FROM python:3.11-slim

RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg curl unzip \
    && rm -rf /var/lib/apt/lists/*

# deno = runtime JS pro yt-dlp resolver os desafios do YouTube (menos bot-check)
RUN curl -fsSL https://github.com/denoland/deno/releases/latest/download/deno-x86_64-unknown-linux-gnu.zip -o /tmp/deno.zip \
    && unzip -q /tmp/deno.zip -d /usr/local/bin && rm /tmp/deno.zip && chmod +x /usr/local/bin/deno

RUN pip install --no-cache-dir fastapi "uvicorn[standard]" yt-dlp faster-whisper python-multipart

WORKDIR /app
COPY app.py .

ENV DATA_DIR=/data WHISPER_MODEL=small
EXPOSE 80
CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "80"]
