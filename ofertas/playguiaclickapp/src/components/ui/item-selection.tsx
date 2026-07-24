import Image from "next/image";

interface ItemSelectionProps {
  onItemSelect: (itemName: string, itemId: string) => void;
  selectedGameId?: string;
}

const items = [
  {
    id: "ff",
    name: "Free Fire",
    icon: "/images/icon_alpha.png",
  },
  {
    id: "deltaforce",
    name: "Delta Force",
    icon: "/images/icon_gamma.png",
  },
];

export function ItemSelection({ onItemSelect, selectedGameId = "ff" }: ItemSelectionProps) {
  return (
    <div className="p-3 bg-white">
      <h2 className="text-sm font-medium text-[#404756] mb-2">Seleção de itens</h2>
      <div className="flex gap-4 overflow-x-auto pb-2">
        {items.map((item) => {
          const isSelected = item.id === selectedGameId;
          return (
            <div
              key={item.id}
              className={`flex flex-col items-center min-w-16 ${isSelected ? "opacity-100" : "opacity-60"}`}
              onClick={() => onItemSelect(item.name, item.id)}
              role="button"
              tabIndex={0}
            >
            <div className="w-14 h-14 relative mb-1">
              <Image
                src={item.icon}
                alt={item.name}
                width={56}
                height={56}
                className="rounded-md"
                unoptimized
              />
            </div>
            <span className="text-xs text-center text-[#404756] whitespace-nowrap max-w-16 truncate">
              {item.name}
            </span>
            </div>
          );
        })}
      </div>
    </div>
  );
} 