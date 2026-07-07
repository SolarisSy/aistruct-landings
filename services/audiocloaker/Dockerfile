FROM python:3.11-slim

# ffmpeg é o motor do cloak (cancelamento de fase estéreo)
RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

ENV AUDIOCLOAKER_DATA=/data
ENV AUDIOCLOAKER_MAX_MB=512
VOLUME ["/data"]
EXPOSE 8000

CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8000"]
