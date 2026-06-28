/* Discounts — configurable (Merit, Sports, Staff Child…). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { DISCOUNT_METHODS, financeApi, type Discount, type SiblingRule } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'code', label: 'Code', type: 'text' },
  {
    name: 'method',
    label: 'Method',
    type: 'select',
    options: DISCOUNT_METHODS.map((m) => ({ value: m, label: m })),
  },
  { name: 'value', label: 'Value', type: 'number', required: true },
  { name: 'description', label: 'Description', type: 'text' },
];

const siblingFields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'child_order', label: 'Child order (2 = 2nd child)', type: 'number', required: true },
  {
    name: 'method',
    label: 'Method',
    type: 'select',
    options: DISCOUNT_METHODS.map((m) => ({ value: m, label: m })),
  },
  { name: 'value', label: 'Value', type: 'number', required: true },
];

export function DiscountsPage() {
  const { user } = useAuth();
  const columns: AXColumn<Discount>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'method', header: 'Method', render: (r) => <AXBadge tone="navy">{r.method}</AXBadge> },
    {
      key: 'value',
      header: 'Value',
      render: (r) => (r.method === 'percentage' ? `${r.value}%` : `₹${r.value}`),
    },
  ];

  const siblingColumns: AXColumn<SiblingRule>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'order', header: 'Child order', render: (r) => r.child_order },
    {
      key: 'value',
      header: 'Value',
      render: (r) => (r.method === 'percentage' ? `${r.value}%` : `₹${r.value}`),
    },
  ];

  return (
    <div className="space-y-6">
      <EntityManager<Discount>
        title="Discounts"
        icon="percent"
        unitLabel="discounts"
        api={financeApi.discounts}
        columns={columns}
        fields={fields}
        emptyForm={{ name: '', code: '', method: 'percentage', value: 0, description: '' }}
        toForm={(r) => ({
          name: r.name,
          code: r.code ?? '',
          method: r.method,
          value: r.value,
          description: '',
        })}
        createDefaults={{ school_id: user?.school_id }}
        searchKey="name"
        searchPlaceholder="Search discounts…"
        sort="name"
      />

      <EntityManager<SiblingRule>
        title="Sibling Concession Rules"
        icon="children"
        unitLabel="sibling rules"
        api={financeApi.siblingRules}
        columns={siblingColumns}
        fields={siblingFields}
        emptyForm={{ name: '', child_order: 2, method: 'percentage', value: 0 }}
        toForm={(r) => ({
          name: r.name,
          child_order: r.child_order,
          method: r.method,
          value: r.value,
        })}
        createDefaults={{ school_id: user?.school_id }}
        sort="child_order"
      />
    </div>
  );
}
