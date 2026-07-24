"use client";

import Image from "next/image";
import { CircleUser, X } from "lucide-react";
import { useRouter } from "next/navigation";

interface HeaderProps {
  showCloseButton?: boolean;
}

export function Header({ showCloseButton = false }: HeaderProps) {
  const router = useRouter();

  const handleClose = () => {
    router.back();
  };

  return (
    <header className="bg-white w-full p-3 flex items-center justify-between shadow-sm">
      <div className="flex items-center gap-2">
        <div className="w-6 h-6 rounded-full bg-red-600 flex items-center justify-center">
          <span className="text-white text-sm">G</span>
        </div>
        <span className="text-sm font-medium text-[#404756]">Canal Oficial de Recarga</span>
      </div>
      {showCloseButton ? (
        <button
          onClick={handleClose}
          className="w-6 h-6 flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors"
          aria-label="Fechar"
        >
          <X className="w-5 h-5" />
        </button>
      ) : (
        <CircleUser className="w-6 h-6 text-gray-400" />
      )}
    </header>
  );
}
