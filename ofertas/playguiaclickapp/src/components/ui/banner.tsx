import Image from "next/image";

export function Banner() {
  return (
    <div className="relative w-full h-20 overflow-hidden">
      <Image
        src="/images/promo_display_banner.jpeg"
        alt="Promo Banner"
        fill
        className="object-cover"
        priority
        unoptimized
      />
      <div className="absolute inset-0 flex flex-col justify-center p-4">
        <h1 className="text-lg font-bold text-[#ffd25e] drop-shadow-md">
          CENTRO DE RECARGA
        </h1>
        <p className="text-sm font-medium text-[#ffd25e] drop-shadow-md">
          GANHE ATÉ <span className="text-white">20% DE BONUS</span> DE DIAMANTES
        </p>
      </div>
    </div>
  );
}
