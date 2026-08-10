import {
  Coffee, UtensilsCrossed, Laptop, Droplet, Armchair, Soup, GraduationCap,
  Image as ImageIcon, Users, Sheet, Dumbbell, Pill, Watch, BookOpen, Package,
  type LucideIcon,
} from 'lucide-react';
import { cn } from '@/lib/utils';

/** Escolhe um ícone temático pelo nome/categoria do produto (evita thumb vazio). */
function iconFor(nome: string, tags: string[] = []): LucideIcon {
  const t = (nome + ' ' + tags.join(' ')).toLowerCase();
  const map: [RegExp, LucideIcon][] = [
    [/cafeteir|coffee|café/, Coffee],
    [/spice|tempero|cozinha|gourmet/, UtensilsCrossed],
    [/laptop|notebook|eletr/, Laptop],
    [/bottle|garrafa|água|water/, Droplet],
    [/chair|cadeira|office/, Armchair],
    [/blender|liquidific/, Soup],
    [/whey|suplement|nutra|protein/, Dumbbell],
    [/pill|cápsula|kit/, Pill],
    [/watch|smartwatch|relógio/, Watch],
    [/criativo|pack|template|imagem|design/, ImageIcon],
    [/mentoria|comunidade|grupo|escala/, Users],
    [/planilha|gestão|ferramenta/, Sheet],
    [/ebook|livro/, BookOpen],
    [/método|curso|renda|copy|aula/, GraduationCap],
  ];
  for (const [re, ic] of map) if (re.test(t)) return ic;
  return Package;
}

/** Thumbnail de produto: gradiente da marca + ícone temático branco. */
export function ProductThumb({
  nome,
  cor,
  tags = [],
  size = 36,
  className,
}: {
  nome: string;
  cor: string;
  tags?: string[];
  size?: number;
  className?: string;
}) {
  const Icon = iconFor(nome, tags);
  const px = `${size}px`;
  const radius = size >= 48 ? 'rounded-xl' : 'rounded-lg';
  return (
    <span
      className={cn(
        'flex shrink-0 items-center justify-center bg-gradient-to-br text-white/95 shadow-inner',
        cor,
        radius,
        className
      )}
      style={{ width: px, height: px }}
      aria-hidden
    >
      <Icon size={Math.round(size * 0.5)} strokeWidth={1.75} />
    </span>
  );
}
