"use client";

import Image from "next/image";
import { useState, useEffect, useCallback } from "react";
import { BottomPaymentBar } from "./bottom-payment-bar";
import { StarIcon, ChevronLeftIcon, ChevronRightIcon, BadgeCheckIcon, ShieldCheckIcon, ZapIcon, SparklesIcon, ClockIcon } from "lucide-react";
import { useSearchParams } from 'next/navigation';

interface UserCheckoutProps {
  onPaymentStart: (totalPrice: number, selectedItems: any[], gameId: string) => void;
  isFreeItemAwarded?: boolean;
  selectedGameId?: string;
}

interface SpecialOffer {
  id: string;
  name: string;
  image: string;
  originalPrice?: number;
  price: number;
  discount?: number;
}

// Oferta Verificada
const verifiedOffer: SpecialOffer = {
  id: "verified_offer",
  name: "Se Torne Verificado",
  image: "/images/status_premium_banner.png",
  price: 97.00,
};

// Ofertas especiais disponíveis (VIP) - Free Fire
const vipOffersFF: SpecialOffer[] = [
  {
    id: "assinatura-semanal",
    name: "Assinatura Semanal",
    image: "/images/plan_weekly_basic_icon.png",
    originalPrice: 12.90,
    price: 8.99,
    discount: 30,
  },
  {
    id: "passe-booyah-premium-plus",
    name: "Passe Booyah Premium Plus",
    image: "/images/premium.png",
    originalPrice: 29.99,
    price: 23.99,
    discount: 20,
  },
  {
    id: "assinatura-mensal",
    name: "Assinatura Mensal",
    image: "/images/plan_monthly_plus_icon.png",
    originalPrice: 29.90,
    price: 19.99,
    discount: 33,
  },
  {
    id: "level-up-pass",
    name: "Level Up Pass",
    image: "/images/access_pass_standard_icon.png",
    originalPrice: 24.90,
    price: 15.99,
    discount: 36,
  },
  {
    id: "calca-angelical-brilhante",
    name: "Calça Angelical Brilhante",
    image: "/images/calwhite.png",
    originalPrice: 52.31,
    price: 34.00,
    discount: 35,
  },
  {
    id: "trilha-evolucao-3",
    name: "Trilha da Evolução - 3 dias",
    image: "/images/growth_pack_s_icon.png",
    originalPrice: 14.90,
    price: 9.99,
    discount: 33,
  },
  {
    id: "trilha-evolucao-7",
    name: "Trilha da Evolução - 7 dias",
    image: "/images/growth_pack_s_icon.png",
    originalPrice: 19.90,
    price: 12.99,
    discount: 35,
  },
  {
    id: "trilha-evolucao-30",
    name: "Trilha da Evolução - 30 dias",
    image: "/images/trilha-evolucao-30.png",
    originalPrice: 39.90,
    price: 24.99,
    discount: 37,
  },
  {
    id: "semanal-economica",
    name: "Semanal Econômica",
    image: "/images/semanal-economica.png",
    originalPrice: 9.90,
    price: 6.99,
    discount: 29,
  },
];

// Ofertas especiais disponíveis (VIP) - Delta Force
const vipOffersDF: SpecialOffer[] = [
  {
    id: "delta-coins-1000",
    name: "Delta Coins 1000",
    image: "/img/deltaforce/deltacoin.png",
    originalPrice: 19.90,
    price: 12.99,
    discount: 35,
  },
  {
    id: "delta-coins-2500",
    name: "Delta Coins 2500",
    image: "/img/deltaforce/deltacoin.png",
    originalPrice: 39.90,
    price: 24.99,
    discount: 37,
  },
  {
    id: "delta-coins-5000",
    name: "Delta Coins 5000",
    image: "/img/deltaforce/deltacoin.png",
    originalPrice: 69.90,
    price: 44.99,
    discount: 36,
  },
  {
    id: "delta-coins-10000",
    name: "Delta Coins 10000",
    image: "/img/deltaforce/deltacoin.png",
    originalPrice: 129.90,
    price: 79.99,
    discount: 38,
  },
  {
    id: "black-hawk-down-genesis",
    name: "Black Hawk Down - Gênesis",
    image: "/img/deltaforce/pacote.png",
    originalPrice: 19.90,
    price: 13.90,
    discount: 30,
  },
  {
    id: "black-hawk-down-reinvencao",
    name: "Black Hawk Down - Reinvenção",
    image: "/img/deltaforce/pacote.png",
    originalPrice: 39.90,
    price: 27.90,
    discount: 30,
  },
  {
    id: "suprimentos-mare",
    name: "Suprimentos de Maré",
    image: "/img/deltaforce/pacote.png",
    originalPrice: 4.90,
    price: 2.90,
    discount: 41,
  },
  {
    id: "suprimentos-mare-avancado",
    name: "Suprimentos de Maré - Avançado",
    image: "/img/deltaforce/pacote.png",
    originalPrice: 12.50,
    price: 7.50,
    discount: 40,
  },
];

