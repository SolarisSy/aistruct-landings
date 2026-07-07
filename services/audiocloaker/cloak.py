"""
AudioCloaker — motor de processamento.

Técnica: CANCELAMENTO DE FASE ESTÉREO.
A voz é colocada em antifase entre os canais L e R (L = voz, R = voz * -1),
mais um chiado comum de banda alta (11-15 kHz) presente nos dois canais.

  - Downmix mono (L+R)  -> a voz se CANCELA (interferência destrutiva) -> sobra chiado.
    É exatamente o que fazem Whisper, legendas automáticas do YouTube e rips casuais
    (`ffmpeg -ac 1`), então todos capturam silêncio/chiado ao invés da fala.
  - A voz limpa continua em QUALQUER canal isolado (L ou R) ou em L-R, para o humano
    que assiste em estéreo/fone.

Este módulo expõe três operações: cloak(), detect() e recover().
"""

from __future__ import annotations

import os
import re
import shutil
import subprocess
from dataclasses import dataclass, field
from typing import Callable, Optional

# --------------------------------------------------------------------------- #
# Localização dos binários
# --------------------------------------------------------------------------- #

def _resolve_binary(name: str, env_var: str) -> str:
    """Acha o binário: env var -> PATH -> caminho conhecido do WinGet (Windows)."""
    override = os.environ.get(env_var)
    if override and os.path.exists(override):
        return override
    found = shutil.which(name)
    if found:
        return found
    # Fallback comum de instalação via WinGet no Windows.
    winget = os.path.expanduser(
        r"~\AppData\Local\Microsoft\WinGet\Packages"
        r"\Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe"
        r"\ffmpeg-8.1.1-full_build\bin\%s.exe" % name
    )
    if os.path.exists(winget):
        return winget
    return name  # deixa o subprocess falhar com mensagem clara


FFMPEG = _resolve_binary("ffmpeg", "AUDIOCLOAKER_FFMPEG")
FFPROBE = _resolve_binary("ffprobe", "AUDIOCLOAKER_FFPROBE")


class CloakError(RuntimeError):
    """Erro de negócio (arquivo inválido, sem áudio, etc.) — seguro para o usuário."""


# --------------------------------------------------------------------------- #
# Sondagem do arquivo (ffprobe)
# --------------------------------------------------------------------------- #

@dataclass
class MediaInfo:
    duration: float
    has_video: bool
    has_audio: bool
    audio_channels: int


def probe(path: str) -> MediaInfo:
    def _probe(select: str, entry: str) -> str:
        cmd = [
            FFPROBE, "-v", "error",
            "-select_streams", select,
            "-show_entries", entry,
            "-of", "default=nokey=1:noprint_wrappers=1",
            path,
        ]
        out = subprocess.run(cmd, capture_output=True, text=True)
        return out.stdout.strip()

    dur_raw = _probe("v:0", "format=duration") or _probe("a:0", "format=duration")
    # format=duration não depende do stream selecionado; simplifica:
    dur_out = subprocess.run(
        [FFPROBE, "-v", "error", "-show_entries", "format=duration",
         "-of", "default=nokey=1:noprint_wrappers=1", path],
        capture_output=True, text=True,
    ).stdout.strip()
    try:
        duration = float(dur_out)
    except ValueError:
        duration = 0.0

    vcodec = _probe("v:0", "stream=codec_type")
    acodec = _probe("a:0", "stream=codec_type")
    channels_raw = _probe("a:0", "stream=channels")
    try:
        channels = int(channels_raw)
    except ValueError:
        channels = 0

    return MediaInfo(
        duration=duration,
        has_video=vcodec == "video",
        has_audio=acodec == "audio",
        audio_channels=channels,
    )


# --------------------------------------------------------------------------- #
# Runner com progresso
# --------------------------------------------------------------------------- #

