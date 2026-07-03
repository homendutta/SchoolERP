/* CMS Notice Board — categories, priority, publish/expiry window, featured. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { CONTENT_STATUSES, NOTICE_PRIORITIES, cmsApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'green' | 'amber' | 'navy'> = {
  draft: 'gray',
  published: 'green',
  scheduled: 'amber',
  archived: 'navy',
};

export function NoticesPage() {
  const { user } = useAuth();
  const [categories, setCategories] = useState<FieldOption[]>([]);

  useEffect(() => {
    cmsApi.categories
      .list({ filter: { school_id: user?.school_id, type: 'notice' }, per_page: 200 })
      .then((r) =>
        setCategories(r.data.map((c) => ({ value: String(c.id), label: String(c.name) })))
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'title', label: 'Title', type: 'text', required: true },
    { name: 'body', label: 'Body', type: 'text' },
    {
      name: 'category_id',
      label: 'Category',
      type: 'select',
      options: [{ value: '', label: '—' }, ...categories],
    },
    {
      name: 'priority',
      label: 'Priority',
      type: 'select',
      options: NOTICE_PRIORITIES.map((p) => ({ value: p, label: p })),
    },
    { name: 'publish_date', label: 'Publish date', type: 'date' },
    { name: 'expiry_date', label: 'Expiry date', type: 'date' },
    { name: 'featured', label: 'Featured', type: 'checkbox' },
    { name: 'attachment_media_id', label: 'Attachment (Media id)', type: 'number' },
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
    { key: 'priority', header: 'Priority', render: (r) => String(r.priority) },
    { key: 'featured', header: 'Featured', render: (r) => (r.featured ? '★' : '') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Ref>
      title="Notice Board"
      icon="bullhorn"
      unitLabel="notices"
      api={cmsApi.notices}
      columns={columns}
      fields={fields}
      emptyForm={{
        title: '',
        body: '',
        category_id: '',
        priority: 'normal',
        publish_date: '',
        expiry_date: '',
        featured: false,
        attachment_media_id: '',
        status: 'draft',
      }}
      toForm={(r) => ({
        title: r.title,
        body: r.body ?? '',
        category_id: r.category_id ? String(r.category_id) : '',
        priority: String(r.priority ?? 'normal'),
        publish_date: (r.publish_date as string) ?? '',
        expiry_date: (r.expiry_date as string) ?? '',
        featured: Boolean(r.featured),
        attachment_media_id: (r.attachment_media_id as number) ?? '',
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search notices…"
      sort="id"
    />
  );
}
