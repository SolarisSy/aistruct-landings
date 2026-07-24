import Image from "next/image";
import { ChevronUp } from "lucide-react";

// Interface para os itens passados
interface SelectedItem {
  id: string;
  name: string;
  price: number;
  image: string;
  type?: 'base' | 'offer' | 'bonus';
}

// Interface para os itens que serão enviados na URL
interface CheckoutItem {
  id: string;
  name: string;
  price: number;
  type: 'base' | 'offer' | 'bonus';
  image: string;
}

interface BottomPaymentBarProps {
  totalPrice: number;
  diamondsAmount: number;
  bonusDiamonds: number;
  selectedItems: SelectedItem[];
  baseDiamonds: number;
  basePrice: number;
  baseImage: string;
  onPaymentStart: () => void;
  isFreeItemClaimed?: boolean;
  accountName?: string;
}

export function BottomPaymentBar({
  totalPrice,
  diamondsAmount, // Este é o total (base + bônus talvez? Verificar uso)
  bonusDiamonds, // Bônus específico
  selectedItems,
  baseDiamonds, // Quantidade base
  basePrice, // Preço base
  baseImage, // Usar a nova prop
  onPaymentStart,
  isFreeItemClaimed,
  accountName
}: BottomPaymentBarProps) {
  const handleCheckout = () => {
    onPaymentStart();

    if (totalPrice === 0) return;

    const valorEmCentavos = Math.round(totalPrice * 100);
    const totalDiamantes = baseDiamonds + bonusDiamonds; // Calcula total de diamantes incluindo bônus

    // Função auxiliar para criar URL absoluta da imagem
    const getAbsoluteImageUrl = (imagePath: string): string => {
      if (typeof window !== 'undefined') { // Garante que está no lado do cliente
        if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
          return imagePath; // Já é uma URL absoluta
        }
        // Constrói URL absoluta. Remove a '/' inicial de imagePath se existir,
        // para evitar '//' na URL. window.location.origin já vem com a barra final.
        return `${window.location.origin}${imagePath.startsWith('/') ? imagePath : `/${imagePath}`}`;
      }
      // Fallback para SSR ou se window não estiver disponível (embora improvável para este componente)
      // Pode ser necessário um ENV var com a URL base do app aqui se for renderizado no servidor
      // e precisar da URL absoluta. Por ora, retorna o caminho como está.
      return imagePath; 
    };

    // 1. Criar o item base
    const baseItem: CheckoutItem = {
      id: 'base_diamonds',
      name: `${baseDiamonds.toLocaleString('pt-BR')} Diamantes`,
      price: basePrice,
      type: 'base',
      image: getAbsoluteImageUrl(baseImage),
    };

    // 2. Mapear os itens selecionados para adicionar o tipo e garantir que a imagem seja incluída
    const offerItems: CheckoutItem[] = selectedItems.map(item => ({
      id: item.id,
      name: item.name,
      price: item.price,
      image: getAbsoluteImageUrl(item.image),
      // O type será 'bonus' se veio do freeMagicCubeDisplayItem de UserCheckout,
      // ou 'offer' para os demais selectedOfferDetails que não têm 'type' explicitamente.
      type: item.type || 'offer',
    }));

    // 3. Combinar itens
    // A lista checkoutItems agora já conterá o Cubo Mágico (com type: 'bonus') 
    // se ele foi incluído em 'selectedItems' (vindo de 'displayItems' de UserCheckout) 
    // e isFreeItemAwarded/isFreeItemClaimed for true.
    const checkoutItems: CheckoutItem[] = [baseItem, ...offerItems];

    // 4. Serializar e codificar para URL
    let encodedItems = '';
    try {
      encodedItems = encodeURIComponent(JSON.stringify(checkoutItems));
    } catch (error) {
      console.error("[ff-new] Erro ao serializar itens para checkout:", error);
      // Considerar um fallback ou tratamento de erro
    }

    // Monta a URL com todos os parâmetros
    // Nota: A prop diamondsAmount original pode precisar ser recalculada ou removida se confusa
    const baseUrl = '/index.html';
    const params = new URLSearchParams();
    params.set('preco', totalPrice.toFixed(2));
    params.set('diamantes', totalDiamantes.toString());
    params.set('valor', valorEmCentavos.toString());
    params.set('items', encodedItems);

    if (accountName) {
      params.set('playerName', accountName);
    }

    const checkoutUrl = `${baseUrl}?${params.toString()}`;

    console.log("[ff-new] Redirecionando para Checkout com URL:", checkoutUrl);
    // console.log("[ff-new] Itens enviados:", JSON.stringify(checkoutItems)); // Log para depuração dos itens

    window.location.href = checkoutUrl;
  };

  return (
    <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg p-3 z-50 max-w-md mx-auto">
      <div className="flex items-center justify-between">
        <div className="flex flex-col">
          <div className="flex items-center gap-1 text-sm font-bold">
            <Image
              src="/images/credit_icon_large.png"
              alt="Diamante"
              width={16}
              height={16}
              className="object-contain"
              unoptimized
            />
            {/* Exibe a quantidade base + bônus */}
            <span>{(baseDiamonds).toLocaleString('pt-BR')}</span>
            <span className="whitespace-nowrap">+ {bonusDiamonds.toLocaleString('pt-BR')}</span>
            {/* Se houver itens selecionados, pode adicionar um indicador visual aqui */}
            {selectedItems.length > 0 && (
                 <span className="ml-1 text-xs font-normal text-gray-600">(+ {selectedItems.length} item{selectedItems.length > 1 ? 's' : ''})</span>
            )}
            {/* Manter botão de chevron? Sua função não está definida */}
            {/* <button className="rounded-full bg-gray-200 text-gray-600 w-5 h-5 flex items-center justify-center">
              <ChevronUp className="w-4 h-4" />
            </button> */}
          </div>
          <div className="mt-1 flex items-center gap-1 text-sm">
            <span className="font-medium">Total:</span>
            <span className="font-bold text-[#404756]">R$ {totalPrice.toFixed(2)}</span>
          </div>
        </div>

        <button
          onClick={handleCheckout}
          className="inline-flex items-center justify-center gap-1.5 rounded-md py-2 px-5 bg-[#c23743] text-white font-bold text-sm h-11"
        >
          <span className="mr-1">
            <svg width="18" height="18" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M54.125 34.1211C55.2966 32.9495 55.2966 31.05 54.125 29.8784C52.9534 28.7069 51.0539 28.7069 49.8823 29.8784L38.0037 41.7571L32.125 35.8784C30.9534 34.7069 29.0539 34.7069 27.8823 35.8784C26.7108 37.05 26.7108 38.9495 27.8823 40.1211L35.8823 48.1211C37.0539 49.2926 38.9534 49.2926 40.125 48.1211L54.125 34.1211Z" fill="currentColor" />
              <path fillRule="evenodd" clipRule="evenodd" d="M43.4187 3.4715C41.2965 2.28554 38.711 2.28554 36.5889 3.4715L8.07673 19.4055C6.19794 20.4555 4.97252 22.4636 5.02506 24.7075C5.36979 39.43 10.1986 63.724 37.0183 76.9041C38.8951 77.8264 41.1125 77.8264 42.9893 76.9041C69.809 63.724 74.6377 39.43 74.9825 24.7075C75.035 22.4636 73.8096 20.4555 71.9308 19.4055L43.4187 3.4715ZM39.5159 8.7091C39.8191 8.53968 40.1885 8.53968 40.4916 8.7091L68.9826 24.6313C68.6493 38.3453 64.2154 59.7875 40.343 71.5192C40.135 71.6214 39.8725 71.6214 39.6646 71.5192C15.7921 59.7875 11.3583 38.3453 11.025 24.6313L39.5159 8.7091Z" fill="currentColor" />
            </svg>
          </span>
          Compre agora
        </button>
      </div>
    </div>
  );
}
