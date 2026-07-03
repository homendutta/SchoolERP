/* CMS Downloads — prospectus, forms, circulars... (files are Media references). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { CONTENT_STATUSES, cmsApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'green' | 'amber' | 'navy'> = {
  draft: 'gray',
  published: 'green',
  scheduled: 'amber',
  archived: 'navy',
};

export function DownloadsPage() {
  const { user } = useAuth();
  const [categories, setCategories] = useState<FieldOption[]>([]);

  useEffect(() => {
    cmsApi.categories
      .list({ filter: { school_id: user?.school_id, type: 'download' }, per_page: 200 })
      .then((r) =>
        setCategories(r.data.map((c) => ({ value: String(c.id), label: String(c.name) })))
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'title', label: 'Title', type: 'text', required: true },
    { name: 'description', label: 'Description', type: 'text' },
    {
      name: 'category_id',
      label: 'Category',
      type: 'select',
      options: [{ value: '', label: '—' }, ...categories],
    },
    { name: 'media_id', label: 'File (Media id)', type: 'number' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: CONTENT_STATUSES.map((s) => ({ value: s, label: s })),
    },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'title',
      header: 'Title',
      render: (r) => <span className="font-medium">{String(r.title)}</span>,
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Ref>
      title="Downloads"
      icon="file-arrow-down"
      unitLabel="downloads"
      api={cmsApi.downloads}
      columns={columns}
      fields={fields}
      emptyForm={{ title: '', description: '', category_id: '', media_id: '', status: 'draft' }}
      toForm={(r) => ({
        title: r.title,
        description: r.description ?? '',
        category_id: r.category_id ? String(r.category_id) : '',
        media_id: (r.media_id as number) ?? '',
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search downloads…"
      sort="title"
    />
  );
}
