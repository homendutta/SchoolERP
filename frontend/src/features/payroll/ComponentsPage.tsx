/* Salary Components — configurable earning/deduction/employer/informational. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { CALCULATION_TYPES, COMPONENT_TYPES, payrollApi, type Ref } from './api';

const TONES: Record<string, 'green' | 'red' | 'navy' | 'gray'> = {
  earning: 'green',
  deduction: 'red',
  employer_contribution: 'navy',
  informational: 'gray',
};

export function ComponentsPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Component name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    {
      name: 'component_type',
      label: 'Type',
      type: 'select',
      options: COMPONENT_TYPES.map((t) => ({ value: t, label: t.replace(/_/g, ' ') })),
      required: true,
    },
    {
      name: 'calculation_type',
      label: 'Calculation',
      type: 'select',
      options: CALCULATION_TYPES.map((t) => ({ value: t, label: t })),
    },
    { name: 'default_value', label: 'Default value', type: 'number' },
    { name: 'based_on', label: 'Based on (code, for %)', type: 'text' },
    { name: 'taxable', label: 'Taxable', type: 'checkbox' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Component',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    {
      key: 'type',
      header: 'Type',
      render: (r) => (
        <AXBadge tone={TONES[String(r.component_type)] ?? 'gray'}>
          {String(r.component_type).replace(/_/g, ' ')}
        </AXBadge>
      ),
    },
    { key: 'calc', header: 'Calc', render: (r) => String(r.calculation_type) },
    { key: 'value', header: 'Value', render: (r) => String(r.default_value ?? 0) },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Salary Components"
      icon="puzzle-piece"
      unitLabel="components"
      api={payrollApi.components}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        code: '',
        component_type: 'earning',
        calculation_type: 'fixed',
        default_value: 0,
        based_on: '',
        taxable: false,
      }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        component_type: String(r.component_type ?? 'earning'),
        calculation_type: String(r.calculation_type ?? 'fixed'),
        default_value: (r.default_value as number) ?? 0,
        based_on: r.based_on ?? '',
        taxable: Boolean(r.taxable),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search components…"
      sort="name"
    />
  );
}
