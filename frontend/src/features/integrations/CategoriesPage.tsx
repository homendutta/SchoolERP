/* Integration Categories — Authentication, Payment, Communication, ... */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { integrationsApi, type Ref } from './api';

export function CategoriesPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Category name', type: 'text', required: true },
    { name: 'code', label: 'Code (payment, communication, ...)', type: 'text', required: true },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Category',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    {
      key: 'code',
      header: 'Code',
      render: (r) => <code className="text-xs text-gray-500">{String(r.code)}</code>,
    },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Integration Categories"
      icon="layer-group"
      unitLabel="categories"
      api={integrationsApi.categories}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '' }}
      toForm={(r) => ({ name: r.name, code: String(r.code ?? '') })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search categories…"
      sort="name"
    />
  );
}
