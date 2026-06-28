/* Fee Categories — schools define their own (Tuition, Transport…). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { financeApi, type FeeCategory } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'code', label: 'Code', type: 'text' },
  { name: 'description', label: 'Description', type: 'text' },
];

export function FeeCategoriesPage() {
  const { user } = useAuth();
  const columns: AXColumn<FeeCategory>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'code', header: 'Code', render: (r) => r.code ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={r.is_active ? 'green' : 'gray'}>
          {r.is_active ? 'Active' : 'Inactive'}
        </AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<FeeCategory>
      title="Fee Categories"
      icon="tags"
      unitLabel="categories"
      api={financeApi.categories}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '', description: '' }}
      toForm={(r) => ({ name: r.name, code: r.code ?? '', description: r.description ?? '' })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search categories…"
      sort="sort_order"
    />
  );
}
