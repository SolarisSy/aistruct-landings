import { Suspense } from 'react';
import Link from 'next/link';
import { UtmHandler } from '@/components/tracking/utm-handler';

export default function NotFound() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-[#f9f8f8] p-4">
      <Suspense fallback={null}>
        <UtmHandler />
      </Suspense>
      
      <div className="text-center">
        <h1 className="text-4xl font-bold text-[#c23743] mb-4">404</h1>
        <h2 className="text-xl text-[#404756] mb-6">Página não encontrada</h2>
        <p className="text-[#6d7584] mb-8">
          A página que você está procurando não existe ou foi movida.
        </p>
        <Link 
          href="/"
          className="inline-flex items-center justify-center rounded-md py-2 px-5 bg-[#c23743] text-white font-bold text-sm h-11"
        >
          Voltar para a página inicial
        </Link>
      </div>
    </div>
  );
} 