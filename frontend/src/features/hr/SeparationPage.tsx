/* Employee separation — the employee is NEVER deleted; a separated employment
 * state is recorded and the employee remains searchable. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { CLEARANCE_STATUSES, SEPARATION_TYPES, hrApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'amber' | 'green'> = {
  pending: 'gray',
  in_progress: 'amber',
  completed: 'green',
};

export function SeparationPage() {
  const { user } = useAuth();
  const [employees, setEmployees] = useState<FieldOption[]>([]);

  useEffect(() => {
    staffApi.staff
      .list({ per_page: 500, sort: 'name' })
      .then((r) =>
        setEmployees(
          r.data.map((s) => ({ value: String(s.id), label: `${s.employee_number} — ${s.name}` }))
        )
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'staff_id', label: 'Employee', type: 'select', options: employees, required: true },
    {
      name: 'separation_type',
      label: 'Type',
      type: 'select',
      options: SEPARATION_TYPES.map((t) => ({ value: t, label: t.replace(/_/g, ' ') })),
      required: true,
    },
    { name: 'last_working_day', label: 'Last working day', type: 'date' },
    { name: 'reason', label: 'Reason', type: 'text' },
    {
      name: 'clearance_status',
      label: 'Clearance',
      type: 'select',
      options: CLEARANCE_STATUSES.map((c) => ({ value: c, label: c.replace(/_/g, ' ') })),
    },
    { name: 'exit_notes', label: 'Exit notes', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'employee',
      header: 'Employee',
      render: (r) => (
        <span className="font-medium">
          {String((r.employee as { name?: string })?.name ?? r.staff_id)}
        </span>
      ),
    },
    { key: 'type', header: 'Type', render: (r) => String(r.separation_type).replace(/_/g, ' ') },
    { key: 'lwd', header: 'Last day', render: (r) => String(r.last_working_day ?? '—') },
    {
      key: 'clearance',
      header: 'Clearance',
      render: (r) => (
        <AXBadge tone={TONES[String(r.clearance_status)] ?? 'gray'}>
          {String(r.clearance_status).replace(/_/g, ' ')}
        </AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<Ref>
      title="Employee Separation"
      icon="user-xmark"
      unitLabel="separations"
      api={hrApi.separation}
      columns={columns}
      fields={fields}
      emptyForm={{
        staff_id: '',
        separation_type: 'resignation',
        last_working_day: '',
        reason: '',
        clearance_status: 'pending',
        exit_notes: '',
      }}
      toForm={(r) => ({
        staff_id: String(r.staff_id),
        separation_type: String(r.separation_type ?? 'resignation'),
        last_working_day: (r.last_working_day as string) ?? '',
        reason: r.reason ?? '',
        clearance_status: String(r.clearance_status ?? 'pending'),
        exit_notes: r.exit_notes ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
