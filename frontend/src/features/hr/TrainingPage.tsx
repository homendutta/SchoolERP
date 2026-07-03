/* Training programmes — records remain historical; enrol participants inline. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXSelect, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { TRAINING_STATUSES, hrApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'amber' | 'green' | 'red'> = {
  planned: 'gray',
  ongoing: 'amber',
  completed: 'green',
  cancelled: 'red',
};

export function TrainingPage() {
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
    { name: 'name', label: 'Training name', type: 'text', required: true },
    { name: 'provider', label: 'Provider', type: 'text' },
    { name: 'start_date', label: 'Start date', type: 'date' },
    { name: 'end_date', label: 'End date', type: 'date' },
    { name: 'duration_hours', label: 'Duration (hours)', type: 'number' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: TRAINING_STATUSES.map((s) => ({ value: s, label: s })),
    },
    { name: 'description', label: 'Description', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Training',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'provider', header: 'Provider', render: (r) => String(r.provider ?? '—') },
    {
      key: 'participants',
      header: 'Enrolled',
      render: (r) => String(Array.isArray(r.participants) ? r.participants.length : 0),
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Ref>
      title="Training"
      icon="chalkboard-user"
      unitLabel="programmes"
      api={hrApi.training}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        provider: '',
        start_date: '',
        end_date: '',
        duration_hours: '',
        status: 'planned',
        description: '',
      }}
      toForm={(r) => ({
        name: r.name,
        provider: r.provider ?? '',
        start_date: (r.start_date as string) ?? '',
        end_date: (r.end_date as string) ?? '',
        duration_hours: (r.duration_hours as number) ?? '',
        status: String(r.status ?? 'planned'),
        description: r.description ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search training…"
      sort="id"
      rowExtras={(r) => (
        <div className="w-40" onClick={(e) => e.stopPropagation()}>
          <AXSelect
            value=""
            onChange={(e) => {
              if (e.target.value) hrApi.assignTraining(r.id, Number(e.target.value));
            }}
            options={[{ value: '', label: 'Enrol…' }, ...employees]}
          />
        </div>
      )}
    />
  );
}
