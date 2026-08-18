# -*- coding: utf-8 -*-
"""Transcriber — plataforma de transcrição de vídeo de qualquer tamanho.

Fluxo por job:
  1. URL (YouTube/qualquer site suportado pelo yt-dlp): tenta legenda pronta
     (manual ou automática) — resolve em segundos mesmo pra vídeo de horas.
  2. Sem legenda (ou modo=whisper, ou upload de arquivo): baixa/recebe o áudio
     e transcreve com faster-whisper (CPU, int8), com progresso ao vivo.

Env:
  ACCESS_TOKEN   — se setado, exige ?token= / X-Token em toda rota de API
  YTDLP_PROXY    — proxy residencial pro yt-dlp (YouTube barra IP de datacenter)
  WHISPER_MODEL  — modelo faster-whisper (default: small)
"""
import json
import os
import re
import shutil
import threading
import time
import uuid
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

from fastapi import FastAPI, HTTPException, Request, UploadFile
from fastapi.responses import FileResponse, HTMLResponse, JSONResponse

DATA = Path(os.environ.get("DATA_DIR", "/data"))
DATA.mkdir(parents=True, exist_ok=True)
TOKEN = os.environ.get("ACCESS_TOKEN", "")
PROXY = os.environ.get("YTDLP_PROXY", "")
MODEL_NAME = os.environ.get("WHISPER_MODEL", "small")

app = FastAPI(title="Transcriber")
JOBS: dict[str, dict] = {}
LOCK = threading.Lock()
POOL = ThreadPoolExecutor(max_workers=2)
WHISPER_SEM = threading.Semaphore(1)  # 1 transcrição pesada por vez (CPU compartilhada)
_model = None
_model_lock = threading.Lock()


def _check(request: Request):
    if not TOKEN:
        return
    tok = request.query_params.get("token") or request.headers.get("x-token") or ""
    if tok != TOKEN:
        raise HTTPException(401, "token inválido")


def _get_model():
    global _model
    with _model_lock:
        if _model is None:
            from faster_whisper import WhisperModel

            _model = WhisperModel(MODEL_NAME, device="cpu", compute_type="int8", cpu_threads=2)
        return _model


def _vtt_to_txt(vtt: str) -> str:
    lines = []
    for raw in vtt.splitlines():
        line = raw.strip()
        if not line or line.startswith(("WEBVTT", "Kind:", "Language:", "NOTE")) or "-->" in line:
            continue
        line = re.sub(r"<[^>]+>", "", line).strip()
        if line and (not lines or lines[-1] != line):
            lines.append(line)
    dedup = []
    for line in lines:
        if dedup and (line == dedup[-1] or dedup[-1].endswith(line)):
            continue
        dedup.append(line)
    return "\n".join(dedup)


def _fmt_ts(sec: float) -> str:
    h, r = divmod(int(sec), 3600)
    m, s = divmod(r, 60)
    ms = int((sec - int(sec)) * 1000)
    return f"{h:02d}:{m:02d}:{s:02d},{ms:03d}"


def _ydl_opts(extra=None):
    opts = {
        "quiet": True,
        "no_warnings": True,
        "noprogress": True,
        "socket_timeout": 60,
        "retries": 3,
        # URL com &list=RD… (mix/rádio) travava o extract_info expandindo playlist infinita
        "noplaylist": True,
        "playlist_items": "1",
    }
    if PROXY:
        opts["proxy"] = PROXY
    if extra:
        opts.update(extra)
    return opts


def _ydl_retry(fn, tentativas=4):
    """Proxy residencial rota IP por conexão: bot-check do YouTube é por-IP,
    então repetir com conexão nova costuma passar."""
    last = None
    for i in range(tentativas):
        try:
            return fn()
        except Exception as e:
            last = e
            msg = str(e)
            if "Sign in to confirm" not in msg and "not a bot" not in msg and "403" not in msg:
                raise
            time.sleep(2 + i * 3)
    raise last


def _set(job_id, **kw):
    with LOCK:
        JOBS[job_id].update(kw)
        (DATA / job_id / "job.json").write_text(
            json.dumps({k: v for k, v in JOBS[job_id].items() if k != "_src"}, ensure_ascii=False),
            encoding="utf-8",
        )


