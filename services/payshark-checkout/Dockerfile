FROM python:3.12-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app.py .
COPY checkout.html .
COPY upsell1.html .
COPY upsell2.html .
COPY obrigado.html .
COPY banner.png .
COPY icone.png .

EXPOSE 8000

CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8000"]
