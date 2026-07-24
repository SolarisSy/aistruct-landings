import Image from "next/image";
import { RefreshCw } from "lucide-react";

const diamondOptions = [
  { value: 65, selected: false },
  { value: 100, selected: false },
  { value: 310, selected: false },
  { value: 520, selected: true },
  { value: 1060, selected: false },
  { value: 2180, selected: false },
  { value: 5600, selected: false },
];

export function ValueSelection() {
  return (
    <div className="mx-3 mt-3 bg-white rounded-lg p-3 shadow-sm">
      <div className="flex items-center mb-3">
        <div className="w-6 h-6 rounded-full bg-[#c23743] flex items-center justify-center text-white text-xs font-bold">
          2
        </div>
        <h2 className="ml-2 text-sm font-medium text-[#404756]">Valor de Recarga</h2>
      </div>

      <div className="flex mb-3">
        <button className="bg-[#c23743] text-white text-sm py-1 px-4 rounded-full mr-2">
          Comprar
        </button>
        <button className="border border-gray-300 text-[#404756] text-sm py-1 px-4 rounded-full">
          Resgatar
        </button>
      </div>

      <div className="text-xs text-[#6d7584] mb-3">
        A denominação exibida suporta pagamento via{" "}
        <span className="font-bold">Pix via PagSeguro</span>. Clique em redefinir para remover a seleção.
        <button className="text-[#c23743] ml-1 inline-flex items-center">
          Reiniciar
          <RefreshCw className="w-3 h-3 ml-1" />
        </button>
      </div>

      <div className="grid grid-cols-3 gap-2 mb-3">
        {diamondOptions.map((option) => (
          <div
            key={option.value}
            className={`border ${
              option.selected ? "border-[#c23743]" : "border-gray-300"
            } rounded-md p-2 flex items-center justify-center ${
              option.selected ? "bg-[#fff9f9]" : ""
            }`}
          >
            <Image
              src="/images/diamante.png"
              alt="Diamante"
              width={16}
              height={16}
              className="mr-1"
              unoptimized
            />
            <span className="text-sm text-[#404756]">{option.value}</span>
          </div>
        ))}
      </div>

      <h3 className="text-sm font-medium text-[#404756] mb-2">Ofertas especiais</h3>
      <div className="grid grid-cols-2 gap-2">
        <div className="border border-gray-300 rounded-md p-2 relative overflow-hidden">
          <div className="hot-badge">Hot</div>
          <Image
            src="/images/assinatura-semanal.png"
            alt="Assinatura Semanal"
            width={140}
            height={80}
            className="mb-1"
            unoptimized
          />
          <span className="text-xs text-[#404756]">Assinatura Semanal</span>
        </div>

        <div className="border border-gray-300 rounded-md p-2 relative overflow-hidden">
          <div className="hot-badge">Hot</div>
          <Image
            src="/images/assinatura-mensal.png"
            alt="Assinatura Mensal"
            width={140}
            height={80}
            className="mb-1"
            unoptimized
          />
          <span className="text-xs text-[#404756]">Assinatura Mensal</span>
        </div>
      </div>
    </div>
  );
}
