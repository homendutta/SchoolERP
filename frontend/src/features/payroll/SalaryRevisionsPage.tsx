/* Salary Revisions — each revision creates a new immutable salary version. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { REVISION_TYPES, payrollApi, type Ref } from './api';

export function SalaryRevisionsPage() {
  const { user } = useAuth();
  const [employees, setEmployees] = useState<FieldOption[]>([]);
  const [structures, setStructures] = useState<FieldOption[]>([]);

  useEffect(() => {
    staffApi.staff
      .list({ per_page: 500, sort: 'name' })
      .then((r) =>
        setEmployees(
          r.data.map((s) => ({ value: String(s.id), label: `${s.employee_number} — ${s.name}` }))
        )
      );
    payrollApi.structures
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) =>
        setStructures(r.data.map((s) => ({ value: String(s.id), label: String(s.name) })))
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'staff_id', label: 'Employee', type: 'select', options: employees, required: true },
    {
      name: 'revision_type',
      label: 'Revision type',
      type: 'select',
      options: REVISION_TYPES.map((t) => ({ value: t, label: t.replace(/_/g, ' ') })),
      required: true,
    },
    {
      name: 'structure_id',
      label: 'New structure (optional)',
      type: 'select',
      options: structures,
    },
    { name: 'effective_date', label: 'Effective date', type: 'date' },
    { name: 'reason', label: 'Reason', type: 'text' },
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
    { key: 'type', header: 'Type', render: (r) => String(r.revision_type).replace(/_/g, ' ') },
    { key: 'effective', header: 'Effective', render: (r) => String(r.effective_date ?? '—') },
    { key: 'reason', header: 'Reason', render: (r) => String(r.reason ?? '—') },
  ];

  return (
    <EntityManager<Ref>
      title="Salary Revisions"
      icon="arrow-trend-up"
      unitLabel="revisions"
      api={payrollApi.revisions}
      columns={columns}
      fields={fields}
      emptyForm={{
        staff_id: '',
        revision_type: 'annual_increment',
        structure_id: '',
        effective_date: '',
        reason: '',
      }}
      toForm={(r) => ({
        staff_id: String(r.staff_id),
        revision_type: String(r.revision_type ?? 'annual_increment'),
        structure_id: r.structure_id ? String(r.structure_id) : '',
        effective_date: (r.effective_date as string) ?? '',
        reason: r.reason ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