def _run_ffmpeg(args: list[str], total_seconds: float = 0.0,
                on_progress: Optional[Callable[[float], None]] = None) -> None:
    """
    Roda ffmpeg com `-progress pipe:1`. Reporta 0..1 via on_progress.
    Levanta CloakError com o tail do stderr em caso de falha.
    """
    cmd = [FFMPEG, "-hide_banner", "-nostdin", "-y",
           "-loglevel", "error", "-progress", "pipe:1", "-nostats", *args]

    proc = subprocess.Popen(
        cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
        text=True, bufsize=1,
    )
    assert proc.stdout is not None
    for line in proc.stdout:
        if on_progress and total_seconds > 0 and line.startswith("out_time_ms="):
            try:
                us = int(line.split("=", 1)[1])
                on_progress(min(0.99, (us / 1_000_000) / total_seconds))
            except ValueError:
                pass
    proc.wait()
    stderr_tail = (proc.stderr.read() if proc.stderr else "") or ""
    if proc.returncode != 0:
        tail = stderr_tail.strip().splitlines()[-6:]
        raise CloakError("ffmpeg falhou: " + " | ".join(tail))
    if on_progress:
        on_progress(1.0)


# --------------------------------------------------------------------------- #
# CLOAK — esconde a voz das nossas mídias
# --------------------------------------------------------------------------- #

def _audio_codec_args(ext: str) -> list[str]:
    """Codec de áudio compatível com o container de saída (permite manter a extensão original)."""
    ext = ext.lower()
    if ext in (".webm", ".ogg", ".opus"):
        return ["-c:a", "libopus", "-b:a", "160k"]
    if ext == ".mp3":
        return ["-c:a", "libmp3lame", "-q:a", "2"]
    if ext == ".wav":
        return ["-c:a", "pcm_s16le"]
    if ext == ".flac":
        return ["-c:a", "flac"]
    return ["-c:a", "aac", "-b:a", "160k"]  # mp4/mov/mkv/m4v/aac/m4a/avi


def cloak(input_path: str, output_path: str, hiss: float = 0.0,
          on_progress: Optional[Callable[[float], None]] = None) -> MediaInfo:
    """
    Aplica o cloak de fase:
      FL = voz + chiado(11-15k)
      FR = (voz * -1) + chiado(11-15k)
    Downmix mono (FL+FR) cancela a voz.

    `hiss` (0.0-0.3) = volume do chiado de cobertura adicionado. DEFAULT 0.0 =
    fase pura, chão inaudível (mono >8k ~= -85 dB) — igual aos criativos de referência.
    Medição (voz limpa): 0.0 -> -85 dB | 0.01 -> -54 dB | 0.05 -> -40 dB (audível).
    Só aumente se precisar de ruído de cobertura explícito; acima de ~0.01 já fica audível.
    """
    info = probe(input_path)
    if not info.has_audio:
        raise CloakError("O arquivo não tem trilha de áudio para proteger.")
    if info.audio_channels < 1:
        raise CloakError("Não foi possível ler os canais de áudio.")

    hiss = max(0.0, min(0.3, float(hiss)))
    dur = info.duration or 3600.0

    fc = (
        "[0:a:0]aformat=channel_layouts=mono[voz];"
        f"[1:a]highpass=f=11000,lowpass=f=15000,volume={hiss:.4f},asplit=2[h1][h2];"
        "[voz]asplit=2[s1][s2];"
        "[s1][h1]amix=inputs=2:normalize=0[FL];"
        "[s2]volume=-1[sn];"
        "[sn][h2]amix=inputs=2:normalize=0[FR];"
        "[FL][FR]join=inputs=2:channel_layout=stereo[aout]"
    )

    out_ext = os.path.splitext(output_path)[1].lower()
    acodec = _audio_codec_args(out_ext)

    args = [
        "-i", input_path,
        "-f", "lavfi", "-t", f"{dur:.3f}", "-i", "anoisesrc=c=white:a=0.9",
        "-filter_complex", fc,
    ]
    if info.has_video:
        args += ["-map", "0:v", "-map", "[aout]", "-c:v", "copy", *acodec]
        if out_ext in (".mp4", ".mov", ".m4v"):
            args += ["-movflags", "+faststart"]
    else:
        args += ["-map", "[aout]", *acodec]
    args += ["-shortest", output_path]

    _run_ffmpeg(args, total_seconds=dur, on_progress=on_progress)
    return info


# --------------------------------------------------------------------------- #
# DETECT — o arquivo já está cloakado por fase?
# --------------------------------------------------------------------------- #

