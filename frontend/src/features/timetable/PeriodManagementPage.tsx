/* Period Management — configurable bell schedule (incl. breaks). Never hardcoded. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { timetableApi, type Period } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'code', label: 'Code', type: 'text' },
  { name: 'start_time', label: 'Start time (HH:MM)', type: 'text' },
  { name: 'end_time', label: 'End time (HH:MM)', type: 'text' },
  { name: 'sort_order', label: 'Sort order', type: 'number' },
  { name: 'is_break', label: 'Break period', type: 'checkbox' },
];

export function PeriodManagementPage() {
  const { user } = useAuth();

  const columns: AXColumn<Period>[] = [
    { key: 'sort_order', header: '#', render: (r) => r.sort_order },
    { key: 'name', header: 'Period', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'code', header: 'Code', render: (r) => r.code ?? '—' },
    { key: 'time', header: 'Time', render: (r) => `${r.start_time ?? '—'} – ${r.end_time ?? '—'}` },
    {
      key: 'is_break',
      header: 'Type',
      render: (r) => (
        <AXBadge tone={r.is_break ? 'amber' : 'navy'}>{r.is_break ? 'Break' : 'Class'}</AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<Period>
      title="Periods"
      icon="clock"
      unitLabel="periods"
      api={timetableApi.periods}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        code: '',
        start_time: '',
        end_time: '',
        sort_order: 0,
        is_break: false,
      }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        start_time: r.start_time ?? '',
        end_time: r.end_time ?? '',
        sort_order: r.sort_order,
        is_break: r.is_break,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search periods…"
      sort="sort_order"
    />
  );
}
