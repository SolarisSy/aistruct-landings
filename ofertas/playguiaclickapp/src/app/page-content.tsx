"use client";

import { useState, useEffect } from "react";
import { useRouter, useSearchParams } from 'next/navigation';
import Image from 'next/image';
import { Header } from "@/components/ui/header";
import { Banner } from "@/components/ui/banner";
import { ItemSelection } from "@/components/ui/item-selection";
import { ItemCard } from "@/components/ui/item-card";
import { BonusItem } from "@/components/ui/bonus-item";
import { LoginForm } from "@/components/ui/login-form";
import { Footer } from "@/components/ui/footer";
import { ConsentBanner } from "@/components/ui/consent-banner";
import { UserCheckout } from "@/components/ui/user-checkout";
import { 
  trackPageView, 
  trackLogin, 
  trackClick,
  trackBeginCheckout,
  trackPageAccess,
  trackCategoryChange,
} from "@/components/tracking/event-tracker";
import { GiftIcon } from 'lucide-react';

interface UserData {
  playerUid: string;
  region: string;
  accountName: string;
  accountId: string;
}

export default function PageContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userData, setUserData] = useState<UserData | null>(null);
  const [consentBannerVisible, setConsentBannerVisible] = useState(true);
  const [showFreeItemModal, setShowFreeItemModal] = useState(false);
  const [triggerLoginHighlight, setTriggerLoginHighlight] = useState(false);
  const [userAwardedFreeItem, setUserAwardedFreeItem] = useState(false);
  const [selectedGameId, setSelectedGameId] = useState("ff");
  const [hasTrackedPageAccess, setHasTrackedPageAccess] = useState(false);
  const [lastTrackedGameId, setLastTrackedGameId] = useState<string | null>(null);

  useEffect(() => {
    trackPageView('main_view', { gameId: selectedGameId });

    // Enviar webhook apenas no primeiro acesso à página
    if (!hasTrackedPageAccess) {
      trackPageAccess(selectedGameId);
      setHasTrackedPageAccess(true);
      setLastTrackedGameId(selectedGameId); // Inicializar o último jogo rastreado
    }

    const paramPlayerUid = searchParams ? searchParams.get('playerUid') : null;
    const paramRegion = searchParams ? searchParams.get('region') : null;
    const paramAccountName = searchParams ? searchParams.get('accountName') : null;
    const paramAccountId = searchParams ? searchParams.get('accountId') : null;

    if (paramPlayerUid && paramRegion) {
      console.log('Dados recebidos via URL:', { paramPlayerUid, paramRegion, paramAccountName, paramAccountId });
      setIsLoggedIn(true);
      const receivedUserData: UserData = {
        playerUid: paramPlayerUid,
        region: paramRegion,
        accountName: paramAccountName || 'Jogador',
        accountId: paramAccountId || paramPlayerUid
      };
      setUserData(receivedUserData);
      setConsentBannerVisible(false);
      if (typeof window !== 'undefined') {
        localStorage.setItem('ffUserLoggedIn', 'true');
        localStorage.setItem('ffUserData', JSON.stringify(receivedUserData));
      }
      window.history.replaceState({}, document.title, window.location.pathname);

    } else if (typeof window !== 'undefined') {
      const storedLoginStatus = localStorage.getItem('ffUserLoggedIn');
      const storedUserData = localStorage.getItem('ffUserData');
      if (storedLoginStatus === 'true') {
        setIsLoggedIn(true);
        if (storedUserData) {
            setUserData(JSON.parse(storedUserData));
        }
        setConsentBannerVisible(false);
      }
    }
  }, [searchParams, router]);

  const handleLogin = () => {
    setIsLoggedIn(true);
    trackLogin('user_auth', true);
    setConsentBannerVisible(false);
    if (typeof window !== 'undefined') {
      localStorage.setItem('ffUserLoggedIn', 'true');
    }
  };

  const handleCloseConsentBanner = () => {
    setConsentBannerVisible(false);
    trackClick('accept_consent', 'button', 'consent_banner');
  };

  const handleItemSelection = (itemName: string, itemId: string) => {
    trackClick(`select_item_${itemName}`, 'button', 'item_selection');
    setSelectedGameId(itemId);
  };

  // Enviar webhook quando o jogo for alterado (apenas se já tiver acessado a página e for uma mudança real)
  useEffect(() => {
    if (hasTrackedPageAccess && selectedGameId && lastTrackedGameId !== selectedGameId) {
      trackCategoryChange(selectedGameId);
      setLastTrackedGameId(selectedGameId);
    }
  }, [selectedGameId, hasTrackedPageAccess, lastTrackedGameId]);

  const handleResgatarItemClick = () => {
    if (!isLoggedIn) {
      console.log("Usuário precisa fazer login para resgatar.");
      setTriggerLoginHighlight(true);
      setTimeout(() => setTriggerLoginHighlight(false), 1000);
    } else {
      setShowFreeItemModal(true);
      trackClick('click_resgatar_item_gratis', 'button', 'secao_item_gratis');
    }
  };

  const FreeItemSection = (
    <div className="my-6 mx-3 p-4 bg-white rounded-lg shadow-md">
      <div className="relative flex h-full w-full justify-between items-center">
        <div className="flex flex-col items-start justify-center">
          <div className="mb-0.5 text-base font-bold text-gray-800">Item Grátis</div>
          <div className="mb-3 text-xs text-gray-600">Resgate aqui seu item exclusivo grátis</div>
          <button 
            onClick={handleResgatarItemClick}
            className="min-w-[80px] inline-flex items-center justify-center gap-1.5 rounded-md border py-1 text-center leading-none transition-colors border-orange-500 bg-orange-500 text-white hover:bg-orange-600 hover:border-orange-600 px-3 text-xs font-medium h-7"
          >
            {isLoggedIn ? 'Resgatar Agora' : 'Login para Resgatar'}
          </button>
        </div>
        <div className="flex flex-col items-center justify-center ml-3">
          <div className="mb-2 flex h-[60px] w-[60px] items-center justify-center overflow-hidden rounded-xl border border-gray-300 bg-gray-100">
            <Image 
              className="pointer-events-none h-full w-full object-contain"
              src="/images/cubomagic.png"
              alt="Cubo Mágico"
              width={60}
              height={60}
              unoptimized
            />
          </div>
          <div className="flex items-center text-xs text-center max-w-[100px] text-gray-700">
            <div className="truncate font-medium">Cubo Mágico</div>
          </div>
        </div>
      </div>
    </div>
  );

  return (
    <main className="bg-[#f9f8f8] min-h-screen pb-[160px] md:pb-[240px]">
      <Header />
      <Banner />
      <ItemSelection onItemSelect={handleItemSelection} selectedGameId={selectedGameId} />
      <ItemCard selectedGameId={selectedGameId} />
      
      {selectedGameId === "ff" && FreeItemSection}

      <BonusItem selectedGameId={selectedGameId} />

      {!isLoggedIn ? (
        <LoginForm onLogin={handleLogin} triggerHighlight={triggerLoginHighlight} />
      ) : (
        <UserCheckout 
          onPaymentStart={(totalPrice, selectedItems, gameId) => {
            trackClick('initiate_checkout', 'button', 'checkout_process');
            trackBeginCheckout(totalPrice, selectedItems, gameId);
          }} 
          isFreeItemAwarded={userAwardedFreeItem}
          selectedGameId={selectedGameId}
        />
      )}

      <Footer />

      {consentBannerVisible && (
        <ConsentBanner onClose={handleCloseConsentBanner} />
      )}

      {showFreeItemModal && (
        <div className="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
          <div className="flex w-full max-w-sm flex-col items-center justify-center rounded-lg bg-white p-6 text-center shadow-xl">
            <GiftIcon className="mb-4 h-14 w-14 text-orange-500" />
            <div className="mb-3 text-base font-bold text-gray-800">Quase lá para o seu Item Grátis!</div>
            <div className="text-sm text-gray-700">
              Para receber o seu <span className="font-semibold text-gray-800">Cubo Mágico</span>, finalize sua compra e ele será adicionado automaticamente junto com seus diamantes!
            </div>
            <button 
              onClick={() => {
                setShowFreeItemModal(false);
                setUserAwardedFreeItem(true);
                trackClick('click_ok_modal_item_gratis', 'button', 'modal_item_gratis');
              }}
              className="mt-5 w-full inline-flex items-center justify-center gap-1.5 rounded-md border py-1 text-center leading-none transition-colors border-orange-500 bg-orange-500 text-white hover:bg-orange-600 hover:border-orange-600 px-5 text-sm font-bold h-10"
            >
              OK
            </button>
          </div>
        </div>
      )}
    </main>
  );
} 