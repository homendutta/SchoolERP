/*
 * App — composes the application providers (auth) and the router. The single
 * API client and theme are wired here; business modules are added later.
 */
import { BrowserRouter } from 'react-router-dom';
import { AuthProvider } from '@core/auth/AuthContext';
import { AppRoutes } from '@app/routing/AppRoutes';

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <AppRoutes />
      </BrowserRouter>
    </AuthProvider>
  );
}
