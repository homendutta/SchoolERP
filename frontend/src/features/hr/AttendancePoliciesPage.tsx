/* Attendance policies — DEFINED here, CONSUMED by the Attendance module. */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { hrApi, type Ref } from './api';

export function AttendancePoliciesPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Policy name', type: 'text', required: true },
    { name: 'grace_minutes', label: 'Grace minutes', type: 'number' },
    { name: 'late_after_minutes', label: 'Late after (minutes)', type: 'number' },
    { name: 'half_day_hours', label: 'Half-day hours', type: 'number' },
    { name: 'minimum_working_hours', label: 'Minimum working hours', type: 'number' },
    { name: 'overtime_eligible', label: 'Overtime eligible', type: 'checkbox' },
    { name: 'description', label: 'Description', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Policy',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'grace', header: 'Grace', render: (r) => `${r.grace_minutes ?? 0}m` },
    { key: 'min', header: 'Min hours', render: (r) => String(r.minimum_working_hours ?? '—') },
    { key: 'ot', header: 'Overtime', render: (r) => (r.overtime_eligible ? '✓' : '—') },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Attendance Policies"
      icon="user-clock"
      unitLabel="policies"
      api={hrApi.attendancePolicies}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        grace_minutes: 0,
        late_after_minutes: 0,
        half_day_hours: 4,
        minimum_working_hours: 8,
        overtime_eligible: false,
        description: '',
      }}
      toForm={(r) => ({
        name: r.name,
        grace_minutes: (r.grace_minutes as number) ?? 0,
        late_after_minutes: (r.late_after_minutes as number) ?? 0,
        half_day_hours: (r.half_day_hours as number) ?? '',
        minimum_working_hours: (r.minimum_working_hours as number) ?? '',
        overtime_eligible: Boolean(r.overtime_eligible),
        description: r.description ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search policies…"
      sort="name"
    />
  );
}
