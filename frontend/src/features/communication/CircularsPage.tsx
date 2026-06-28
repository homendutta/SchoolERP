/* Circulars — title + body + Media attachment (reference, never a path). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { AUDIENCE_TYPES, communicationApi, type Circular } from './api';

const fields: Field[] = [
  { name: 'title', label: 'Title', type: 'text', required: true },
  { name: 'body', label: 'Body', type: 'text', required: true },
  {
    name: 'audience_type',
    label: 'Audience',
    type: 'select',
    options: AUDIENCE_TYPES.map((a) => ({ value: a, label: a })),
  },
  { name: 'media_id', label: 'Attachment media ID (optional)', type: 'number' },
  { name: 'publish_date', label: 'Publish date', type: 'date' },
  { name: 'expiry_date', label: 'Expiry date', type: 'date' },
];

export function CircularsPage() {
  const { user } = useAuth();
  const columns: AXColumn<Circular>[] = [
    {
      key: 'title',
      header: 'Title',
      render: (r) => <span className="font-medium">{r.title}</span>,
    },
    {
      key: 'audience',
      header: 'Audience',
      render: (r) => <AXBadge tone="navy">{r.audience_type}</AXBadge>,
    },
    {
      key: 'media',
      header: 'Attachment',
      render: (r) => (r.media_id ? <AXBadge tone="green">attached</AXBadge> : '—'),
    },
    { key: 'expiry', header: 'Expiry', render: (r) => r.expiry_date ?? '—' },
  ];

  return (
    <EntityManager<Circular>
      title="Circulars"
      icon="file-pdf"
      unitLabel="circulars"
      api={communicationApi.circulars}
      columns={columns}
      fields={fields}
      emptyForm={{
        title: '',
        body: '',
        audience_type: 'school',
        media_id: '',
        publish_date: '',
        expiry_date: '',
      }}
      toForm={(r) => ({
        title: r.title,
        body: r.body,
        audience_type: r.audience_type,
        media_id: r.media_id ?? '',
        publish_date: r.publish_date ?? '',
        expiry_date: r.expiry_date ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search circulars…"
      sort="id"
    />
  );
}
