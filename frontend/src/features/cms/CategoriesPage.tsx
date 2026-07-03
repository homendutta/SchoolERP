/* CMS Content Categories — shared across notices/news/gallery/videos/downloads. */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { CATEGORY_TYPES, cmsApi, type Ref } from './api';

export function CmsCategoriesPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Category name', type: 'text', required: true },
    {
      name: 'type',
      label: 'Applies to',
      type: 'select',
      options: CATEGORY_TYPES.map((t) => ({ value: t, label: t })),
      required: true,
    },
    { name: 'slug', label: 'Slug', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Name',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'type', header: 'Type', render: (r) => String(r.type) },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Content Categories"
      icon="tags"
      unitLabel="categories"
      api={cmsApi.categories}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', type: 'notice', slug: '' }}
      toForm={(r) => ({ name: r.name, type: String(r.type ?? 'notice'), slug: r.slug ?? '' })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search categories…"
      sort="name"
    />
  );
}
