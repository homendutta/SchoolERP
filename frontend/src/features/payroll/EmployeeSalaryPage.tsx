/* Employee Salary — assign a structure to an employee. Each assignment is a new
 * immutable version (history never overwritten). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { payrollApi, type Ref } from './api';

export function EmployeeSalaryPage() {
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
      name: 'structure_id',
      label: 'Salary structure',
      type: 'select',
      options: structures,
      required: true,
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
    {
      key: 'structure',
      header: 'Structure',
      render: (r) => String((r.structure as { name?: string })?.name ?? '—'),
    },
    { key: 'rev', header: 'Revision', render: (r) => String(r.revision_number ?? 1) },
    { key: 'current', header: 'Current', render: (r) => (r.is_current ? '✓' : '') },
    { key: 'effective', header: 'Effective', render: (r) => String(r.effective_date ?? '—') },
  ];

  return (
    <EntityManager<Ref>
      title="Employee Salary"
      icon="money-check"
      unitLabel="assignments"
      api={payrollApi.assignments}
      columns={columns}
      fields={fields}
      emptyForm={{ staff_id: '', structure_id: '', effective_date: '', reason: '' }}
      toForm={(r) => ({
        staff_id: String(r.staff_id),
        structure_id: String(r.structure_id),
        effective_date: (r.effective_date as string) ?? '',
        reason: r.reason ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