_MEAN_RE = re.compile(r"mean_volume:\s*(-?\d+(?:\.\d+)?)\s*dB")

_PANS = {
    "L":    "c0=c0",
    "R":    "c0=c1",
    "L-R":  "c0=c0-c1",
    "mono": "c0=0.5*c0+0.5*c1",
}


def _measure_speech_band(path: str, pan_expr: str) -> Optional[float]:
    """mean_volume (dB) da banda de fala (<4 kHz) de um downmix específico."""
    cmd = [
        FFMPEG, "-hide_banner", "-nostdin",
        "-i", path,
        "-filter_complex", f"[0:a:0]pan=mono|{pan_expr},lowpass=f=4000,volumedetect",
        "-f", "null", "-",
    ]
    out = subprocess.run(cmd, capture_output=True, text=True)
    m = _MEAN_RE.search(out.stderr)
    return float(m.group(1)) if m else None


@dataclass
class DetectResult:
    is_cloaked: bool
    channels: int
    measurements: dict = field(default_factory=dict)  # nome -> dB
    best_channel: Optional[str] = None                # canal com voz mais clara
    gap_db: Optional[float] = None                    # isolado - mono
    verdict: str = ""


def detect(input_path: str) -> DetectResult:
    info = probe(input_path)
    if not info.has_audio:
        raise CloakError("O arquivo não tem trilha de áudio.")

    if info.audio_channels < 2:
        return DetectResult(
            is_cloaked=False, channels=info.audio_channels,
            verdict="Áudio mono — impossível cloak por fase estéreo (precisa de 2 canais).",
        )

    meas = {name: _measure_speech_band(input_path, expr) for name, expr in _PANS.items()}
    valid = {k: v for k, v in meas.items() if v is not None}
    mono = valid.get("mono")
    isolated = {k: v for k, v in valid.items() if k in ("L", "L-R")}

    if mono is None or not isolated:
        return DetectResult(
            is_cloaked=False, channels=info.audio_channels, measurements=meas,
            verdict="Não foi possível medir os canais.",
        )

    best_channel = max(isolated, key=isolated.get)
    gap = isolated[best_channel] - mono
    # Cloakado: mono muito mais fraco que o melhor canal isolado, e o isolado tem fala real.
    is_cloaked = gap >= 15.0 and isolated[best_channel] > -35.0

    if is_cloaked:
        verdict = (
            f"CLOAKADO por fase. Voz cancela no mono (queda de {gap:.1f} dB). "
            f"Recuperar isolando o canal '{best_channel}'."
        )
    else:
        verdict = "Áudio normal — a voz sobrevive ao downmix mono (não está cloakado por fase)."

    return DetectResult(
        is_cloaked=is_cloaked, channels=info.audio_channels,
        measurements=meas, best_channel=best_channel, gap_db=gap, verdict=verdict,
    )


# --------------------------------------------------------------------------- #
# RECOVER — recupera a voz de um áudio cloakado (isola um canal)
# --------------------------------------------------------------------------- #

def recover(input_path: str, output_path: str, method: str = "L",
            on_progress: Optional[Callable[[float], None]] = None) -> MediaInfo:
    """
    Recupera a voz de um arquivo cloakado por fase, isolando um downmix:
      method = "L"    -> canal esquerdo (c0)
      method = "R"    -> canal direito  (c1)
      method = "L-R"  -> diferença (costuma cancelar o chiado comum, melhor SNR)
    Retorna um MP3 mono só com a voz (pronto pra transcrever/ouvir).
    """
    expr = _PANS.get(method)
    if expr is None or method == "mono":
        raise CloakError(f"Método de recuperação inválido: {method!r}")

    info = probe(input_path)
    if not info.has_audio:
        raise CloakError("O arquivo não tem trilha de áudio.")

    dur = info.duration or 3600.0
    args = [
        "-i", input_path,
        "-filter_complex", f"[0:a:0]pan=mono|{expr}[a]",
        "-map", "[a]", "-c:a", "libmp3lame", "-q:a", "2",
        output_path,
    ]
    _run_ffmpeg(args, total_seconds=dur, on_progress=on_progress)
    return info
