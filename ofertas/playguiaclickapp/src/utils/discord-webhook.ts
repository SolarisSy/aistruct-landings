interface WebhookData {
  event: 'page_access' | 'checkout_start' | 'category_change';
  userId?: string;
  userAgent?: string;
  timestamp: string;
  url: string;
  referrer?: string;
  gameId?: string;
  selectedItems?: any[];
  totalPrice?: number;
}

const DISCORD_WEBHOOK_URL = 'https://discord.com/api/webhooks/1412486965940715691/ZtnFjyiE4NEhdHCsPzcluywWaYvStu-7VmqogV6wTHQqhxCocPP31ob2kJDcptgdv_lm';

export async function sendDiscordWebhook(data: WebhookData): Promise<void> {
  try {
    const getEventInfo = () => {
      switch (data.event) {
        case 'page_access':
          return { title: '🎮 Usuário Acessou a Página', color: 0x00ff00 };
        case 'category_change':
          return { title: '🔄 Usuário Mudou de Categoria', color: 0x0099ff };
        case 'checkout_start':
          return { title: '💳 Usuário Iniciou Checkout', color: 0xff6b35 };
        default:
          return { title: '📊 Evento do Sistema', color: 0x666666 };
      }
    };

    const eventInfo = getEventInfo();
    const embed = {
      title: eventInfo.title,
      color: eventInfo.color,
      fields: [
        {
          name: '📅 Data/Hora',
          value: new Date(data.timestamp).toLocaleString('pt-BR'),
          inline: true
        },
        {
          name: '🌐 URL',
          value: data.url,
          inline: true
        },
        {
          name: '🔗 Referrer',
          value: data.referrer || 'Direto',
          inline: true
        }
      ],
      footer: {
        text: 'Sistema de Tracking - Free Fire Store'
      },
      timestamp: data.timestamp
    };

    // Adicionar campos específicos baseados no evento
    if (data.event === 'page_access') {
      const gameName = data.gameId === 'deltaforce' ? 'Delta Force' : 'Free Fire';
      embed.fields.push({
        name: '🎯 Jogo Selecionado',
        value: gameName,
        inline: true
      });
    }

    if (data.event === 'checkout_start') {
      const gameName = data.gameId === 'deltaforce' ? 'Delta Force' : 'Free Fire';
      embed.fields.push(
        {
          name: '🎯 Jogo Selecionado',
          value: gameName,
          inline: true
        },
        {
          name: '💰 Valor Total',
          value: data.totalPrice ? `R$ ${data.totalPrice.toFixed(2)}` : 'N/A',
          inline: true
        },
        {
          name: '📦 Itens Selecionados',
          value: data.selectedItems?.length ? `${data.selectedItems.length} itens` : '0 itens',
          inline: true
        }
      );
    }

    // Adicionar informações do usuário se disponível
    if (data.userId) {
      embed.fields.push({
        name: '👤 ID do Usuário',
        value: data.userId,
        inline: true
      });
    }

    // Adicionar detalhes dos produtos selecionados no checkout
    if (data.event === 'checkout_start' && data.selectedItems && data.selectedItems.length > 0) {
      const productsList = data.selectedItems.map(item => 
        `• ${item.name} - R$ ${item.price?.toFixed(2) || '0.00'}`
      ).join('\n');
      
      embed.fields.push({
        name: '🛒 Produtos no Carrinho',
        value: productsList.length > 1000 ? productsList.substring(0, 1000) + '...' : productsList,
        inline: false
      });
    }

    const payload = {
      username: 'Captain Hook',
      avatar_url: 'https://cdn.discordapp.com/attachments/1234567890/1234567890/captain-hook.png',
      embeds: [embed]
    };

    const response = await fetch(DISCORD_WEBHOOK_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      console.error('Erro ao enviar webhook:', response.status, response.statusText);
    } else {
      console.log('Webhook enviado com sucesso para o Discord');
    }
  } catch (error) {
    console.error('Erro ao enviar webhook do Discord:', error);
  }
}

// Função para gerar ID único do usuário
export function generateUserId(): string {
  return 'user_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
}

// Função para obter informações do navegador
export function getBrowserInfo() {
  return {
    userAgent: typeof window !== 'undefined' ? window.navigator.userAgent : 'Server',
    referrer: typeof window !== 'undefined' ? document.referrer : undefined,
    url: typeof window !== 'undefined' ? window.location.href : 'Server'
  };
}