def _transcrever_whisper(job_id: str, media: Path, dur_hint: float):
    _set(job_id, etapa=f"transcrevendo com whisper ({MODEL_NAME})…", progress=0.05)
    with WHISPER_SEM:
        model = _get_model()
        segments, info = model.transcribe(str(media), vad_filter=True)
        dur = info.duration or dur_hint or 0
        txt_lines, srt_lines = [], []
        for i, seg in enumerate(segments, 1):
            txt_lines.append(seg.text.strip())
            srt_lines.append(f"{i}\n{_fmt_ts(seg.start)} --> {_fmt_ts(seg.end)}\n{seg.text.strip()}\n")
            if dur:
                _set(job_id, progress=min(0.99, 0.05 + 0.94 * seg.end / dur))
    out = DATA / job_id
    (out / "transcricao.txt").write_text("\n".join(txt_lines), encoding="utf-8")
    (out / "transcricao.srt").write_text("\n".join(srt_lines), encoding="utf-8")
    return {"idioma": info.language, "duracao": round(dur), "fonte": f"whisper-{MODEL_NAME}"}


def _run_job(job_id: str):
    job = JOBS[job_id]
    out = DATA / job_id
    out.mkdir(exist_ok=True)
    try:
        if job.get("upload"):
            media = Path(job["_src"])
            meta = _transcrever_whisper(job_id, media, 0)
            media.unlink(missing_ok=True)
            _set(job_id, status="done", progress=1.0, etapa="concluído", **meta)
            return

        import yt_dlp

        url = job["url"]
        _set(job_id, etapa="lendo metadados…", progress=0.02)

        def _meta():
            with yt_dlp.YoutubeDL(_ydl_opts()) as ydl:
                return ydl.extract_info(url, download=False)

        info = _ydl_retry(_meta)
        titulo = info.get("title") or url
        dur = info.get("duration") or 0
        _set(job_id, titulo=titulo, duracao=dur)

        if job.get("modo") != "whisper":
            _set(job_id, etapa="procurando legenda pronta…", progress=0.1)
            subdir = out / "subs"
            subdir.mkdir(exist_ok=True)
            def _subs():
                with yt_dlp.YoutubeDL(
                    _ydl_opts(
                        {
                            "skip_download": True,
                            "writesubtitles": True,
                            "writeautomaticsub": True,
                            "subtitleslangs": ["pt-orig", "pt", "pt-BR", "en-orig", "en", "es"],
                            "subtitlesformat": "vtt",
                            "outtmpl": str(subdir / "s.%(ext)s"),
                        }
                    )
                ) as ydl:
                    ydl.download([url])

            try:
                _ydl_retry(_subs)
            except Exception:
                pass
            vtts = sorted(subdir.glob("*.vtt"))
            if vtts:
                # preferência: idioma original > pt > primeiro que veio
                pref = [v for v in vtts if "orig" in v.name] or [v for v in vtts if ".pt" in v.name] or vtts
                vtt = pref[0]
                txt = _vtt_to_txt(vtt.read_text(encoding="utf-8", errors="replace"))
                if len(txt) > 40:  # legenda de verdade, não stub vazio
                    (out / "transcricao.txt").write_text(txt, encoding="utf-8")
                    shutil.copy(vtt, out / "transcricao.vtt")
                    shutil.rmtree(subdir, ignore_errors=True)
                    _set(
                        job_id,
                        status="done",
                        progress=1.0,
                        etapa="concluído",
                        fonte=f"legenda ({vtt.suffixes[0].lstrip('.')})",
                    )
                    return
            shutil.rmtree(subdir, ignore_errors=True)

        _set(job_id, etapa="sem legenda — baixando áudio…", progress=0.15)

        def _audio():
            with yt_dlp.YoutubeDL(
                _ydl_opts({"format": "bestaudio/best", "outtmpl": str(out / "audio.%(ext)s")})
            ) as ydl:
                ydl.download([url])

        _ydl_retry(_audio)
        media = next(p for p in out.glob("audio.*"))
        meta = _transcrever_whisper(job_id, media, dur)
        media.unlink(missing_ok=True)
        _set(job_id, status="done", progress=1.0, etapa="concluído", **meta)
    except Exception as e:
        _set(job_id, status="error", etapa=f"erro: {str(e)[:300]}")


