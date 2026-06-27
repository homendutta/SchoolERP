/*
 * Routing — preserves the one-domain URL model: the ERP lives under role
 * workspaces. Restores the session on boot (GET /auth/me) before routing.
 */
import { Navigate, Route, Routes } from 'react-router-dom';
import { useAuth } from '@core/auth/AuthContext';
import { LoginPage } from '@features/auth/LoginPage';
import { DashboardPage } from '@features/dashboard/DashboardPage';
import type { ReactNode } from 'react';

function Splash() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-[var(--navy-primary)] text-white">
      <i className="fas fa-spinner fa-spin text-2xl" />
    </div>
  );
}

function RequireAuth({ children }: { children: ReactNode }) {
  const { isAuthenticated, isLoading } = useAuth();
  if (isLoading) return <Splash />;
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" replace />;
}

export function AppRoutes() {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) return <Splash />;

  const workspaces = [
    '/admin', '/supervisor', '/accountant', '/clerk',
    '/receptionist', '/teacher', '/student', '/parent',
  ];

  return (
    <Routes>
      <Route
        path="/login"
        element={isAuthenticated ? <Navigate to="/" replace /> : <LoginPage />}
      />

      <Route
        path="/"
        element={
          <RequireAuth>
            <DashboardPage />
          </RequireAuth>
        }
      />
      {workspaces.map((p) => (
        <Route
          key={p}
          path={p}
          element={
            <RequireAuth>
              <DashboardPage />
            </RequireAuth>
          }
        />
      ))}

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
