/* Identity Dashboard — totals by owner type and status. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { identityApi } from './api';

export function IdentityDashboardPage() {
  const { user } = useAuth();
  const [counts, setCounts] = useState<Record<string, number | null>>({});

  useEffect(() => {
    const school = user?.school_id ?? undefined;
    const total = (params: Record<string, unknown>) =>
      identityApi
        .search({ per_page: 1, ...params })
        .then((r) => r.meta.total)
        .catch(() => 0);

    const queries: Record<string, Promise<number>> = {
      total: total({ filter: { school_id: school } }),
      student: total({ filter: { school_id: school, identity_type: 'student' } }),
      guardian: total({ filter: { school_id: school, identity_type: 'guardian' } }),
      staff: total({ filter: { school_id: school, identity_type: 'staff' } }),
      active: total({ filter: { school_id: school, status: 'active' } }),
      disabled: total({ filter: { school_id: school, status: 'disabled' } }),
    };
    Object.entries(queries).forEach(([k, p]) =>
      p.then((v) => setCounts((c) => ({ ...c, [k]: v })))
    );
  }, [user?.school_id]);

  const cards = [
    { label: 'Total Identities', icon: 'fingerprint', key: 'total' },
    { label: 'Students', icon: 'user-graduate', key: 'student' },
    { label: 'Guardians', icon: 'users', key: 'guardian' },
    { label: 'Staff', icon: 'id-card-clip', key: 'staff' },
    { label: 'Active', icon: 'circle-check', key: 'active' },
    { label: 'Disabled', icon: 'circle-xmark', key: 'disabled' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-fingerprint text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Identity Dashboard</h2>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-3">
        {cards.map((c) => (
          <div key={c.key} className="erp-card flex items-center gap-4">
            <div className="bg-[var(--navy-primary)]/10 flex h-12 w-12 items-center justify-center rounded-lg text-[var(--navy-primary)]">
              <i className={`fas fa-${c.icon} text-lg`} />
            </div>
            <div>
              <div className="text-2xl font-semibold text-[var(--navy-primary)]">
                {counts[c.key] ?? '—'}
              </div>
              <div className="text-xs uppercase tracking-wide text-gray-500">{c.label}</div>
            </div>
          </div>
        ))}
      </div>

      <p className="text-sm text-gray-400">
        Identity is permanent — it never changes when a student is promoted/transferred or a staff
        member changes department. QR codes and barcodes always reference the same identity.
      </p>
    </div>
  );
}