@app.get("/healthz")
def healthz():
    return {"ok": True, "jobs": len(JOBS), "model": MODEL_NAME, "proxy": bool(PROXY)}


@app.post("/api/jobs")
async def criar_job(request: Request):
    _check(request)
    body = await request.json()
    url = (body.get("url") or "").strip()
    if not url.startswith(("http://", "https://")):
        raise HTTPException(400, "url inválida")
    job_id = uuid.uuid4().hex[:12]
    with LOCK:
        JOBS[job_id] = {
            "id": job_id,
            "url": url,
            "modo": body.get("modo") or "auto",
            "status": "running",
            "etapa": "na fila…",
            "progress": 0.0,
            "criado": int(time.time()),
        }
    POOL.submit(_run_job, job_id)
    return {"id": job_id}


@app.post("/api/upload")
async def upload(request: Request, file: UploadFile):
    _check(request)
    job_id = uuid.uuid4().hex[:12]
    out = DATA / job_id
    out.mkdir(exist_ok=True)
    dst = out / ("upload_" + re.sub(r"[^\w.\-]", "_", file.filename or "arquivo"))
    with dst.open("wb") as f:
        while chunk := await file.read(1024 * 1024):
            f.write(chunk)
    with LOCK:
        JOBS[job_id] = {
            "id": job_id,
            "titulo": file.filename,
            "upload": True,
            "_src": str(dst),
            "status": "running",
            "etapa": "na fila…",
            "progress": 0.0,
            "criado": int(time.time()),
        }
    POOL.submit(_run_job, job_id)
    return {"id": job_id}


@app.get("/api/jobs")
def listar(request: Request):
    _check(request)
    with LOCK:
        items = [{k: v for k, v in j.items() if k != "_src"} for j in JOBS.values()]
    return sorted(items, key=lambda j: -j["criado"])


@app.get("/api/jobs/{job_id}/download")
def baixar(job_id: str, request: Request, fmt: str = "txt"):
    _check(request)
    ext = "srt" if fmt == "srt" else ("vtt" if fmt == "vtt" else "txt")
    path = DATA / job_id / f"transcricao.{ext}"
    if not path.exists():
        raise HTTPException(404, "não encontrado")
    nome = re.sub(r"[^\w\- ]", "", (JOBS.get(job_id, {}).get("titulo") or job_id))[:60] or job_id
    return FileResponse(path, filename=f"{nome}.{ext}", media_type="text/plain; charset=utf-8")


@app.get("/api/jobs/{job_id}/texto")
def texto(job_id: str, request: Request):
    _check(request)
    path = DATA / job_id / "transcricao.txt"
    if not path.exists():
        raise HTTPException(404, "não encontrado")
    return JSONResponse({"texto": path.read_text(encoding="utf-8")})


