/*
 * Sidebar — preserves the reference application's navy, grouped, role-adaptive
 * navigation. Groups and per-role ordering come from the navigation model.
 */
import { buildSidebar, type Role } from '@app/navigation/menu';

interface SidebarProps {
  role: Role;
  activeMenu: string;
  onSelect: (id: string) => void;
  collapsed: boolean;
  schoolName?: string;
}

export function Sidebar({ role, activeMenu, onSelect, collapsed, schoolName }: SidebarProps) {
  const groups = buildSidebar(role);

  return (
    <aside
      className={`flex h-full flex-col bg-[var(--navy-primary)] text-white transition-all duration-200 ${
        collapsed ? 'w-sidebar-collapsed' : 'w-sidebar'
      }`}
    >
      {/* Brand */}
      <div className="flex h-header items-center gap-3 border-b border-white/10 px-4">
        <i className="fas fa-graduation-cap text-xl text-[var(--navy-accent)]" />
        {!collapsed && (
          <span className="truncate text-sm font-semibold">
            {schoolName ?? 'Asylinx School ERP'}
          </span>
        )}
      </div>

      {/* Grouped menu */}
      <nav className="flex-1 overflow-y-auto py-2">
        {groups.map((g) => (
          <div key={g.group}>
            {!collapsed && <div className="sidebar-group-title">{g.label}</div>}
            {g.items.map((item) => {
              const active = item.id === activeMenu;
              return (
                <button
                  key={item.id}
                  type="button"
                  onClick={() => onSelect(item.id)}
                  title={item.label}
                  className={`sidebar-item w-full text-left ${active ? 'sidebar-item-active' : ''}`}
                >
                  <i className={`fas fa-${item.icon} w-5 text-center`} />
                  {!collapsed && <span className="truncate">{item.label}</span>}
                </button>
              );
            })}
          </div>
        ))}
      </nav>
    </aside>
  );
}
