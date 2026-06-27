/*
 * LoginPage — real authentication against the API. Preserves the reference
 * application's navy gradient login with a single unified identifier field
 * (staff number / admission number / parent id / mobile / email) + password.
 */
import { useState, type FormEvent } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { ApiError } from '@core/api/client';

export function LoginPage() {
  const { login } = useAuth();
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const onSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      await login(identifier.trim(), password);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Unable to sign in. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div
      className="flex min-h-screen items-center justify-center p-5"
      style={{
        background:
          'radial-gradient(circle at 15% 20%, rgba(0,116,217,0.35) 0%, transparent 45%),' +
          'radial-gradient(circle at 85% 80%, rgba(0,140,255,0.25) 0%, transparent 50%),' +
          'linear-gradient(135deg, var(--navy-dark) 0%, var(--navy-primary) 100%)',
      }}
    >
      <div className="w-full max-w-sm rounded-xl bg-white p-8 shadow-2xl">
        <div className="mb-6 text-center">
          <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[var(--navy-primary)] text-2xl text-white">
            <i className="fas fa-graduation-cap" />
          </div>
          <h1 className="text-xl font-bold text-[var(--navy-primary)]">Asylinx School ERP</h1>
          <p className="text-sm text-gray-500">Sign in to continue</p>
        </div>

        {error && (
          <div className="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-[var(--danger)]">
            <i className="fas fa-circle-exclamation mr-1" /> {error}
          </div>
        )}

        <form onSubmit={onSubmit} className="space-y-4">
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">
              Staff No. / Admission No. / Parent ID / Mobile / Email
            </label>
            <div className="flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 focus-within:border-[var(--navy-accent)]">
              <i className="fas fa-user text-gray-400" />
              <input
                value={identifier}
                onChange={(e) => setIdentifier(e.target.value)}
                className="w-full outline-none"
                placeholder="Enter your identifier"
                autoComplete="username"
                required
              />
            </div>
          </div>

          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Password</label>
            <div className="flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 focus-within:border-[var(--navy-accent)]">
              <i className="fas fa-lock text-gray-400" />
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full outline-none"
                placeholder="Enter your password"
                autoComplete="current-password"
                required
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={submitting}
            className="flex w-full items-center justify-center gap-2 rounded-md bg-[var(--navy-primary)] py-2.5 font-semibold text-white transition-colors hover:bg-[var(--navy-hover)] disabled:opacity-60"
          >
            {submitting && <i className="fas fa-spinner fa-spin" />}
            {submitting ? 'Signing in…' : 'Sign In'}
          </button>
        </form>
      </div>
    </div>
  );
}
