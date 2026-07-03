/* CMS Pages — formerly-static pages made dynamic, with per-page SEO + publishing. */
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

/** Fold the flat SEO form fields back into the nested `seo` payload. */
function toPayload(d: Record<string, unknown>): Record<string, unknown> {
  const { meta_title, meta_description, ...rest } = d;
  return { ...rest, seo: { meta_title, meta_description } };
}

export function PagesPage() {
  const { user } = useAuth();

  const api = useMemo(
    () => ({
      ...cmsApi.pages,
      create: (d: Record<string, unknown>) => cmsApi.pages.create(toPayload(d)),
      update: (id: number, d: Record<string, unknown>) => cmsApi.pages.update(id, toPayload(d)),
    }),
    []
  );

  const fields: Field[] = [
    { name: 'title', label: 'Title', type: 'text', required: true },
    { name: 'slug', label: 'Slug', type: 'text', required: true },
    { name: 'body', label: 'Body (HTML)', type: 'text' },
    { name: 'meta_title', label: 'SEO meta title', type: 'text' },
    { name: 'meta_description', label: 'SEO meta description', type: 'text' },
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
      key: 'slug',
      header: 'Slug',
      render: (r) => <code className="text-xs text-gray-500">{String(r.slug)}</code>,
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
  ];

  const seoOf = (r: Ref) => (r.seo as Record<string, string> | null) ?? {};

  return (
    <EntityManager<Ref>
      title="Pages"
      icon="file-lines"
      unitLabel="pages"
      api={api}
      columns={columns}
      fields={fields}
      emptyForm={{
        title: '',
        slug: '',
        body: '',
        meta_title: '',
        meta_description: '',
        status: 'draft',
      }}
      toForm={(r) => ({
        title: r.title,
        slug: r.slug,
        body: r.body ?? '',
        meta_title: seoOf(r).meta_title ?? '',
        meta_description: seoOf(r).meta_description ?? '',
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search pages…"
      sort="title"
    />
  );
}
