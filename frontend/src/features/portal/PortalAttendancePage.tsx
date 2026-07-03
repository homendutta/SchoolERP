/* Portal Attendance — read-only from the Attendance module. */
import { useEffect, useState } from 'react';
import { AXBadge, AXTable, type AXColumn } from '@ui/ax';
import { portalApi } from './api';
import { usePortal } from './usePortal';
import { PortalShell } from './PortalShell';

export function PortalAttendancePage() {
  const { context, studentId, setStudentId, error } = usePortal();
  const [data, setData] = useState<Record<string, unknown> | null>(null);

  useEffect(() => {
    if (!studentId) return;
    portalApi
      .attendance(studentId)
      .then(setData)
      .catch(() => setData(null));
  }, [studentId]);

  const summary = (data?.summary as Record<string, unknown>) ?? {};
  const recent = (data?.recent as Array<Record<string, unknown>>) ?? [];

  const columns: AXColumn<Record<string, unknown>>[] = [
    { key: 'date', header: 'Date', render: (r) => String(r.date) },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge
          tone={
            String(r.status) === 'present'
              ? 'green'
              : String(r.status) === 'absent'
                ? 'red'
                : 'amber'
          }
        >
          {String(r.status)}
        </AXBadge>
      ),
    },
  ];

  return (
    <PortalShell
      title="Attendance"
      icon="clipboard-check"
      context={context}
      studentId={studentId}
      onStudent={setStudentId}
      error={error}
    >
      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        {['percentage', 'present', 'absent', 'leave'].map((k) => (
          <div key={k} className="erp-card">
            <div className="text-xl font-semibold text-[var(--navy-primary)]">
              {String(summary[k] ?? 0)}
              {k === 'percentage' ? '%' : ''}
            </div>
            <div className="text-xs uppercase tracking-wide text-gray-500">{k}</div>
          </div>
        ))}
      </div>
      <AXTable
        columns={columns}
        rows={recent}
        rowKey={(r) => String(r.date)}
        empty="No attendance records."
      />
    </PortalShell>
  );
}
