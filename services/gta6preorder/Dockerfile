FROM python:3.11-slim
WORKDIR /app
RUN pip install --no-cache-dir fastapi==0.115.0 uvicorn[standard]==0.30.6 httpx==0.27.2 python-multipart==0.0.12
COPY app.py .
COPY index.html .
COPY checkout.html .
COPY obrigado.html .
COPY assets/ ./assets/
CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8000"]
