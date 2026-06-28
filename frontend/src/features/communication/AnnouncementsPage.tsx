/* Announcements — created here and fanned out through the Communication Engine. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { useClasses } from '@features/academic/useReference';
import { AUDIENCE_TYPES, communicationApi, type Announcement } from './api';

export function AnnouncementsPage() {
  const { user } = useAuth();
  const classes = useClasses();

  const fields: Field[] = [
    { name: 'title', label: 'Title', type: 'text', required: true },
    { name: 'body', label: 'Body', type: 'text', required: true },
    {
      name: 'audience_type',
      label: 'Audience',
      type: 'select',
      options: AUDIENCE_TYPES.map((a) => ({ value: a, label: a })),
    },
    {
      name: 'class_id',
      label: 'Class (if class audience)',
      type: 'select',
      options: [{ value: '', label: '—' }, ...classes],
    },
  ];

  const columns: AXColumn<Announcement>[] = [
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
    { key: 'published', header: 'Published', render: (r) => r.published_at?.slice(0, 10) ?? '—' },
  ];

  return (
    <EntityManager<Announcement>
      title="Announcements"
      icon="bullhorn"
      unitLabel="announcements"
      api={communicationApi.announcements}
      columns={columns}
      fields={fields}
      emptyForm={{ title: '', body: '', audience_type: 'school', class_id: '' }}
      toForm={(r) => ({
        title: r.title,
        body: r.body,
        audience_type: r.audience_type,
        class_id: r.class_id ? String(r.class_id) : '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search announcements…"
      sort="id"
      canCreate
    />
  );
}
