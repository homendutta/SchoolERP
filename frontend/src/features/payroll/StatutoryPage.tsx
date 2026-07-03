/* Statutory Components — configurable PF/ESI/PT/TDS/Other (config only, no rates hardcoded). */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { CALCULATION_TYPES, STATUTORY_TYPES, payrollApi, type Ref } from './api';

export function StatutoryPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    {
      name: 'statutory_type',
      label: 'Type',
      type: 'select',
      options: STATUTORY_TYPES.map((t) => ({ value: t, label: t.replace(/_/g, ' ') })),
      required: true,
    },
    {
      name: 'calculation_type',
      label: 'Calculation',
      type: 'select',
      options: CALCULATION_TYPES.filter((c) => c !== 'formula').map((t) => ({
        value: t,
        label: t,
      })),
    },
    { name: 'employee_rate', label: 'Employee rate', type: 'number' },
    { name: 'employer_rate', label: 'Employer rate', type: 'number' },
    { name: 'based_on', label: 'Based on (basic/gross)', type: 'text' },
    { name: 'wage_ceiling', label: 'Wage ceiling', type: 'number' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Name',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'type', header: 'Type', render: (r) => String(r.statutory_type).replace(/_/g, ' ') },
    { key: 'emp', header: 'Employee %', render: (r) => String(r.employee_rate ?? 0) },
    { key: 'er', header: 'Employer %', render: (r) => String(r.employer_rate ?? 0) },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Statutory Components"
      icon="landmark"
      unitLabel="components"
      api={payrollApi.statutory}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        code: '',
        statutory_type: 'pf',
        calculation_type: 'percentage',
        employee_rate: 0,
        employer_rate: 0,
        based_on: 'basic',
        wage_ceiling: '',
      }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        statutory_type: String(r.statutory_type ?? 'pf'),
        calculation_type: String(r.calculation_type ?? 'percentage'),
        employee_rate: (r.employee_rate as number) ?? 0,
        employer_rate: (r.employer_rate as number) ?? 0,
        based_on: r.based_on ?? 'basic',
        wage_ceiling: (r.wage_ceiling as number) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search statutory…"
      sort="name"
    />
  );
}
