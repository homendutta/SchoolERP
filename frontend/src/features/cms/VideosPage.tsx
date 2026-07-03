/* CMS Video Gallery — YouTube / Vimeo / self-hosted references. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { CONTENT_STATUSES, VIDEO_PROVIDERS, cmsApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'green' | 'amber' | 'navy'> = {
  draft: 'gray',
  published: 'green',
  scheduled: 'amber',
  archived: 'navy',
};

export function VideosPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'title', label: 'Title', type: 'text', required: true },
    {
      name: 'provider',
      label: 'Provider',
      type: 'select',
      options: VIDEO_PROVIDERS.map((p) => ({ value: p, label: p })),
    },
    { name: 'video_url', label: 'Video URL', type: 'text' },
    { name: 'media_id', label: 'Self-hosted (Media id)', type: 'number' },
    { name: 'thumbnail_media_id', label: 'Thumbnail (Media id)', type: 'number' },
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
    { key: 'provider', header: 'Provider', render: (r) => String(r.provider) },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Ref>
      title="Video Gallery"
      icon="film"
      unitLabel="videos"
      api={cmsApi.videos}
      columns={columns}
      fields={fields}
      emptyForm={{
        title: '',
        provider: 'youtube',
        video_url: '',
        media_id: '',
        thumbnail_media_id: '',
        featured: false,
        status: 'draft',
      }}
      toForm={(r) => ({
        title: r.title,
        provider: String(r.provider ?? 'youtube'),
        video_url: r.video_url ?? '',
        media_id: (r.media_id as number) ?? '',
        thumbnail_media_id: (r.thumbnail_media_id as number) ?? '',
        featured: Boolean(r.featured),
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search videos…"
      sort="id"
    />
  );
}
