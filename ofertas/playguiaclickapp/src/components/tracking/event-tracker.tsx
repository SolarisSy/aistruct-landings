"use client";

import { sendDiscordWebhook, generateUserId, getBrowserInfo } from '@/utils/discord-webhook';

/**
 * Módulo para rastreamento de eventos personalizados (ex: Google Ads, Meta Pixel)
 * Pode ser expandido para incluir outras plataformas de rastreamento
 */

// Interface para itens como esperado pelo dataLayer/Google
interface DataLayerItem {
  item_id: string;
  item_name: string;
  price: number;
  quantity: number;
  // Adicionar outros campos se necessário (item_category, etc.)
}

// Interface genérica para parâmetros de eventos, permitindo valores primitivos ou arrays de TrackingItem
export interface EventParams {
  [key: string]: string | number | boolean | undefined | DataLayerItem[];
}

interface TrackingItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
}

// Define os tipos de eventos personalizados que podem ser rastreados
type CustomEventName =
  | 'page_view'
  | 'login'
  | 'checkout_start'
  | 'payment_selected'
  | 'purchase'
  | 'click'
  | 'form_submit'
  | string;
  // Adicione outros nomes de eventos personalizados aqui

// Define a interface para selecionar plataformas de rastreamento
interface PlatformSelection {
  gtag?: boolean;
  meta?: boolean;
  // Adicione outras plataformas aqui, se necessário
}

// Interface para o evento begin_checkout
interface BeginCheckoutEventData {
  currency: string;
  value: number;
  items?: Array<{ item_id: string; item_name: string; price: number; quantity: number }>;
}

// Interface para o evento purchase
interface PurchaseEventData {
  currency: string;
  transaction_id: string;
  value: number;
  items?: Array<{ item_id: string; item_name: string; price: number; quantity: number }>;
  tax?: number;
  shipping?: number;
  coupon?: string;
}

/**
 * Função principal para enviar eventos para o dataLayer.
 * @param eventName Nome do evento (ex: 'login', 'purchase', 'begin_checkout')
 * @param eventParams Objeto com os dados do evento
 */
const trackDataLayerEvent = (
  eventName: CustomEventName,
  eventParams: EventParams = {}
) => {
  /* // Comentado para desativar o rastreamento
  if (typeof window !== 'undefined' && window.dataLayer) {
    console.log(`[DataLayer] Pushing event: ${eventName}`, eventParams);
    window.dataLayer.push({
      event: eventName,
      ...eventParams,
    });
  } else {
    console.warn(`[DataLayer] window.dataLayer not found. Event not pushed: ${eventName}`);
  }
  */
  console.log(`[Tracking Desativado] Evento: ${eventName}`, eventParams);
};

// Eventos predefinidos para facilitar uso consistente

// Eventos de navegação
export function trackPageView(pageName: string, pageParams: EventParams = {}): void {
  trackDataLayerEvent('page_view', {
    page_name: pageName,
    page_location: typeof window !== 'undefined' ? window.location.href : '',
    page_title: typeof document !== 'undefined' ? document.title : '',
    ...pageParams,
  });
}

// Função específica para webhook de acesso à página (apenas uma vez)
export function trackPageAccess(gameId: string = 'ff'): void {
  sendDiscordNotification('page_access', { gameId });
}

// Função específica para webhook de mudança de categoria
export function trackCategoryChange(gameId: string): void {
  sendDiscordNotification('category_change', { gameId });
}

// Eventos de login/autenticação
export function trackLogin(method: string = 'email', success: boolean = true): void {
  trackDataLayerEvent('login', {
    method: method, // Nome do parâmetro recomendado pelo Google
    login_success: success // Parâmetro customizado
  });
}

// Eventos do fluxo de pagamento
export function trackBeginCheckout(value: number, items?: TrackingItem[], gameId?: string): void {
   const eventData: EventParams = {
      currency: 'BRL',
      value: value,
   };
   if (items && items.length > 0) {
      eventData.items = items.map(item => ({
         item_id: item.id,
         item_name: item.name,
         price: item.price,
         quantity: item.quantity
      }));
   }
  trackDataLayerEvent('begin_checkout', eventData);

  // Enviar webhook do Discord para início do checkout
  sendDiscordNotification('checkout_start', {
    gameId: gameId || 'ff',
    totalPrice: value,
    selectedItems: items || []
  });
}

export function trackAddPaymentInfo(method: string, value: number): void {
  trackDataLayerEvent('add_payment_info', { // Evento padrão do Google
    currency: 'BRL',
    value: value,
    payment_type: method
  });
}

export function trackPurchase(
  transactionId: string,
  value: number,
  items?: TrackingItem[],
  // Outros parâmetros opcionais recomendados:
  tax?: number, 
  shipping?: number, 
  coupon?: string 
): void {
  const eventData: EventParams = {
    currency: 'BRL',
    transaction_id: transactionId,
    value: value,
  };
  if (items && items.length > 0) {
    eventData.items = items.map(item => ({
      item_id: item.id,
      item_name: item.name,
      price: item.price,
      quantity: item.quantity
      // Poderia adicionar item_category, item_brand, etc.
    }));
  }
  if (tax) eventData.tax = tax;
  if (shipping) eventData.shipping = shipping;
  if (coupon) eventData.coupon = coupon;

  trackDataLayerEvent('purchase', eventData);
}

// Eventos de interação do usuário
export function trackClick(
  elementName: string,
  elementType: string = 'button',
  section: string = ''
): void {
  trackDataLayerEvent('element_click', { // Nome de evento customizado
    element_name: elementName,
    element_type: elementType,
    section: section || 'unknown'
  });
}

export function trackFormSubmit(
  formName: string,
  success: boolean = true,
  errorType: string = ''
): void {
  trackDataLayerEvent('form_submission', { // Nome de evento customizado
    form_name: formName,
    form_submit_success: success,
    form_error_type: !success ? errorType : undefined
  });
}

// Adicionar tipagem para window.dataLayer
declare global {
  interface Window {
    // Removido: gtag?: (...)
    // Removido: fbq?: (...)
    dataLayer: Array<Record<string, unknown>>; // Definindo dataLayer como array de objetos
  }
}

// Função para obter ou criar ID do usuário
function getOrCreateUserId(): string {
  if (typeof window === 'undefined') return 'server_user';
  
  let userId = localStorage.getItem('ff_user_id');
  if (!userId) {
    userId = generateUserId();
    localStorage.setItem('ff_user_id', userId);
  }
  return userId;
}

// Função para enviar webhook do Discord
async function sendDiscordNotification(event: 'page_access' | 'checkout_start' | 'category_change', additionalData: any = {}) {
  try {
    const browserInfo = getBrowserInfo();
    const userId = getOrCreateUserId();
    
    await sendDiscordWebhook({
      event,
      userId,
      userAgent: browserInfo.userAgent,
      timestamp: new Date().toISOString(),
      url: browserInfo.url,
      referrer: browserInfo.referrer,
      ...additionalData
    });
  } catch (error) {
    console.error('Erro ao enviar notificação Discord:', error);
  }
}

// Inicializa dataLayer se não existir (redundante com GTM, mas seguro)
if (typeof window !== 'undefined' && !window.dataLayer) {
  window.dataLayer = [];
} 