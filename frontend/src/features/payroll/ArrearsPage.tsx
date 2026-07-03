/* Arrears — salary / adjustment; applied during payroll processing. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { ARREAR_TYPES, payrollApi, type Ref } from './api';

export function ArrearsPage() {
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
      name: 'arrear_type',
      label: 'Type',
      type: 'select',
      options: ARREAR_TYPES.map((t) => ({ value: t, label: t })),
    },
    { name: 'amount', label: 'Amount', type: 'number', required: true },
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
    { key: 'type', header: 'Type', render: (r) => String(r.arrear_type) },
    { key: 'amount', header: 'Amount', render: (r) => String(r.amount ?? 0) },
    { key: 'applied', header: 'Applied', render: (r) => (r.applied ? '✓' : '—') },
  ];

  return (
    <EntityManager<Ref>
      title="Arrears"
      icon="clock-rotate-left"
      unitLabel="arrears"
      api={payrollApi.arrears}
      columns={columns}
      fields={fields}
      emptyForm={{ staff_id: '', arrear_type: 'salary', amount: 0, reason: '' }}
      toForm={(r) => ({
        staff_id: String(r.staff_id),
        arrear_type: String(r.arrear_type ?? 'salary'),
        amount: (r.amount as number) ?? 0,
        reason: r.reason ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
