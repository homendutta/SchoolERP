/* CMS Photo Gallery — albums of Media images (comma-separated Media ids). */
import { useMemo } from 'react';
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

/** Turn the comma-separated media-id field into the images[] payload. */
function toPayload(d: Record<string, unknown>): Record<string, unknown> {
  const { image_media_ids, ...rest } = d;
  const ids = String(image_media_ids ?? '')
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean)
    .map((id, i) => ({ media_id: Number(id), sequence: i }));
  return { ...rest, images: ids };
}

export function GalleryPage() {
  const { user } = useAuth();

  const api = useMemo(
    () => ({
      ...cmsApi.gallery,
      create: (d: Record<string, unknown>) => cmsApi.gallery.create(toPayload(d)),
      update: (id: number, d: Record<string, unknown>) => cmsApi.gallery.update(id, toPayload(d)),
    }),
    []
  );

  const fields: Field[] = [
    { name: 'title', label: 'Album title', type: 'text', required: true },
    { name: 'description', label: 'Description', type: 'text' },
    { name: 'cover_media_id', label: 'Cover (Media id)', type: 'number' },
    { name: 'image_media_ids', label: 'Image Media ids (comma-separated)', type: 'text' },
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
      header: 'Album',
      render: (r) => <span className="font-medium">{String(r.title)}</span>,
    },
    {
      key: 'images',
      header: 'Images',
      render: (r) => String(Array.isArray(r.images) ? r.images.length : 0),
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
      title="Photo Gallery"
      icon="images"
      unitLabel="albums"
      api={api}
      columns={columns}
      fields={fields}
      emptyForm={{
        title: '',
        description: '',
        cover_media_id: '',
        image_media_ids: '',
        featured: false,
        status: 'draft',
      }}
      toForm={(r) => ({
        title: r.title,
        description: r.description ?? '',
        cover_media_id: (r.cover_media_id as number) ?? '',
        image_media_ids: Array.isArray(r.images)
          ? (r.images as Array<{ media_id: number }>).map((i) => i.media_id).join(', ')
          : '',
        featured: Boolean(r.featured),
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search albums…"
      sort="id"
    />
  );
}
