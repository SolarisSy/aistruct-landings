"use client";

import { useEffect, useMemo, Suspense } from 'react';
import { useRouter, usePathname, useSearchParams } from 'next/navigation';

/**
 * Componente que gerencia a persistência de parâmetros UTM e outros parâmetros de rastreamento
 * - Armazena parâmetros UTM no localStorage quando detectados na URL
 * - Adiciona parâmetros UTM a links para páginas externas
 */
function UtmHandlerInner() {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();

  // Lista de parâmetros UTM e outros para rastrear
  const trackingParams = useMemo(() => [
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'utm_term',
    'fbclid',
    'gclid'
  ], []);

  // Salva os parâmetros UTM no localStorage quando eles estão presentes na URL
  useEffect(() => {
    if (!searchParams) return;

    const hasUtmParams = trackingParams.some(param => searchParams.has(param));
    
    if (hasUtmParams) {
      const utmData: Record<string, string> = {};
      
      trackingParams.forEach(param => {
        const value = searchParams.get(param);
        if (value) {
          utmData[param] = value;
        }
      });
      
      // Salvar no localStorage
      localStorage.setItem('utm_data', JSON.stringify(utmData));
      localStorage.setItem('utm_timestamp', Date.now().toString());
    }
  }, [searchParams, trackingParams]);

  // Adiciona eventos para capturar cliques em links externos e adicionar parâmetros UTM
  useEffect(() => {
    const handleLinkClick = (event: MouseEvent) => {
      const target = event.target as HTMLElement;
      const link = target.closest('a') as HTMLAnchorElement;
      
      if (!link) return;
      
      // Verifica se é um link externo para o site de checkout
      if (link.href && 
          (link.href.includes('checkout-ff') || 
           link.getAttribute('data-track-utm') === 'true')) {
        
        // Recupera UTMs do localStorage
        const utmDataStr = localStorage.getItem('utm_data');
        if (!utmDataStr) return;
        
        // Impede o comportamento padrão para modificar o link
        event.preventDefault();
        
        try {
          const utmData = JSON.parse(utmDataStr) as Record<string, string>;
          const url = new URL(link.href);
          
          // Adiciona os parâmetros UTM à URL
          Object.entries(utmData).forEach(([key, value]) => {
            if (value && !url.searchParams.has(key)) {
              url.searchParams.append(key, value);
            }
          });
          
          // Redireciona para a URL com os parâmetros
          window.location.href = url.toString();
        } catch (error) {
          console.error('Erro ao processar UTMs:', error);
          window.location.href = link.href; // Fallback para o link original
        }
      }
    };
    
    // Adiciona listener para capturar cliques
    document.addEventListener('click', handleLinkClick);
    
    return () => {
      document.removeEventListener('click', handleLinkClick);
    };
  }, [trackingParams]);

  return null; // Componente não renderiza nada, apenas executa lógica
}

export function UtmHandler() {
  return (
    <Suspense fallback={null}>
      <UtmHandlerInner />
    </Suspense>
  );
}

/**
 * Função utilitária para recuperar parâmetros UTM do localStorage
 * Pode ser usada em componentes para acessar os valores UTM
 */
export function getUtmParams(): Record<string, string> {
  if (typeof window === 'undefined') return {};
  
  try {
    const utmDataStr = localStorage.getItem('utm_data');
    if (!utmDataStr) return {};
    
    return JSON.parse(utmDataStr) as Record<string, string>;
  } catch (error) {
    console.error('Erro ao recuperar UTMs:', error);
    return {};
  }
}

/**
 * Função para adicionar parâmetros UTM a uma URL
 * Útil para links programáticos ou redirecionamentos
 */
export function addUtmParamsToUrl(url: string): string {
  if (typeof window === 'undefined') return url;
  
  try {
    const utmDataStr = localStorage.getItem('utm_data');
    if (!utmDataStr) return url;
    
    const utmData = JSON.parse(utmDataStr) as Record<string, string>;
    const urlObj = new URL(url);
    
    Object.entries(utmData).forEach(([key, value]: [string, string]) => {
      if (value && !urlObj.searchParams.has(key)) {
        urlObj.searchParams.append(key, value);
      }
    });
    
    return urlObj.toString();
  } catch (error: unknown) {
    console.error('Erro ao adicionar UTMs à URL:', error);
    return url;
  }
} 