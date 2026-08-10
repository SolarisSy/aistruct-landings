import { NextRequest, NextResponse } from 'next/server';
import { AGENTES_CFG, KB_DEMO, type AgenteTipo, type KbContext } from '@/lib/agent-kb';

export const runtime = 'nodejs';

const MODEL = 'claude-haiku-4-5';
const MAX_TOKENS = 400;

type Msg = { role: 'user' | 'assistant'; content: string };

/* ---- guardas do proxy (evita DoS/queima de tokens/abuso na chave do dono) ---- */
// rate-limit simples em memória: N requisições por janela, por IP.
const RL_MAX = 20;
const RL_WINDOW_MS = 60_000;
const hits = new Map<string, number[]>();

function rateLimited(ip: string): boolean {
  const now = Date.now();
  const arr = (hits.get(ip) ?? []).filter((t) => now - t < RL_WINDOW_MS);
  arr.push(now);
  hits.set(ip, arr);
  if (hits.size > 5000) hits.clear(); // teto de memória
  return arr.length > RL_MAX;
}

function sameOrigin(req: NextRequest): boolean {
  const origin = req.headers.get('origin');
  if (!origin) return true; // same-origin fetch do próprio app não manda Origin
  const host = req.headers.get('host') ?? '';
  try {
    return new URL(origin).host === host;
  } catch {
    return false;
  }
}

/**
 * Motor do agente de IA (checkout / tutor). Chama a Claude Messages API.
 * Se ANTHROPIC_API_KEY não estiver setada, responde em modo degradado (sem quebrar a UI).
 */
export async function POST(req: NextRequest) {
  // 1) só aceita chamada da própria origem (bloqueia uso do proxy por terceiros)
  if (!sameOrigin(req)) {
    return NextResponse.json({ error: 'origem não permitida' }, { status: 403 });
  }
  // 2) rate-limit por IP
  const ip = req.headers.get('x-forwarded-for')?.split(',')[0]?.trim() || 'local';
  if (rateLimited(ip)) {
    return NextResponse.json({ error: 'muitas requisições, aguarde um momento' }, { status: 429 });
  }

  let body: { tipo?: AgenteTipo; messages?: Msg[]; ctx?: Partial<KbContext> };
  try {
    body = await req.json();
  } catch {
    return NextResponse.json({ error: 'json inválido' }, { status: 400 });
  }
  // 3) limita tamanho da conversa (anti-abuso de contexto)
  if ((body.messages?.length ?? 0) > 20) {
    return NextResponse.json({ error: 'conversa muito longa' }, { status: 400 });
  }

  const tipo: AgenteTipo = body.tipo === 'tutor' ? 'tutor' : 'checkout';
  const cfg = AGENTES_CFG[tipo];
  const ctx: KbContext = { ...KB_DEMO[tipo], ...(body.ctx ?? {}) };
  const messages = (body.messages ?? []).filter(
    (m) => (m.role === 'user' || m.role === 'assistant') && typeof m.content === 'string'
  );

  const key = process.env.ANTHROPIC_API_KEY?.trim();
  if (!key) {
    return NextResponse.json({
      reply:
        'O agente ainda não está conectado à IA (falta configurar ANTHROPIC_API_KEY no servidor). ' +
        'Assim que a chave for adicionada, eu respondo de verdade. 🙂',
      degraded: true,
    });
  }

  try {
    const r = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: {
        'x-api-key': key,
        'anthropic-version': '2023-06-01',
        'content-type': 'application/json',
      },
      body: JSON.stringify({
        model: MODEL,
        max_tokens: MAX_TOKENS,
        system: cfg.system(ctx),
        messages: messages.map((m) => ({ role: m.role, content: m.content })),
      }),
    });

    if (!r.ok) {
      const detail = await r.text();
      return NextResponse.json(
        { reply: 'Tive um problema pra responder agora, tenta de novo em instantes.', error: detail.slice(0, 300) },
        { status: 502 }
      );
    }

    const data = await r.json();
    const reply =
      (data?.content ?? [])
        .filter((b: { type: string }) => b.type === 'text')
        .map((b: { text: string }) => b.text)
        .join('\n')
        .trim() || 'Desculpa, não entendi — pode reformular?';

    return NextResponse.json({ reply });
  } catch (e) {
    return NextResponse.json(
      { reply: 'Falha de conexão com o assistente. Tenta novamente.', error: String(e).slice(0, 200) },
      { status: 502 }
    );
  }
}
