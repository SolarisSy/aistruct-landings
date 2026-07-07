"""
AudioCloaker SaaS — API FastAPI.

Upload de vídeo -> processamento (cloak / detect / recover) em background ->
download do resultado. Fila de jobs em memória com polling, para suportar
arquivos longos sem estourar o timeout do request HTTP.
"""

from __future__ import annotations

import os
import re
import shutil
import threading
import uuid
from concurrent.futures import ThreadPoolExecutor
from dataclasses import asdict, dataclass, field
from pathlib import Path
from typing import Optional

from fastapi import FastAPI, File, Form, HTTPException, UploadFile
from fastapi.responses import FileResponse, JSONResponse
from fastapi.staticfiles import StaticFiles

import cloak as engine

# --------------------------------------------------------------------------- #
# Configuração
# --------------------------------------------------------------------------- #

BASE_DIR = Path(__file__).parent
DATA_DIR = Path(os.environ.get("AUDIOCLOAKER_DATA", BASE_DIR / "data"))
UPLOAD_DIR = DATA_DIR / "uploads"
OUTPUT_DIR = DATA_DIR / "outputs"
for d in (UPLOAD_DIR, OUTPUT_DIR):
    d.mkdir(parents=True, exist_ok=True)

MAX_UPLOAD_BYTES = int(os.environ.get("AUDIOCLOAKER_MAX_MB", "512")) * 1024 * 1024
ALLOWED_EXT = {".mp4", ".mov", ".mkv", ".webm", ".avi", ".m4v",
               ".mp3", ".wav", ".m4a", ".aac", ".ogg", ".flac"}

_pool = ThreadPoolExecutor(max_workers=int(os.environ.get("AUDIOCLOAKER_WORKERS", "2")))
_jobs: dict[str, "Job"] = {}
_lock = threading.Lock()


@dataclass
class Job:
    id: str
    mode: str            # cloak | detect | recover
    status: str = "queued"   # queued | running | done | error
    progress: float = 0.0
    message: str = ""
    orig_name: str = ""
    input_path: str = ""
    output_path: str = ""
    output_name: str = ""
    result: dict = field(default_factory=dict)
    error: str = ""

    def public(self) -> dict:
        d = asdict(self)
        d.pop("input_path", None)
        d.pop("output_path", None)
        d["download_url"] = f"/api/download/{self.id}" if self.status == "done" and self.output_path else None
        return d


app = FastAPI(title="AudioCloaker", version="1.0")


# --------------------------------------------------------------------------- #
# Processamento em background
# --------------------------------------------------------------------------- #

def _set(job: Job, **kw):
    with _lock:
        for k, v in kw.items():
            setattr(job, k, v)


def _safe_stem(name: str) -> str:
    """Nome-base do arquivo original, sem caminho/extensão e sem caracteres inválidos."""
    base = os.path.basename(name or "")
    stem = os.path.splitext(base)[0]
    stem = re.sub(r'[\\/:*?"<>|\r\n\t]+', "_", stem).strip()
    return stem or "audio"


_VIDEO_KEEP = (".mp4", ".mov", ".mkv", ".m4v", ".webm")
_AUDIO_KEEP = (".mp3", ".wav", ".m4a", ".aac", ".ogg", ".opus", ".flac")


def _process(job: Job, hiss: float, method: str):
    _set(job, status="running", progress=0.0, message="Analisando o arquivo…")
    try:
        cb = lambda p: _set(job, progress=round(p, 3))
        stem = _safe_stem(job.orig_name)
        in_ext = os.path.splitext(job.orig_name)[1].lower()

        if job.mode == "cloak":
            probe0 = engine.probe(job.input_path)
            if probe0.has_video:
                out_ext = in_ext if in_ext in _VIDEO_KEEP else ".mp4"
            else:
                out_ext = in_ext if in_ext in _AUDIO_KEEP else ".m4a"
            out = OUTPUT_DIR / f"{job.id}{out_ext}"
            info = engine.cloak(job.input_path, str(out), hiss=hiss, on_progress=cb)
            _set(job, output_path=str(out), output_name=f"{stem}{out_ext}",
                 result={"duration": round(info.duration, 2), "had_video": info.has_video},
                 message="Narração protegida.")

        elif job.mode == "detect":
            res = engine.detect(job.input_path)
            _set(job, result=asdict(res), message=res.verdict)

        elif job.mode == "recover":
            out = OUTPUT_DIR / f"{job.id}.mp3"
            engine.recover(job.input_path, str(out), method=method, on_progress=cb)
            _set(job, output_path=str(out), output_name=f"{stem}.mp3",
                 result={"method": method}, message="Voz recuperada.")
        else:
            raise engine.CloakError(f"Modo desconhecido: {job.mode}")

        _set(job, status="done", progress=1.0)
    except engine.CloakError as e:
        _set(job, status="error", error=str(e), message=str(e))
    except Exception as e:  # noqa: BLE001 — superfície defensiva
        _set(job, status="error", error=f"Erro inesperado: {e}", message="Falha no processamento.")
    finally:
        # limpa o upload; mantém só o resultado
        try:
            if job.input_path and os.path.exists(job.input_path):
                os.remove(job.input_path)
        except OSError:
            pass


# --------------------------------------------------------------------------- #
# Endpoints
# --------------------------------------------------------------------------- #

@app.post("/api/jobs")
async def create_job(
    file: UploadFile = File(...),
    mode: str = Form("cloak"),
    hiss: float = Form(0.0),
    method: str = Form("L"),
):
    if mode not in ("cloak", "detect", "recover"):
        raise HTTPException(400, "mode inválido (cloak|detect|recover)")

    ext = Path(file.filename or "").suffix.lower()
    if ext not in ALLOWED_EXT:
        raise HTTPException(400, f"Extensão não suportada: {ext or '(vazia)'}")

    job_id = uuid.uuid4().hex
    dest = UPLOAD_DIR / f"{job_id}{ext}"

    size = 0
    with open(dest, "wb") as f:
        while chunk := await file.read(1024 * 1024):
            size += len(chunk)
            if size > MAX_UPLOAD_BYTES:
                f.close()
                dest.unlink(missing_ok=True)
                raise HTTPException(413, f"Arquivo maior que o limite ({MAX_UPLOAD_BYTES // (1024*1024)} MB).")
            f.write(chunk)

    job = Job(id=job_id, mode=mode, input_path=str(dest), orig_name=(file.filename or ""))
    with _lock:
        _jobs[job_id] = job
    _pool.submit(_process, job, hiss, method)
    return JSONResponse(job.public())


@app.get("/api/jobs/{job_id}")
async def get_job(job_id: str):
    job = _jobs.get(job_id)
    if not job:
        raise HTTPException(404, "job não encontrado")
    return job.public()


@app.get("/api/download/{job_id}")
async def download(job_id: str):
    job = _jobs.get(job_id)
    if not job or job.status != "done" or not job.output_path:
        raise HTTPException(404, "resultado indisponível")
    if not os.path.exists(job.output_path):
        raise HTTPException(410, "arquivo expirou")
    return FileResponse(job.output_path, filename=job.output_name or "resultado")


@app.get("/api/health")
async def health():
    return {"ok": True, "ffmpeg": engine.FFMPEG, "jobs": len(_jobs)}


# Frontend estático (montado por último para não capturar /api).
app.mount("/", StaticFiles(directory=str(BASE_DIR / "static"), html=True), name="static")
