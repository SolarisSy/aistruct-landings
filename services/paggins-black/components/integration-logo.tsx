import type { Integracao } from '@/lib/integrations';

/* Glyphs oficiais (Simple Icons, monocromáticos) — colorimos com a cor da marca. */
const GLYPHS: Record<string, string> = {
  facebook:
    'M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z',
  googleads:
    'M3.9998 22.9291C1.7908 22.9291 0 21.1383 0 18.9293s1.7908-3.9998 3.9998-3.9998 3.9998 1.7908 3.9998 3.9998-1.7908 3.9998-3.9998 3.9998zm19.4643-6.0004L15.4632 3.072C14.3586 1.1587 11.9121.5028 9.9988 1.6074S7.4295 5.1585 8.5341 7.0718l8.0009 13.8567c1.1046 1.9133 3.5511 2.5679 5.4644 1.4646 1.9134-1.1046 2.568-3.5511 1.4647-5.4644zM7.5137 4.8438L1.5645 15.1484A4.5 4.5 0 0 1 4 14.4297c2.5597-.0075 4.6248 2.1585 4.4941 4.7148l3.2168-5.5723-3.6094-6.25c-.4499-.7793-.6322-1.6394-.5878-2.4784z',
};

/**
 * Ícone da integração: tile arredondado com a COR DA MARCA (tint escuro + borda sutil)
 * e, dentro, o glyph oficial (quando existe) ou o monograma — tudo na cor cheia da marca.
 * Sem PNG, sem fundo branco — legível e consistente no tema dark.
 */
export function IntegrationLogo({ item, size = 44 }: { item: Integracao; size?: number }) {
  const { cor, mono, glyph } = item;
  return (
    <span
      className="flex shrink-0 items-center justify-center rounded-[12px] border"
      style={{
        width: size,
        height: size,
        background: `${cor}1f`,      // ~12% de opacidade → tint escuro
        borderColor: `${cor}3d`,     // ~24% → borda sutil da marca
      }}
      aria-hidden
    >
      {glyph ? (
        <svg width={size * 0.5} height={size * 0.5} viewBox="0 0 24 24" fill={cor}>
          <path d={GLYPHS[glyph]} />
        </svg>
      ) : (
        <span
          className="font-bold leading-none"
          style={{ color: cor, fontSize: mono.length > 1 ? size * 0.32 : size * 0.44 }}
        >
          {mono}
        </span>
      )}
    </span>
  );
}