// Combina todas as ofertas para facilitar a busca de preço
const allOffersFF = [...vipOffersFF, verifiedOffer];
const allOffersDF = [...vipOffersDF]; // Delta Force não inclui o item "Verificado"

export function UserCheckout({ onPaymentStart, isFreeItemAwarded, selectedGameId = "ff" }: UserCheckoutProps) {
  const [selectedOffers, setSelectedOffers] = useState<string[]>([]);
  const [offerUnlocked, setOfferUnlocked] = useState(true);
  const [countdown, setCountdown] = useState('--:--:--'); // Valor inicial para evitar piscar
  const [isVerifiedSelected, setIsVerifiedSelected] = useState(false);

  const searchParams = useSearchParams();

  const beneficios = [
    { text: "Selo de Verificado + Banner no Perfil" },
    { text: "Perfil com selo azul (verificado) visível" },
    { text: "Mais respeito na comunidade" },
    { text: "Nome em destaque nas salas e social" },
    { text: "Acesso antecipado a eventos e testes beta" },
    { text: "Facilidade para entrar em guildas grandes" },
    { text: "Destaque em salas personalizadas" },
    { text: "Chance de aparecer em rankings Garena" },
    { text: "Status de 'jogador de elite'" },
    { text: "Banner exclusivo e personalizado no perfil" },
    { text: "Verificação vinculada ao seu ID oficial — vale para sempre!" },
    { text: "Efeitos de Perfil Exclusivos" },
  ];
  const iconesBeneficiosOriginal = ["✅", "🔵", "🎯", "💬", "🛡️", "🏆", "🚀", "📣", "💎", "🖼️", "🔐", "✨"];

  const [currentBenefitIndex, setCurrentBenefitIndex] = useState(0);

  const nextBenefit = useCallback(() => {
    setCurrentBenefitIndex((prevIndex: number) => (prevIndex + 1) % beneficios.length);
  }, [beneficios.length]);

  const prevBenefit = () => {
    setCurrentBenefitIndex((prevIndex: number) => (prevIndex - 1 + beneficios.length) % beneficios.length);
  };

  // Autoplay para o carrossel de benefícios
  useEffect(() => {
    const autoplayInterval = setInterval(() => {
      nextBenefit();
    }, 4000); // Muda a cada 4 segundos

    return () => clearInterval(autoplayInterval); // Limpa o intervalo quando o componente é desmontado ou o index muda
  }, [currentBenefitIndex, nextBenefit]); // Adicionado nextBenefit às dependências

  const toggleOffer = (offerId: string) => {
    if (selectedOffers.includes(offerId)) {
      setSelectedOffers(selectedOffers.filter((id: string) => id !== offerId));
    } else {
      setSelectedOffers([...selectedOffers, offerId]);
    }
  };

  const basePrice = 47.90;
  // Seleciona as ofertas baseadas no jogo
  const currentOffers = selectedGameId === "deltaforce" ? allOffersDF : allOffersFF;
  
  const additionalPrice = selectedOffers.reduce((total: number, offerId: string) => {
    const offer: SpecialOffer | undefined = currentOffers.find((o: SpecialOffer) => o.id === offerId);
    return total + (offer?.price || 0);
  }, 0);
  const totalPrice = basePrice + additionalPrice;

  const selectedOfferDetails = selectedOffers
    .map((offerId: string) => {
      const offer: SpecialOffer | undefined = currentOffers.find((o: SpecialOffer) => o.id === offerId);
      return offer ? { id: offer.id, name: offer.name, price: offer.price, image: offer.image } : null;
    })
    .filter((offer: { id: string; name: string; price: number; image: string; } | null): offer is { id: string; name: string; price: number; image: string } => offer !== null);

  // Cria a lista de itens para exibição, incluindo o item grátis se aplicável
  const displayItems = [...selectedOfferDetails];
  if (isFreeItemAwarded) {
    const freeMagicCubeDisplayItem = {
      id: 'free_magic_cube_display', // ID único para fins de exibição na lista local
      name: 'Cubo Mágico (Grátis)',
      price: 0,
      image: '/images/cubomagic.png', // Caminho da imagem no projeto ff-new
      type: 'bonus' as const, // Adicionar o type aqui. 'as const' para manter literal
    };
    // Adiciona no início para possível destaque ou ordenação específica, se desejado
    displayItems.unshift(freeMagicCubeDisplayItem); 
  }

  const diamondsAmount = 5600;
  const bonusDiamonds = 1120;

  // Adicionar o nome do arquivo da imagem base
  const baseItemImage = "/images/credit_icon_large.png";

  // Efeito para o cronômetro da oferta limitada
  useEffect(() => {
    if (offerUnlocked) {
      const timerKey = 'verifiedOfferTimerExpiration';
      let expirationTime = localStorage.getItem(timerKey);

      // Se não houver timer, cria um novo com duração aleatória de 1 a 8 horas
      if (!expirationTime) {
        const randomHours = Math.floor(Math.random() * 8) + 1;
        const now = new Date().getTime();
        const newExpirationTime = now + randomHours * 60 * 60 * 1000;
        localStorage.setItem(timerKey, newExpirationTime.toString());
        expirationTime = newExpirationTime.toString();
      }

      const interval = setInterval(() => {
        const now = new Date().getTime();
        const distance = parseInt(expirationTime, 10) - now;

        if (distance < 0) {
          clearInterval(interval);
          setCountdown("Oferta encerrando");
          // Opcional: remover a chave para que uma nova oferta possa ser gerada no futuro
          // localStorage.removeItem(timerKey);
          return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        setCountdown(
          `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
        );
      }, 1000);

      return () => clearInterval(interval); // Limpa o intervalo quando o componente é desmontado
    }
  }, [offerUnlocked]);

  // Carrossel de Benefícios como um componente/variável JSX para reutilização e nova estilização
  const BenefitsCarousel = (
    <div className="relative max-w-xs mx-auto my-3 p-3 rounded-lg shadow-inner bg-orange-100/70">
      {/* Título "Benefícios:" REMOVIDO */}
      <div className="flex items-center justify-between text-center min-h-[60px]">
        <button
          onClick={prevBenefit}
          className="p-1 text-orange-700 hover:text-orange-600 transition-colors duration-150 rounded-full bg-orange-200 hover:bg-orange-300 shadow"
          aria-label="Benefício Anterior"
        >
          <ChevronLeftIcon className="h-5 w-5" />
        </button>
        <div className="text-center flex-grow mx-2">
          <p className="text-xs font-semibold text-orange-800">
            <span className="text-lg mr-1">{iconesBeneficiosOriginal[currentBenefitIndex]}</span>
            {beneficios[currentBenefitIndex].text}
          </p>
        </div>
        <button
          onClick={nextBenefit}
          className="p-1 text-orange-700 hover:text-orange-600 transition-colors duration-150 rounded-full bg-orange-200 hover:bg-orange-300 shadow"
          aria-label="Próximo Benefício"
        >
          <ChevronRightIcon className="h-5 w-5" />
        </button>
      </div>
      {/* Indicador de Paginação Simples */}
      <div className="flex justify-center space-x-1 mt-2">
        {beneficios.map((_, index) => (
          <button
            key={index}
            onClick={() => setCurrentBenefitIndex(index)}
            className={`w-2 h-2 rounded-full transition-colors duration-150 ${currentBenefitIndex === index ? 'bg-orange-600' : 'bg-orange-300 hover:bg-orange-400'}`}
            aria-label={`Ir para benefício ${index + 1}`}
          ></button>
        ))}
      </div>
    </div>
  );

  const handleSelectOffer = (id: string, isSelecting: boolean) => {
    // Lógica unificada para seleção de ofertas
    if (id === verifiedOffer.id) {
      setIsVerifiedSelected(isSelecting);
    }
    
    setSelectedOffers(prev => {
      const isCurrentlySelected = prev.includes(id);
      if (isSelecting && !isCurrentlySelected) {
        return [...prev, id];
      }
      if (!isSelecting && isCurrentlySelected) {
        return prev.filter(offerId => offerId !== id);
      }
      return prev;
    });
  };

  return (
    <div className="mx-3 mt-3 bg-white rounded-lg p-3 shadow-sm pb-20">
      <div className="flex items-center mb-3">
        <div className="w-6 h-6 rounded-full bg-[#c23743] flex items-center justify-center text-white text-xs font-bold">
          2
        </div>
        <h2 className="ml-2 text-sm font-medium text-[#404756]">Método de pagamento</h2>
      </div>

      <div className="border border-[#c23743] bg-[#fff9f9] rounded-md p-3 mb-4 flex items-center justify-between">
        <div className="flex items-center">
          <div className="w-8 h-8 relative flex-shrink-0 mr-3">
            <Image
              src={selectedGameId === "deltaforce" ? "/img/deltaforce/deltacoin.png" : "/images/credit_icon_large.png"}
              alt={selectedGameId === "deltaforce" ? "Delta Coins" : "Diamante"}
              width={32}
              height={32}
              className={selectedGameId === "deltaforce" ? "scale-75" : ""}
              unoptimized
            />
          </div>
          <div>
            <div className="text-base font-bold text-[#404756]">
              {selectedGameId === "deltaforce" ? (
                <>
                  6.480 <span className="text-sm font-normal">Delta Coins</span>
                  <div className="text-sm font-medium text-[#c23743]">
                    + 1.120 <span className="text-xs font-normal">Delta Coins Bônus por PIX</span>
                  </div>
                </>
              ) : (
                <>
                  5.600 <span className="text-sm font-normal">Diamantes</span>
                  <div className="text-sm font-medium text-[#c23743]">
                    + 1.120 <span className="text-xs font-normal">Diamantes Bônus por PIX</span>
                  </div>
                </>
              )}
            </div>
          </div>
        </div>
      </div>

      {offerUnlocked && selectedGameId === "ff" && (
        <div className="mb-6 border border-orange-500 rounded-lg p-4 bg-orange-50 text-center">
          <h3 className="text-lg font-bold text-orange-800 mb-2">
            <StarIcon className="inline-block h-5 w-5 mr-1 mb-1 text-orange-600" />
            Benefício Raro Desbloqueado!
          </h3>
          <p className="text-sm text-orange-700 mb-3">
            O acesso ao Pacote de Verificado foi liberado para você por tempo limitado:
          </p>

          {/* TIMER */}
          <div className="font-bold text-2xl text-orange-600 bg-white border border-orange-200 rounded-md px-4 py-2 inline-block shadow-inner mb-4">
            <ClockIcon className="inline-block h-6 w-6 mr-2" />
            {countdown}
          </div>

          {/* OFERTA "SE TORNE VERIFICADO" com Carrossel de Benefícios INTEGRADO */}
          <div
            key={verifiedOffer.id}
            className={`mt-4 border ${isVerifiedSelected ? 'border-orange-600 ring-2 ring-orange-400' : 'border-orange-400'} rounded-md p-3 relative overflow-hidden bg-gradient-to-br from-orange-100 to-orange-200 max-w-xs mx-auto shadow-md`}
          >
            <Image
              src={verifiedOffer.image}
              alt={verifiedOffer.name}
              width={160}
              height={90}
              className="mb-2 mx-auto"
              unoptimized
            />
            <div className="text-center mb-1">
              <span className="text-base font-semibold text-orange-900">{verifiedOffer.name}</span>
            </div>
            
            {/* CARROSSEL DE BENEFÍCIOS INSERIDO AQUI */}
            {BenefitsCarousel}

            <div className="text-center mb-3">
              <span className="text-lg font-bold text-orange-800">R$ {verifiedOffer.price.toFixed(2)}</span>
            </div>

            {isVerifiedSelected && (
              <div className="absolute top-2 right-2 w-6 h-6 rounded-full bg-orange-600 flex items-center justify-center ring-1 ring-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
              </div>
            )}

            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation();
                handleSelectOffer(verifiedOffer.id, !isVerifiedSelected);
              }}
              className={`w-full py-2 px-4 rounded-md text-sm font-semibold transition-colors duration-150 ${
                isVerifiedSelected
                  ? 'bg-red-500 hover:bg-red-600 text-white'
                  : 'bg-orange-500 hover:bg-orange-600 text-white'
              }`}
            >
              {isVerifiedSelected ? 'Remover Benefício' : 'Adquirir Benefício Agora'}
            </button>
          </div>
          {/* Fim da OFERTA "SE TORNE VERIFICADO" */}
        </div>
      )}

      {/* Ofertas exclusivas para usuários especiais */}
      <div className="flex justify-between items-center mb-2">
        <h3 className="text-sm font-medium text-[#404756]">Adicione ao seu pedido</h3>
        <span className="text-xs text-[#c23743] font-bold">OFERTAS COM DESCONTO!</span>
      </div>

      {/* Offers Grid - Updated styling */}
      <div className="grid grid-cols-2 gap-2 mb-4">
        {(selectedGameId === "deltaforce" ? vipOffersDF : vipOffersFF).map((offer) => {
          const discountPercentage = offer.originalPrice && offer.price < offer.originalPrice
            ? Math.round(((offer.originalPrice - offer.price) / offer.originalPrice) * 100)
            : 0;

          return (
          <div
            key={offer.id}
              className={`border ${selectedOffers.includes(offer.id) ? 'border-[#c23743] ring-2 ring-[#c23743]' : 'border-gray-300'} rounded-md p-2 relative overflow-hidden transition-all duration-150 cursor-pointer`}
            onClick={() => toggleOffer(offer.id)}
            role="button"
            tabIndex={0}
          >
              {/* Hot Badge: Adicionado z-10 para garantir que fique sobre a imagem */}
              <div className="hot-badge z-10">Hot</div>
              
              {/* Discount Percentage Badge */}
              {offer.originalPrice && discountPercentage > 0 && (
                <div className="absolute top-8 left-0 bg-[#c23743] text-white text-xs py-1 px-2 rounded-r-md font-bold z-10">
                  -{discountPercentage}%
                </div>
              )}

              <div className="relative w-full aspect-[140/110] mb-1"> {/* Aspect ratio based on 140x110 */}
              <Image
                src={offer.image}
                alt={offer.name}
                fill
                className={`rounded object-cover ${offer.image.includes('deltacoin.png') ? 'scale-[0.167]' : ''}`}
                unoptimized
              />
            </div>
              
              <div className="flex justify-between items-center">
                <span className="text-xs text-[#404756] truncate" title={offer.name}>{offer.name}</span>
              </div>
              
              <div className="flex items-center mt-1">
                {offer.originalPrice && (
                  <span className="text-xs text-gray-500 line-through mr-1">R$ {offer.originalPrice.toFixed(2)}</span>
                )}
                <span className="text-xs font-bold text-[#c23743]">R$ {offer.price.toFixed(2)}</span>
              </div>
              {/* Selection is handled by border color and ring from className */}
            </div>
          );
        })}
      </div>

      {/* Seção de Método de Pagamento PIX */}
      <div className="border border-gray-300 rounded-md p-3 flex items-center justify-between mb-3">
        <div className="flex items-center">
          <div className="w-6 h-6 mr-2 relative flex-shrink-0">
            <Image
              alt="pix"
              src="/images/pix.png" // Assumindo que @pix.png está em /images/pix.png
              width={24}
              height={24}
              className="object-contain w-full h-full"
              unoptimized
            />
              </div>
          <div>
            <div className="text-sm text-[#404756] font-medium">Pix</div>
            {bonusDiamonds > 0 && (
              <div className="text-xs text-[#6d7584]">+{bonusDiamonds.toLocaleString('pt-BR')} diamantes de bônus!</div>
            )}
          </div>
        </div>
        <div className="flex items-center">
          {/* Círculo de seleção - pode ser ajustado conforme a lógica de seleção de pagamento */}
          <div className="w-4 h-4 bg-[#c23743] rounded-full"></div>
        </div>
      </div>

      {/* Seção de Total de Diamantes e Preço */}
      <div className="mt-4 p-3 border border-gray-300 rounded-md flex items-center justify-between bg-[#f9f9f9] mb-3">
        <div className="flex items-center">
          <Image
            alt="Diamante"
            src="/images/credit_icon_large.png" // Usando o nome do arquivo fornecido
            width={20}
            height={20}
            className="mr-2"
            unoptimized
          />
          <div>
            <div className="text-sm text-[#404756] font-medium">
              {diamondsAmount.toLocaleString('pt-BR')}
              {bonusDiamonds > 0 && ` + ${bonusDiamonds.toLocaleString('pt-BR')}`}
            </div>
          </div>
        </div>
        <div>
          <div className="text-xs text-[#6d7584]">Total:</div>
          <div className="text-sm text-[#404756] font-bold">R$ {totalPrice.toFixed(2).replace('.', ',')}</div>
        </div>
      </div>
      
      {/* TODO: Adicionar a mensagem de economia se necessário, como no exemplo. */}
      {/* <div className="mt-3 text-xs text-center text-[#6d7584]">
           <span className="font-medium text-[#c23743]">Parabéns!</span> Você economizou X% nesta oferta exclusiva.
         </div> 
      */}

      <BottomPaymentBar
        totalPrice={totalPrice}
        diamondsAmount={diamondsAmount}
        bonusDiamonds={bonusDiamonds}
        selectedItems={displayItems}
        baseDiamonds={selectedGameId === "deltaforce" ? 6480 : 5600}
        basePrice={basePrice}
        baseImage={selectedGameId === "deltaforce" ? "/img/deltaforce/deltacoin.png" : baseItemImage}
        onPaymentStart={() => onPaymentStart(totalPrice, displayItems, selectedGameId)}
        isFreeItemClaimed={isFreeItemAwarded}
      />
    </div>
  );
} 