import Image from "next/image";
import { Info } from "lucide-react";

interface BonusItemProps {
  selectedGameId?: string;
}

export function BonusItem({ selectedGameId = "ff" }: BonusItemProps) {
  const isDeltaForce = selectedGameId === "deltaforce";
  
  return (
    <div className="mx-3 mt-3 bg-white rounded-lg p-3 relative overflow-hidden shadow-sm">
      <div className="relative z-10">
        <h2 className="text-sm font-medium text-[#404756] mb-1">Você ganhou 71% de desconto!</h2>
        <div className="flex items-center mt-2">
          <Image
            src={isDeltaForce ? "/img/deltaforce/deltacoin.png" : "/images/credit_icon_large.png"}
            alt={isDeltaForce ? "Delta Coins" : "Diamante"}
            width={20}
            height={20}
            className={`mr-2 ${isDeltaForce ? "scale-[0.75]" : ""}`}
            unoptimized
          />
          <span className="text-sm font-bold text-[#404756]">{isDeltaForce ? "6.480" : "5.600"}</span>
        </div>
      </div>
      <div className="absolute right-0 bottom-0 opacity-20">
        <Image
          src="/images/bonus_offer_bg.png"
          alt="Item Bônus Background"
          width={120}
          height={80}
          unoptimized
        />
      </div>
      {!isDeltaForce && (
        <div className="absolute bottom-1 right-2 flex items-center text-[#6d7584] text-xs">
          <Info className="w-3 h-3 mr-1" />
          <span>Recife de Abrantes</span>
        </div>
      )}
    </div>
  );
} 