/*
 * DashboardLayout — the application shell that preserves the reference layout:
 * navy sidebar + top header + main content + footer, with a mobile bottom nav.
 * Role-adaptive: the signed-in user's role drives the navigation.
 *
 * Foundation stage: renders the shell and a single active "page" placeholder.
 * Module pages mount into the content area as modules are built.
 */
import { useMemo, useState, type ReactNode } from 'react';
import { Sidebar } from './Sidebar';
import { Header } from './Header';
import { Footer } from './Footer';
import { BottomNavigation } from './BottomNavigation';
import { useAuth } from '@core/auth/AuthContext';
import { landingMenu, roleFromSlug } from '@app/navigation/menu';
import { SchoolSettingsPage } from '@features/administration/SchoolSettingsPage';
import { MasterDataPage } from '@features/administration/MasterDataPage';
import { FeatureFlagsPage } from '@features/administration/FeatureFlagsPage';
import { NumberGeneratorPage } from '@features/administration/NumberGeneratorPage';
import { GatewaysPage } from '@features/administration/GatewaysPage';

interface DashboardLayoutProps {
  children?: (activeMenu: string) => ReactNode;
  schoolName?: string;
}

export function DashboardLayout({ children, schoolName }: DashboardLayoutProps) {
  const { user } = useAuth();
  // The signed-in user's primary role drives the dynamic, role-adaptive sidebar.
  const role = roleFromSlug(user?.roles?.[0]);

  const [collapsed, setCollapsed] = useState(false);
  const [mobileSidebar, setMobileSidebar] = useState(false);
  const initial = useMemo(() => landingMenu(role), [role]);
  const [activeMenu, setActiveMenu] = useState(initial);

  const select = (id: string) => {
    setActiveMenu(id);
    setMobileSidebar(false);
  };

  return (
    <div className="flex h-screen overflow-hidden bg-[#f5f5f5]">
      {/* Desktop sidebar */}
      <div className="hidden md:block">
        <Sidebar
          role={role}
          activeMenu={activeMenu}
          onSelect={select}
          collapsed={collapsed}
          schoolName={schoolName}
        />
      </div>

      {/* Mobile sidebar drawer */}
      {mobileSidebar && (
        <div className="fixed inset-0 z-40 md:hidden">
          <div className="absolute inset-0 bg-black/40" onClick={() => setMobileSidebar(false)} />
          <div className="absolute inset-y-0 left-0">
            <Sidebar
              role={role}
              activeMenu={activeMenu}
              onSelect={select}
              collapsed={false}
              schoolName={schoolName}
            />
          </div>
        </div>
      )}

      {/* Main column */}
      <div className="flex min-w-0 flex-1 flex-col">
        <Header
          activeMenu={activeMenu}
          schoolName={schoolName}
          onToggleSidebar={() =>
            window.matchMedia('(min-width: 768px)').matches
              ? setCollapsed((c) => !c)
              : setMobileSidebar(true)
          }
        />
        <main className="flex-1 overflow-y-auto p-4 pb-24 md:pb-4">
          {children ? children(activeMenu) : renderContent(activeMenu)}
        </main>
        <Footer />
      </div>

      <BottomNavigation
        role={role}
        activeMenu={activeMenu}
        onSelect={select}
        onOpenMenu={() => setMobileSidebar(true)}
      />
    </div>
  );
}

/** Route the active menu id to its Administration page (others: placeholder). */
function renderContent(activeMenu: string) {
  switch (activeMenu) {
    case 'schoolSettings':
      return <SchoolSettingsPage />;
    case 'masterData':
      return <MasterDataPage />;
    case 'numberGenerator':
      return <NumberGeneratorPage />;
    case 'featureFlags':
      return <FeatureFlagsPage />;
    case 'gateways':
      return <GatewaysPage />;
    default:
      return <DefaultContent activeMenu={activeMenu} />;
  }
}

/** Foundation placeholder shown until the selected module is built. */
function DefaultContent({ activeMenu }: { activeMenu: string }) {
  return (
    <div className="erp-card">
      <div className="flex items-center gap-3 text-[var(--navy-primary)]">
        <i className="fas fa-cube text-lg" />
        <h2 className="text-lg font-semibold capitalize">{activeMenu}</h2>
      </div>
      <p className="mt-2 text-sm text-gray-500">
        Engineering foundation — module pages mount here as they are built. The
        layout, navigation, theme, and shell are production-ready.
      </p>
    </div>
  );
}
