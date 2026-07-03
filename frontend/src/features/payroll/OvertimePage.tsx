/* Overtime — payroll only calculates APPROVED overtime. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { payrollApi, type Ref } from './api';

export function OvertimePage() {
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
    { name: 'period_year', label: 'Year', type: 'number', required: true },
    { name: 'period_month', label: 'Month (1-12)', type: 'number', required: true },
    { name: 'hours', label: 'Hours', type: 'number' },
    { name: 'hourly_rate', label: 'Hourly rate', type: 'number' },
    { name: 'max_hours', label: 'Max hours', type: 'number' },
    { name: 'approved', label: 'Approved', type: 'checkbox' },
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
      key: 'period',
      header: 'Period',
      render: (r) => `${r.period_year}-${String(r.period_month).padStart(2, '0')}`,
    },
    { key: 'hours', header: 'Hours', render: (r) => String(r.hours ?? 0) },
    { key: 'rate', header: 'Rate', render: (r) => String(r.hourly_rate ?? 0) },
    { key: 'approved', header: 'Approved', render: (r) => (r.approved ? '✓' : '—') },
  ];

  return (
    <EntityManager<Ref>
      title="Overtime"
      icon="business-time"
      unitLabel="entries"
      api={payrollApi.overtime}
      columns={columns}
      fields={fields}
      emptyForm={{
        staff_id: '',
        period_year: new Date().getFullYear(),
        period_month: new Date().getMonth() + 1,
        hours: 0,
        hourly_rate: 0,
        max_hours: '',
        approved: false,
      }}
      toForm={(r) => ({
        staff_id: String(r.staff_id),
        period_year: (r.period_year as number) ?? new Date().getFullYear(),
        period_month: (r.period_month as number) ?? 1,
        hours: (r.hours as number) ?? 0,
        hourly_rate: (r.hourly_rate as number) ?? 0,
        max_hours: (r.max_hours as number) ?? '',
        approved: Boolean(r.approved),
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
