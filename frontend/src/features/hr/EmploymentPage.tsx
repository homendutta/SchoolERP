/* Employment history — every change creates a NEW record (history never overwritten). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES, hrApi, type Ref } from './api';

const TONES: Record<string, 'green' | 'amber' | 'red' | 'gray'> = {
  active: 'green',
  on_leave: 'amber',
  suspended: 'red',
  separated: 'gray',
};

export function EmploymentPage() {
  const { user } = useAuth();
  const [employees, setEmployees] = useState<FieldOption[]>([]);
  const [departments, setDepartments] = useState<FieldOption[]>([]);
  const [designations, setDesignations] = useState<FieldOption[]>([]);

  useEffect(() => {
    const f = { filter: { school_id: user?.school_id }, per_page: 500 };
    staffApi.staff
      .list({ per_page: 500, sort: 'name' })
      .then((r) =>
        setEmployees(
          r.data.map((s) => ({ value: String(s.id), label: `${s.employee_number} — ${s.name}` }))
        )
      );
    hrApi.departments
      .list(f)
      .then((r) =>
        setDepartments(r.data.map((d) => ({ value: String(d.id), label: String(d.name) })))
      );
    hrApi.designations
      .list(f)
      .then((r) =>
        setDesignations(r.data.map((d) => ({ value: String(d.id), label: String(d.name) })))
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'staff_id', label: 'Employee', type: 'select', options: employees, required: true },
    { name: 'department_id', label: 'Department', type: 'select', options: departments },
    { name: 'designation_id', label: 'Designation', type: 'select', options: designations },
    {
      name: 'employment_type',
      label: 'Employment type',
      type: 'select',
      options: EMPLOYMENT_TYPES.map((t) => ({ value: t, label: t.replace('_', ' ') })),
    },
    { name: 'joining_date', label: 'Joining date', type: 'date' },
    { name: 'confirmation_date', label: 'Confirmation date', type: 'date' },
    { name: 'contract_start', label: 'Contract start', type: 'date' },
    { name: 'contract_end', label: 'Contract end', type: 'date' },
    {
      name: 'reporting_manager_id',
      label: 'Reporting manager',
      type: 'select',
      options: employees,
    },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: EMPLOYMENT_STATUSES.map((s) => ({ value: s, label: s.replace('_', ' ') })),
    },
    { name: 'change_reason', label: 'Change reason', type: 'text' },
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
      key: 'department',
      header: 'Department',
      render: (r) => String((r.department as { name?: string })?.name ?? '—'),
    },
    {
      key: 'designation',
      header: 'Designation',
      render: (r) => String((r.designation as { name?: string })?.name ?? '—'),
    },
    { key: 'current', header: 'Current', render: (r) => (r.is_current ? '✓' : '') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>
          {String(r.status).replace('_', ' ')}
        </AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<Ref>
      title="Employment"
      icon="briefcase"
      unitLabel="records"
      api={hrApi.employment}
      columns={columns}
      fields={fields}
      emptyForm={{
        staff_id: '',
        department_id: '',
        designation_id: '',
        employment_type: 'full_time',
        joining_date: '',
        confirmation_date: '',
        contract_start: '',
        contract_end: '',
        reporting_manager_id: '',
        status: 'active',
        change_reason: '',
      }}
      toForm={(r) => ({
        staff_id: String(r.staff_id),
        department_id: r.department_id ? String(r.department_id) : '',
        designation_id: r.designation_id ? String(r.designation_id) : '',
        employment_type: String(r.employment_type ?? ''),
        joining_date: (r.joining_date as string) ?? '',
        confirmation_date: (r.confirmation_date as string) ?? '',
        contract_start: (r.contract_start as string) ?? '',
        contract_end: (r.contract_end as string) ?? '',
        reporting_manager_id: r.reporting_manager_id ? String(r.reporting_manager_id) : '',
        status: String(r.status ?? 'active'),
        change_reason: '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
