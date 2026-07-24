import Link from 'next/link';
import React from 'react';

export function Footer() {
  return (
    <footer className="w-full max-w-md mx-auto px-4 py-3 text-center text-xs text-[#6d7584] bg-white border-t border-gray-200 flex flex-col items-center justify-center mt-auto">
      <div className="flex flex-wrap justify-center gap-x-4 gap-y-2 mb-2">
        <Link href="/legal/termos" className="hover:underline">Termos e Condições</Link>
        <Link href="/legal/privacidade" className="hover:underline">Política de Privacidade</Link>
      </div>
    </footer>
  );
}
