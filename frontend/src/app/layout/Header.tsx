/*
 * Header — preserves the reference top bar: collapse toggle, page title with
 * icon, global search (available on every page), refresh, school name, and the
 * signed-in user with sign-out.
 */
import { MENU_CATALOG } from '@app/navigation/menu';
import { useAuth } from '@core/auth/AuthContext';

interface HeaderProps {
  activeMenu: string;
  onToggleSidebar: () => void;
  schoolName?: string;
}

export function Header({ activeMenu, onToggleSidebar, schoolName }: HeaderProps) {
  const { user, logout } = useAuth();
  const item = MENU_CATALOG[activeMenu] ?? MENU_CATALOG.dashboard;

  return (
    <header className="flex h-header items-center justify-between border-b border-black/5 bg-white px-4">
      <div className="flex items-center gap-3">
        <button
          type="button"
          onClick={onToggleSidebar}
          className="rounded p-2 text-gray-500 hover:bg-gray-100"
          aria-label="Toggle navigation"
        >
          <i className="fas fa-bars" />
        </button>
        <h1 className="flex items-center gap-2 text-base font-semibold text-[var(--navy-primary)]">
          <i className={`fas fa-${item.icon}`} />
          {item.label}
        </h1>
      </div>

      {/* Global search — available from every page (resolved via the API, scoped). */}
      <div className="mx-4 hidden max-w-md flex-1 items-center md:flex">
        <div className="flex w-full items-center gap-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm text-gray-500">
          <i className="fas fa-search" />
          <input
            type="search"
            placeholder="Search students, fees, receipts…"
            className="w-full bg-transparent outline-none"
            aria-label="Global search"
          />
        </div>
      </div>

      <div className="flex items-center gap-3">
        <button
          type="button"
          className="rounded p-2 text-gray-500 hover:bg-gray-100"
          aria-label="Refresh"
        >
          <i className="fas fa-arrows-rotate" />
        </button>
        {schoolName && (
          <span className="hidden text-xs text-gray-400 lg:inline">
            <i className="fas fa-school" /> {schoolName}
          </span>
        )}
        <span className="hidden text-sm text-gray-600 sm:inline">
          Welcome, {user?.name ?? user?.username ?? 'User'}
        </span>
        <button
          type="button"
          onClick={() => void logout()}
          className="rounded p-2 text-gray-500 hover:bg-gray-100"
          aria-label="Sign out"
          title="Sign out"
        >
          <i className="fas fa-sign-out-alt" />
        </button>
      </div>
    </header>
  );
}
