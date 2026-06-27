/* Academic Dashboard — at-a-glance counts and the current academic year. */
import { useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { academicApi, type AcademicYear } from './api';

interface Stat {
  key: string;
  label: string;
  icon: string;
  value: number | null;
}

export function AcademicDashboardPage() {
  const [stats, setStats] = useState<Stat[]>([
    { key: 'years', label: 'Academic Years', icon: 'calendar-alt', value: null },
    { key: 'classes', label: 'Classes', icon: 'school', value: null },
    { key: 'sections', label: 'Sections', icon: 'table-cells', value: null },
    { key: 'subjects', label: 'Subjects', icon: 'book', value: null },
    { key: 'rooms', label: 'Rooms', icon: 'door-open', value: null },
    { key: 'groups', label: 'Subject Groups', icon: 'object-group', value: null },
  ]);
  const [current, setCurrent] = useState<AcademicYear | null>(null);

  useEffect(() => {
    const totals: Record<string, Promise<number>> = {
      years: academicApi.years.list({ per_page: 1 }).then((r) => r.meta.total),
      classes: academicApi.classes.list({ per_page: 1 }).then((r) => r.meta.total),
      sections: academicApi.sections.list({ per_page: 1 }).then((r) => r.meta.total),
      subjects: academicApi.subjects.list({ per_page: 1 }).then((r) => r.meta.total),
      rooms: academicApi.rooms.list({ per_page: 1 }).then((r) => r.meta.total),
      groups: academicApi.subjectGroups.list({ per_page: 1 }).then((r) => r.meta.total),
    };
    Object.entries(totals).forEach(([k, p]) =>
      p
        .then((value) => setStats((s) => s.map((st) => (st.key === k ? { ...st, value } : st))))
        .catch(() => undefined)
    );
    academicApi.years
      .list({ filter: { is_current: 1 }, per_page: 1 })
      .then((r) => setCurrent(r.data[0] ?? null))
      .catch(() => undefined);
  }, []);

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-graduation-cap text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Academic Overview</h2>
      </div>

      <div className="erp-card flex flex-wrap items-center gap-3">
        <span className="text-sm text-gray-500">Current academic year:</span>
        {current ? (
          <AXBadge tone="green">
            {current.name}
            {current.start_date ? ` (${current.start_date} → ${current.end_date})` : ''}
          </AXBadge>
        ) : (
          <AXBadge tone="amber">None set</AXBadge>
        )}
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-3">
        {stats.map((s) => (
          <div key={s.key} className="erp-card flex items-center gap-4">
            <div className="bg-[var(--navy-primary)]/10 flex h-12 w-12 items-center justify-center rounded-lg text-[var(--navy-primary)]">
              <i className={`fas fa-${s.icon} text-lg`} />
            </div>
            <div>
              <div className="text-2xl font-semibold text-[var(--navy-primary)]">
                {s.value ?? '—'}
              </div>
              <div className="text-xs uppercase tracking-wide text-gray-500">{s.label}</div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
