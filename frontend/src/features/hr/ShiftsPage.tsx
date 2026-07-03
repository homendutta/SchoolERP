/* Work shifts — configurable; office hours are never hardcoded. */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import {
  EntityManager,
  statusCell,
  type Field,
  type FieldOption,
} from '@features/academic/EntityManager';
import { hrApi, type Ref } from './api';

const DAYS: FieldOption[] = [
  { value: '0', label: 'Sun' },
  { value: '1', label: 'Mon' },
  { value: '2', label: 'Tue' },
  { value: '3', label: 'Wed' },
  { value: '4', label: 'Thu' },
  { value: '5', label: 'Fri' },
  { value: '6', label: 'Sat' },
];

export function ShiftsPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Shift name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    { name: 'start_time', label: 'Start time (HH:MM)', type: 'text' },
    { name: 'end_time', label: 'End time (HH:MM)', type: 'text' },
    { name: 'grace_minutes', label: 'Grace minutes', type: 'number' },
    { name: 'weekly_off_pattern', label: 'Weekly off', type: 'multiselect', options: DAYS },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Shift',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'start', header: 'Start', render: (r) => String(r.start_time ?? '—') },
    { key: 'end', header: 'End', render: (r) => String(r.end_time ?? '—') },
    { key: 'grace', header: 'Grace', render: (r) => `${r.grace_minutes ?? 0}m` },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Shifts"
      icon="clock"
      unitLabel="shifts"
      api={hrApi.shifts}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        code: '',
        start_time: '09:00',
        end_time: '17:00',
        grace_minutes: 0,
        weekly_off_pattern: [],
      }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        start_time: (r.start_time as string) ?? '',
        end_time: (r.end_time as string) ?? '',
        grace_minutes: (r.grace_minutes as number) ?? 0,
        weekly_off_pattern: Array.isArray(r.weekly_off_pattern) ? r.weekly_off_pattern : [],
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search shifts…"
      sort="name"
    />
  );
}
