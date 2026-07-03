/* Disciplinary records — complete history is maintained (never overwritten). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { DISCIPLINARY_ACTIONS, hrApi, type Ref } from './api';

const TONES: Record<string, 'amber' | 'red' | 'gray'> = {
  warning: 'amber',
  suspension: 'red',
  notice: 'amber',
  termination_recommendation: 'red',
  other: 'gray',
};

export function DisciplinePage() {
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
      name: 'action_type',
      label: 'Action',
      type: 'select',
      options: DISCIPLINARY_ACTIONS.map((a) => ({ value: a, label: a.replace(/_/g, ' ') })),
      required: true,
    },
    { name: 'subject', label: 'Subject', type: 'text' },
    { name: 'incident_date', label: 'Incident date', type: 'date' },
    { name: 'action_date', label: 'Action date', type: 'date' },
    { name: 'description', label: 'Description', type: 'text' },
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
    {
      key: 'action',
      header: 'Action',
      render: (r) => (
        <AXBadge tone={TONES[String(r.action_type)] ?? 'gray'}>
          {String(r.action_type).replace(/_/g, ' ')}
        </AXBadge>
      ),
    },
    { key: 'subject', header: 'Subject', render: (r) => String(r.subject ?? '—') },
    { key: 'date', header: 'Date', render: (r) => String(r.action_date ?? '—') },
  ];

  return (
    <EntityManager<Ref>
      title="Disciplinary Records"
      icon="gavel"
      unitLabel="records"
      api={hrApi.discipline}
      columns={columns}
      fields={fields}
      emptyForm={{
        staff_id: '',
        action_type: 'warning',
        subject: '',
        incident_date: '',
        action_date: '',
        description: '',
      }}
      toForm={(r) => ({
        staff_id: String(r.staff_id),
        action_type: String(r.action_type ?? 'warning'),
        subject: r.subject ?? '',
        incident_date: (r.incident_date as string) ?? '',
        action_date: (r.action_date as string) ?? '',
        description: r.description ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
