/* Scholarships — independent of discounts; configurable. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { DISCOUNT_METHODS, SCHOLARSHIP_TYPES, financeApi, type Scholarship } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'code', label: 'Code', type: 'text' },
  {
    name: 'type',
    label: 'Type',
    type: 'select',
    options: SCHOLARSHIP_TYPES.map((t) => ({ value: t, label: t })),
  },
  {
    name: 'method',
    label: 'Method',
    type: 'select',
    options: DISCOUNT_METHODS.map((m) => ({ value: m, label: m })),
  },
  { name: 'value', label: 'Value', type: 'number' },
  { name: 'description', label: 'Description', type: 'text' },
];

export function ScholarshipsPage() {
  const { user } = useAuth();
  const columns: AXColumn<Scholarship>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    {
      key: 'type',
      header: 'Type',
      render: (r) => <AXBadge tone={r.type === 'full' ? 'green' : 'amber'}>{r.type}</AXBadge>,
    },
    {
      key: 'value',
      header: 'Value',
      render: (r) =>
        r.type === 'full' ? '100%' : r.method === 'percentage' ? `${r.value}%` : `₹${r.value}`,
    },
  ];

  return (
    <EntityManager<Scholarship>
      title="Scholarships"
      icon="graduation-cap"
      unitLabel="scholarships"
      api={financeApi.scholarships}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        code: '',
        type: 'partial',
        method: 'percentage',
        value: 0,
        description: '',
      }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        type: r.type,
        method: r.method,
        value: r.value,
        description: '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search scholarships…"
      sort="name"
    />
  );
}
