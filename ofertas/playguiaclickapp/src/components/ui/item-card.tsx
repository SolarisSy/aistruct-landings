import Image from "next/image";
import { ShieldCheck } from "lucide-react";

interface ItemCardProps {
  selectedGameId?: string;
}

export function ItemCard({ selectedGameId = "ff" }: ItemCardProps) {
  const isDeltaForce = selectedGameId === "deltaforce";
  
  return (
    <div className="mx-3 mt-3 rounded-lg overflow-hidden relative">
      {isDeltaForce ? (
        // Banner para Delta Force
        <div className="relative w-full h-20">
          <Image
            src="/images/delta-force-icon.png"
            alt="Delta Force Banner"
            fill
            className="object-cover"
            unoptimized
          />
          <div className="absolute inset-0 flex items-center p-3">
            <div className="w-10 h-10 relative mr-2">
              <Image
                src="/images/icon_gamma.png"
                alt="Delta Force"
                width={40}
                height={40}
                className="rounded-md"
                unoptimized
              />
            </div>
            <span className="text-white text-sm font-medium">Delta Force</span>
            <div className="ml-auto flex items-center text-white text-xs">
              <ShieldCheck className="w-4 h-4 mr-1 text-[#54b6d0]" />
              <span>Pagamento 100% Seguro</span>
            </div>
          </div>
        </div>
      ) : (
        // Banner original para Free Fire
        <div className="bg-[#2c2c42] rounded-lg overflow-hidden">
          <div className="flex items-center p-3">
            <div className="w-10 h-10 relative mr-2">
              <Image
                src="/images/icon_alpha.png"
                alt="Free Fire"
                width={40}
                height={40}
                className="rounded-md"
                unoptimized
              />
            </div>
            <span className="text-white text-sm font-medium">Free Fire</span>
            <div className="ml-auto flex items-center text-white text-xs">
              <ShieldCheck className="w-4 h-4 mr-1 text-[#54b6d0]" />
              <span>Pagamento 100% Seguro</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
} 