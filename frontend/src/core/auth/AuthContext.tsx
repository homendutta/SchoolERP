/*
 * Auth context — real Sanctum token auth against the Laravel API.
 *  - login()  -> POST /auth/login, stores token, loads the user
 *  - logout() -> POST /auth/logout, clears token
 *  - on mount -> if a token exists, restores the session via GET /auth/me
 * Exposes the user's roles + permissions to drive the dynamic, role-adaptive UI.
 */
import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { apiClient, tokenStore } from '@core/api/client';

export interface SessionUser {
  id: number;
  name: string;
  email: string;
  username: string | null;
  status: string;
  is_super_admin: boolean;
  school_id: number | null;
  roles: string[];
  permissions: string[];
}

interface LoginResponse {
  token: string;
  token_type: string;
  user: SessionUser;
}

interface AuthContextValue {
  user: SessionUser | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (identifier: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  hasPermission: (slug: string) => boolean;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<SessionUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Restore session on boot if a token is present.
  useEffect(() => {
    let active = true;
    const token = tokenStore.get();
    if (!token) {
      setIsLoading(false);
      return;
    }
    apiClient
      .get<SessionUser>('/auth/me')
      .then((u) => active && setUser(u))
      .catch(() => {
        tokenStore.clear();
      })
      .finally(() => active && setIsLoading(false));
    return () => {
      active = false;
    };
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      isAuthenticated: user !== null,
      isLoading,
      login: async (identifier, password) => {
        const res = await apiClient.post<LoginResponse>('/auth/login', { identifier, password });
        tokenStore.set(res.token);
        setUser(res.user);
      },
      logout: async () => {
        try {
          await apiClient.post('/auth/logout');
        } finally {
          tokenStore.clear();
          setUser(null);
        }
      },
      hasPermission: (slug) => !!user && (user.is_super_admin || user.permissions.includes(slug)),
    }),
    [user, isLoading]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within an AuthProvider');
  return ctx;
}
