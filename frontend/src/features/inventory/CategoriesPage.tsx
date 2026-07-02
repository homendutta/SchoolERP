/* Asset Categories — configurable, parent/child. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { inventoryApi, type Ref } from './api';

export function CategoriesPage() {
  const { user } = useAuth();
  const [parents, setParents] = useState<FieldOption[]>([]);

  useEffect(() => {
    inventoryApi.categories
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setParents(r.data.map((c) => ({ value: String(c.id), label: String(c.name) }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    {
      name: 'parent_id',
      label: 'Parent category',
      type: 'select',
      options: [{ value: '', label: '— (top level)' }, ...parents],
    },
    { name: 'description', label: 'Description', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Category',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'code', header: 'Code', render: (r) => String(r.code ?? '—') },
    {
      key: 'parent',
      header: 'Parent',
      render: (r) => String((r.parent as { name?: string })?.name ?? '—'),
    },
  ];

  return (
    <EntityManager<Ref>
      title="Asset Categories"
      icon="sitemap"
      unitLabel="categories"
      api={inventoryApi.categories}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '', parent_id: '', description: '' }}
      toForm={(r) => ({
        name: String(r.name),
        code: (r.code as string) ?? '',
        parent_id: r.parent_id ? String(r.parent_id) : '',
        description: (r.description as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search categories…"
      sort="name"
    />
  );
}
