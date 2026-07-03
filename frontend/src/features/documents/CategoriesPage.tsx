/* Document Categories — configurable (Student, Staff, Academic, ...). */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { documentsApi, type Ref } from './api';

export function CategoriesPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Category name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Category',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'code', header: 'Code', render: (r) => String(r.code ?? '—') },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Document Categories"
      icon="folder-tree"
      unitLabel="categories"
      api={documentsApi.categories}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '' }}
      toForm={(r) => ({ name: r.name, code: r.code ?? '' })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search categories…"
      sort="name"
    />
  );
}
