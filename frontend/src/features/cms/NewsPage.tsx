/* CMS News — featured image, excerpt, scheduled publishing. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { CONTENT_STATUSES, cmsApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'green' | 'amber' | 'navy'> = {
  draft: 'gray',
  published: 'green',
  scheduled: 'amber',
  archived: 'navy',
};

export function NewsPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'title', label: 'Title', type: 'text', required: true },
    { name: 'slug', label: 'Slug', type: 'text', required: true },
    { name: 'excerpt', label: 'Excerpt', type: 'text' },
    { name: 'body', label: 'Body (HTML)', type: 'text' },
    { name: 'featured_image_media_id', label: 'Featured image (Media id)', type: 'number' },
    { name: 'publish_date', label: 'Publish date', type: 'date' },
    { name: 'scheduled_at', label: 'Scheduled at', type: 'date' },
    { name: 'featured', label: 'Featured', type: 'checkbox' },
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
    { key: 'featured', header: 'Featured', render: (r) => (r.featured ? '★' : '') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Ref>
      title="News"
      icon="newspaper"
      unitLabel="articles"
      api={cmsApi.news}
      columns={columns}
      fields={fields}
      emptyForm={{
        title: '',
        slug: '',
        excerpt: '',
        body: '',
        featured_image_media_id: '',
        publish_date: '',
        scheduled_at: '',
        featured: false,
        status: 'draft',
      }}
      toForm={(r) => ({
        title: r.title,
        slug: r.slug,
        excerpt: r.excerpt ?? '',
        body: r.body ?? '',
        featured_image_media_id: (r.featured_image_media_id as number) ?? '',
        publish_date: (r.publish_date as string) ?? '',
        scheduled_at: (r.scheduled_at as string) ?? '',
        featured: Boolean(r.featured),
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search news…"
      sort="id"
    />
  );
}
