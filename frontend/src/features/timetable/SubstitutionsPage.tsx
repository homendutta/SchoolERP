/* Substitutions — temporary teacher cover. Separate records; the master
 * timetable is never modified. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { useClasses } from '@features/academic/useReference';
import { timetableApi, useStaffTeachers, SUBSTITUTION_STATUSES, type Substitution } from './api';

const TONES: Record<string, 'navy' | 'green' | 'amber' | 'gray'> = {
  planned: 'amber',
  confirmed: 'green',
  cancelled: 'gray',
};

export function SubstitutionsPage() {
  const { user } = useAuth();
  const teachers = useStaffTeachers();
  const classes = useClasses();
  const [periods, setPeriods] = useState<FieldOption[]>([]);

  useEffect(() => {
    timetableApi.periods
      .list({ per_page: 100, sort: 'sort_order' })
      .then((r) => setPeriods(r.data.map((p) => ({ value: String(p.id), label: p.name }))));
  }, []);

  const fields: Field[] = [
    { name: 'date', label: 'Date', type: 'date', required: true },
    {
      name: 'period_id',
      label: 'Period',
      type: 'select',
      options: [{ value: '', label: '—' }, ...periods],
    },
    {
      name: 'original_teacher_id',
      label: 'Original teacher',
      type: 'select',
      options: [{ value: '', label: '—' }, ...teachers],
    },
    {
      name: 'substitute_teacher_id',
      label: 'Substitute teacher',
      type: 'select',
      options: [{ value: '', label: '—' }, ...teachers],
    },
    {
      name: 'class_id',
      label: 'Class',
      type: 'select',
      options: [{ value: '', label: '—' }, ...classes],
    },
    { name: 'reason', label: 'Reason', type: 'text' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: SUBSTITUTION_STATUSES.map((s) => ({ value: s, label: s })),
    },
  ];

  const columns: AXColumn<Substitution>[] = [
    { key: 'date', header: 'Date', render: (r) => r.date ?? '—' },
    { key: 'period', header: 'Period', render: (r) => r.period ?? '—' },
    { key: 'class', header: 'Class', render: (r) => r.class ?? '—' },
    { key: 'original', header: 'Original', render: (r) => r.original_teacher ?? '—' },
    {
      key: 'substitute',
      header: 'Substitute',
      render: (r) => <span className="font-medium">{r.substitute_teacher ?? '—'}</span>,
    },
    { key: 'reason', header: 'Reason', render: (r) => r.reason ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Substitution>
      title="Substitutions"
      icon="user-clock"
      unitLabel="substitutions"
      api={timetableApi.substitutions}
      columns={columns}
      fields={fields}
      emptyForm={{
        date: '',
        period_id: '',
        original_teacher_id: '',
        substitute_teacher_id: '',
        class_id: '',
        reason: '',
        status: 'planned',
      }}
      toForm={(r) => ({
        date: r.date ?? '',
        period_id: String(r.period_id),
        original_teacher_id: r.original_teacher_id ? String(r.original_teacher_id) : '',
        substitute_teacher_id: String(r.substitute_teacher_id),
        class_id: r.class_id ? String(r.class_id) : '',
        reason: r.reason ?? '',
        status: r.status,
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="date"
    />
  );
}
