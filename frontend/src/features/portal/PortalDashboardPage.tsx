/* Portal Dashboard — role-aware (parent/student/teacher). Single dashboard. */
import { useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { portalApi } from './api';
import { usePortal } from './usePortal';
import { PortalShell } from './PortalShell';

export function PortalDashboardPage() {
  const { context, error } = usePortal();
  const [data, setData] = useState<Record<string, unknown> | null>(null);

  useEffect(() => {
    portalApi
      .dashboard()
      .then((d) => setData(d))
      .catch(() => undefined);
  }, []);

  const widgets = (data?.widgets as Record<string, unknown>) ?? {};
  const children = (data?.children as Array<Record<string, unknown>>) ?? [];

  return (
    <PortalShell
      title="My Dashboard"
      icon="gauge"
      context={context}
      studentId={null}
      requiresStudent={false}
      error={error}
    >
      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        {Object.entries(widgets).map(([k, v]) => (
          <div key={k} className="erp-card">
            <div className="text-xl font-semibold text-[var(--navy-primary)]">
              {String(v ?? '—')}
            </div>
            <div className="text-xs uppercase tracking-wide text-gray-500">
              {k.replace(/_/g, ' ')}
            </div>
          </div>
        ))}
      </div>

      {children.length > 0 && (
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Children</h3>
          <div className="space-y-2">
            {children.map((c) => (
              <div
                key={String(c.student_id)}
                className="flex flex-wrap items-center justify-between gap-2 border-b py-2 text-sm last:border-0"
              >
                <span className="font-medium">{String(c.name ?? c.student_id)}</span>
                <span className="flex items-center gap-3 text-gray-600">
                  <span>Attendance: {String(c.attendance_percentage ?? 0)}%</span>
                  <AXBadge tone={Number(c.outstanding ?? 0) > 0 ? 'amber' : 'green'}>
                    Due: {String(c.outstanding ?? 0)}
                  </AXBadge>
                </span>
              </div>
            ))}
          </div>
        </div>
      )}
    </PortalShell>
  );
}
