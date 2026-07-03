/* LMS Dashboard — role-aware (teacher counts / student-parent progress). */
import { useEffect, useState } from 'react';
import { lmsApi } from './api';

export function LmsDashboardPage() {
  const [data, setData] = useState<Record<string, unknown> | null>(null);

  useEffect(() => {
    lmsApi
      .dashboard()
      .then(setData)
      .catch(() => setData(null));
  }, []);

  const role = String(data?.role ?? '');
  const widgets = (data?.widgets as Record<string, unknown>) ?? {};
  const children = (data?.children as Array<Record<string, unknown>>) ?? [];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-graduation-cap text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Learning Dashboard</h2>
      </div>

      {role === 'teacher' ? (
        <div className="grid grid-cols-2 gap-4 md:grid-cols-5">
          {Object.entries(widgets).map(([k, v]) => (
            <div key={k} className="erp-card">
              <div className="text-xl font-semibold text-[var(--navy-primary)]">
                {String(v ?? 0)}
              </div>
              <div className="text-xs uppercase tracking-wide text-gray-500">
                {k.replace(/_/g, ' ')}
              </div>
            </div>
          ))}
        </div>
      ) : (
        <div className="space-y-3">
          {children.map((c) => {
            const p = (c.progress as Record<string, unknown>) ?? {};
            return (
              <div key={String(c.student_id)} className="erp-card">
                <h3 className="mb-2 text-sm font-semibold text-[var(--navy-primary)]">
                  Student #{String(c.student_id)}
                </h3>
                <div className="grid grid-cols-2 gap-3 md:grid-cols-5">
                  {Object.entries(p).map(([k, v]) => (
                    <div key={k}>
                      <div className="text-lg font-semibold">{String(v ?? 0)}</div>
                      <div className="text-xs text-gray-500">{k.replace(/_/g, ' ')}</div>
                    </div>
                  ))}
                </div>
              </div>
            );
          })}
          {children.length === 0 && <p className="text-sm text-gray-400">No learning data yet.</p>}
        </div>
      )}
    </div>
  );
}
