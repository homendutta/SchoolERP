/*
 * Bottom navigation — mobile-only, mirrors the reference application's mobile
 * bottom bar. Surfaces a few top items for the role plus a menu toggle.
 */
import { buildSidebar, type Role } from '@app/navigation/menu';

interface BottomNavigationProps {
  role: Role;
  activeMenu: string;
  onSelect: (id: string) => void;
  onOpenMenu: () => void;
}

export function BottomNavigation({ role, activeMenu, onSelect, onOpenMenu }: BottomNavigationProps) {
  // First few items across the role's groups (reference behaviour).
  const items = buildSidebar(role)
    .flatMap((g) => g.items)
    .slice(0, 4);

  return (
    <nav
      className="fixed inset-x-0 bottom-0 z-30 flex h-bottom-nav items-stretch border-t border-black/5 bg-white md:hidden"
      aria-label="Primary"
    >
      {items.map((item) => {
        const active = item.id === activeMenu;
        return (
          <button
            key={item.id}
            type="button"
            onClick={() => onSelect(item.id)}
            className={`flex flex-1 flex-col items-center justify-center gap-1 text-[11px] ${
              active ? 'text-[var(--navy-accent)]' : 'text-gray-500'
            }`}
          >
            <i className={`fas fa-${item.icon}`} />
            <span className="truncate">{item.label}</span>
          </button>
        );
      })}
      <button
        type="button"
        onClick={onOpenMenu}
        className="flex flex-1 flex-col items-center justify-center gap-1 text-[11px] text-gray-500"
      >
        <i className="fas fa-ellipsis-h" />
        <span>Menu</span>
      </button>
    </nav>
  );
}
