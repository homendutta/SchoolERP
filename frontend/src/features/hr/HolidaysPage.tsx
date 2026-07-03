/* Holidays — configurable (national / state / school / optional). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { HOLIDAY_TYPES, hrApi, type Ref } from './api';

const TONES: Record<string, 'navy' | 'amber' | 'green' | 'gray'> = {
  national: 'navy',
  state: 'amber',
  school: 'green',
  optional: 'gray',
};

export function HolidaysPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Holiday name', type: 'text', required: true },
    { name: 'date', label: 'Date', type: 'date', required: true },
    { name: 'end_date', label: 'End date (optional)', type: 'date' },
    {
      name: 'holiday_type',
      label: 'Type',
      type: 'select',
      options: HOLIDAY_TYPES.map((t) => ({ value: t, label: t })),
    },
    { name: 'is_optional', label: 'Optional', type: 'checkbox' },
    { name: 'description', label: 'Description', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Holiday',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'date', header: 'Date', render: (r) => String(r.date ?? '—') },
    {
      key: 'type',
      header: 'Type',
      render: (r) => (
        <AXBadge tone={TONES[String(r.holiday_type)] ?? 'gray'}>{String(r.holiday_type)}</AXBadge>
      ),
    },
    { key: 'optional', header: 'Optional', render: (r) => (r.is_optional ? '✓' : '—') },
  ];

  return (
    <EntityManager<Ref>
      title="Holidays"
      icon="calendar-day"
      unitLabel="holidays"
      api={hrApi.holidays}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        date: '',
        end_date: '',
        holiday_type: 'school',
        is_optional: false,
        description: '',
      }}
      toForm={(r) => ({
        name: r.name,
        date: (r.date as string) ?? '',
        end_date: (r.end_date as string) ?? '',
        holiday_type: String(r.holiday_type ?? 'school'),
        is_optional: Boolean(r.is_optional),
        description: r.description ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search holidays…"
      sort="date"
    />
  );
}
