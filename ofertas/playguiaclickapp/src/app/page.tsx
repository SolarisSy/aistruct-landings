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
} from "@/components/tracking/event-tracker";
import { GiftIcon } from 'lucide-react';
import { Suspense } from 'react';
import PageContent from './page-content';

interface UserData {
  playerUid: string;
  region: string;
  accountName: string;
  accountId: string;
}

export default function Page() {
  return (
    <Suspense fallback={<div>Carregando...</div>}>
      <PageContent />
    </Suspense>
  );
}
