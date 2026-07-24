import { NextResponse, NextRequest } from 'next/server';

// Lista de User-Agents de bots conhecidos (pode ser expandida)
// Fonte de inspiração: https://github.com/monperrus/crawler-user-agents/blob/master/crawler-user-agents.json
// Adaptado para ser mais genérico e incluir os principais bots de ads.
const BOT_UA_PATTERNS: RegExp[] = [
    /googlebot/i,
    /adsbot-google/i,
    /google-ads/i,
    /mediapartners-google/i,
    /apis-google/i,
    /bingbot/i,
    /msnbot/i,
    /adidxbot/i,
    /bingpreview/i,
    /facebookexternalhit/i,
    /facebookcatalog/i,
    /facebot/i,
    /pinterest/i,
    /twitterbot/i,
    /linkedinbot/i,
    /applebot/i,
    /duckduckbot/i,
    /baiduspider/i,
    /yandexbot/i,
    /sogou/i,
    /exabot/i,
    /slurp/i,        // Yahoo
    /ia_archiver/i,  // Alexa
    /crawler/i,
    /spider/i,
    /bot/i,          // Genérico, cuidado com falsos positivos. Colocar por último.
];

const SAFE_PAGE_URL = 'https://www.freefiremania.com.br/';
const ALLOWED_COUNTRY = 'BR'; // Permitir tráfego do Brasil para a money page

export async function middleware(request: NextRequest) {
    const userAgentString = request.headers.get('user-agent');

    // Obter IP SOMENTE de cabeçalhos
    let ip: string | undefined;
    const xff = request.headers.get('x-forwarded-for');
    if (xff) {
        ip = xff.split(',')[0].trim();
    }

    // Obter país SOMENTE de cabeçalhos
    let country: string | undefined;
    const vercelCountry = request.headers.get('x-vercel-ip-country');
    const cloudflareCountry = request.headers.get('cf-ipcountry');
    // Você pode adicionar mais fallbacks de cabeçalhos de país aqui se necessário
    country = vercelCountry || cloudflareCountry || undefined;

    let isBotUA = false;
    if (userAgentString) {
        for (const pattern of BOT_UA_PATTERNS) {
            if (pattern.test(userAgentString)) {
                isBotUA = true;
                break;
            }
        }
    }
    // Se country for undefined (nenhum cabeçalho encontrado), undefined !== 'BR' será true.
    // Isso significa que, por padrão de segurança, se não pudermos determinar o país, trataremos como se devesse ver a SAFE page.
    const shouldServeSafePage = isBotUA || (country !== ALLOWED_COUNTRY);

    const ipToLog = ip || 'IP N/A (from headers)';
    const countryToLog = country || 'Country N/A (from headers)';
    const uaToLog = userAgentString || 'UA N/A';

    if (shouldServeSafePage) {
        try {
            const reason = isBotUA ? `Bot UA: ${uaToLog}` : `Country: ${countryToLog}`;
            console.log(`[Middleware] Serving SAFE page (${SAFE_PAGE_URL}) to ${ipToLog} (${reason})`);
            
            const externalResponse = await fetch(SAFE_PAGE_URL, {
                headers: {
                    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language': 'en-US,en;q=0.9',
                    'Cache-Control': 'no-cache',
                }
            });

            if (!externalResponse.ok) {
                console.error(`[Middleware] Error fetching SAFE page: ${externalResponse.status} ${externalResponse.statusText}. IP: ${ipToLog}`);
                // Fallback: Se falhar ao buscar a safe page, permite o acesso à money page por enquanto.
                // Idealmente, serviria uma página de erro estática local para evitar expor a money page.
                // Ex: return new NextResponse('Cloaking protection page unavailable.', { status: 503 });
                return NextResponse.next(); 
            }

            const safePageHtml = await externalResponse.text();
            
            return new NextResponse(safePageHtml, {
                status: 200,
                headers: {
                    'Content-Type': externalResponse.headers.get('Content-Type') || 'text/html; charset=utf-8',
                },
            });

        } catch (error) {
            console.error(`[Middleware] Exception fetching/serving SAFE page for IP ${ipToLog}:`, error);
            // Em caso de exceção (ex: rede), permite o acesso à money page por enquanto.
            return NextResponse.next();
        }
    }

    // Se não se enquadra nas condições acima (é do Brasil e não é um bot conhecido pelo UA), serve a money page.
    console.log(`[Middleware] Serving MONEY page to ${ipToLog} (Country: ${countryToLog}, UA: ${uaToLog})`);
    return NextResponse.next();
}

export const config = {
    matcher: [
        /*
         * Match all request paths except for the ones starting with:
         * - api (API routes)
         * - _next/static (static files)
         * - _next/image (image optimization files)
         * - favicon.ico (favicon file)
         * - /public (ou caminhos específicos de assets como /images/, /assets/)
         * Adicionamos um padrão negativo mais genérico para arquivos com extensões comuns.
         */
        '/((?!api|_next/static|_next/image|favicon.ico|.*\.(?:svg|png|jpg|jpeg|gif|webp|js|css|json|xml|txt|map)$).*)',
    ],
}; 