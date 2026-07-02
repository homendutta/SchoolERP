/* Library Categories — configurable (Science, Literature…). */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { libraryApi, type Ref } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'code', label: 'Code', type: 'text' },
  { name: 'description', label: 'Description', type: 'text' },
];

export function CategoriesPage() {
  const { user } = useAuth();
  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Category',
      render: (r) => <span className="font-medium">{r.name}</span>,
    },
    { key: 'code', header: 'Code', render: (r) => r.code ?? '—' },
  ];

  return (
    <EntityManager<Ref>
      title="Categories"
      icon="tags"
      unitLabel="categories"
      api={libraryApi.categories}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '', description: '' }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        description: (r.description as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search categories…"
      sort="name"
    />
  );
}