UI = """<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Transcriber</title><style>
:root{--bg:#0d1117;--card:#161b22;--bd:#30363d;--tx:#e6edf3;--mut:#8b949e;--ac:#2ea87f}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--tx);
font:15px/1.5 system-ui,Segoe UI,sans-serif;padding:32px 16px}
.wrap{max-width:820px;margin:0 auto}h1{font-size:22px;margin:0 0 4px}
.sub{color:var(--mut);margin-bottom:24px;font-size:13px}
.card{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:18px;margin-bottom:14px}
input[type=text],input[type=password]{width:100%;background:#0d1117;border:1px solid var(--bd);
border-radius:8px;color:var(--tx);padding:10px 12px;font-size:14px}
.row{display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;align-items:center}
button{background:var(--ac);border:0;border-radius:8px;color:#fff;padding:10px 18px;
font-size:14px;font-weight:600;cursor:pointer}button.sec{background:#21262d;border:1px solid var(--bd)}
label.chk{color:var(--mut);font-size:13px;display:flex;gap:6px;align-items:center}
.job{border-top:1px solid var(--bd);padding:12px 0}.job:first-child{border-top:0}
.jt{font-weight:600;font-size:14px;word-break:break-all}.jm{color:var(--mut);font-size:12px;margin-top:2px}
.bar{height:6px;background:#21262d;border-radius:4px;margin-top:8px;overflow:hidden}
.bar i{display:block;height:100%;background:var(--ac);transition:width .6s}
.err .bar i{background:#d0454c}.dl{margin-top:8px;display:flex;gap:8px}
.dl a{color:var(--ac);font-size:13px;text-decoration:none;border:1px solid var(--bd);
border-radius:6px;padding:4px 10px}#drop{border:2px dashed var(--bd);border-radius:10px;
padding:22px;text-align:center;color:var(--mut);font-size:13px;cursor:pointer}
#drop.on{border-color:var(--ac);color:var(--ac)}</style></head><body><div class="wrap">
<h1>🎙 Transcriber</h1><div class="sub">Transcrição de vídeo de qualquer tamanho — cole a URL (YouTube ou qualquer site) ou envie um arquivo. Legenda pronta quando existir; Whisper quando não.</div>
<div class="card"><input type="text" id="url" placeholder="https://www.youtube.com/watch?v=…">
<div class="row"><button onclick="go()">Transcrever</button>
<label class="chk"><input type="checkbox" id="forcaw"> forçar Whisper (ignorar legenda)</label></div></div>
<div class="card"><div id="drop">…ou arraste/clique pra enviar um arquivo de vídeo/áudio
<input type="file" id="file" style="display:none"></div></div>
<div class="card" id="jobs"><div class="jm">nenhum job ainda</div></div></div><script>
const $=s=>document.querySelector(s);
async function go(){const u=$('#url').value.trim();if(!u)return;
const r=await fetch('/api/jobs',{method:'POST',
headers:{'Content-Type':'application/json'},body:JSON.stringify({url:u,modo:$('#forcaw').checked?'whisper':'auto'})});
if(!r.ok){alert('erro ao criar job');return}$('#url').value='';poll()}
const drop=$('#drop');drop.onclick=()=>$('#file').click();
['dragover','dragleave','drop'].forEach(ev=>drop.addEventListener(ev,e=>{e.preventDefault();
drop.classList.toggle('on',ev=='dragover')}));
drop.addEventListener('drop',e=>envia(e.dataTransfer.files[0]));
$('#file').onchange=e=>envia(e.target.files[0]);
async function envia(f){if(!f)return;drop.textContent='enviando '+f.name+'…';
const fd=new FormData();fd.append('file',f);
const r=await fetch('/api/upload',{method:'POST',body:fd});
drop.textContent='…ou arraste/clique pra enviar um arquivo de vídeo/áudio';
if(!r.ok)alert('erro no upload');poll()}
async function poll(){const r=await fetch('/api/jobs');
if(!r.ok)return;const js=await r.json();const el=$('#jobs');
if(!js.length){el.innerHTML='<div class="jm">nenhum job ainda</div>';return}
el.innerHTML=js.map(j=>{const p=Math.round((j.progress||0)*100);
const dur=j.duracao?` · ${Math.round(j.duracao/60)}min`:'';
const links=j.status=='done'?`<div class="dl">
<a href="/api/jobs/${j.id}/download?fmt=txt">⬇ .txt</a>
${j.fonte&&j.fonte.startsWith('whisper')?`<a href="/api/jobs/${j.id}/download?fmt=srt">⬇ .srt</a>`:''}
</div>`:'';
return `<div class="job ${j.status=='error'?'err':''}"><div class="jt">${j.titulo||j.url||j.id}</div>
<div class="jm">${j.etapa||''}${dur}${j.fonte?' · '+j.fonte:''}</div>
<div class="bar"><i style="width:${p}%"></i></div>${links}</div>`}).join('')}
setInterval(poll,3000);poll();</script></body></html>"""


@app.get("/", response_class=HTMLResponse)
def index():
    return UI
