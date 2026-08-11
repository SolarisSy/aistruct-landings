/**
 * Integrações da Paggins — cor oficial da marca + símbolo.
 * `glyph` = slug de um SVG real embutido (só onde há logo oficial limpa);
 * senão usa monograma na cor da marca. Nada de PNG com fundo estranho.
 */
export type Integracao = {
  nome: string;
  cat: string;
  cor: string;        // cor oficial da marca (hex)
  mono: string;       // monograma fallback (1-2 letras)
  glyph?: 'facebook' | 'googleads';
  conectado: boolean;
};

export const INTEGRACOES: Integracao[] = [
  { nome: 'UTMify', cat: 'Rastreamento', cor: '#00C853', mono: 'U', conectado: true },
  { nome: 'RedTrack', cat: 'Rastreamento', cor: '#F5333F', mono: 'R', conectado: true },
  { nome: 'Facebook Pixel', cat: 'Pixel', cor: '#0866FF', mono: 'f', glyph: 'facebook', conectado: true },
  { nome: 'Google Ads', cat: 'Pixel', cor: '#4285F4', mono: 'G', glyph: 'googleads', conectado: false },
  { nome: 'Active Campaign', cat: 'E-mail', cor: '#356AE6', mono: 'AC', conectado: false },
  { nome: 'Mandaê', cat: 'Logística', cor: '#00C2B2', mono: 'M', conectado: true },
  { nome: 'Bling', cat: 'ERP / NF-e', cor: '#00A8E8', mono: 'BL', conectado: true },
  { nome: 'Typebot', cat: 'Chatbot', cor: '#7048E8', mono: 'T', conectado: false },
];
