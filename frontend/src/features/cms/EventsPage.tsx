/* CMS Events — date/time/venue, registration flag (no ticketing). */
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

export function EventsPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'title', label: 'Title', type: 'text', required: true },
    { name: 'description', label: 'Description', type: 'text' },
    { name: 'event_date', label: 'Date', type: 'date' },
    { name: 'start_time', label: 'Start time', type: 'text' },
    { name: 'end_time', label: 'End time', type: 'text' },
    { name: 'venue', label: 'Venue', type: 'text' },
    { name: 'featured_image_media_id', label: 'Featured image (Media id)', type: 'number' },
    { name: 'registration_required', label: 'Registration required', type: 'checkbox' },
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
    { key: 'date', header: 'Date', render: (r) => String(r.event_date ?? '—') },
    { key: 'venue', header: 'Venue', render: (r) => String(r.venue ?? '—') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Ref>
      title="Events"
      icon="calendar-days"
      unitLabel="events"
      api={cmsApi.events}
      columns={columns}
      fields={fields}
      emptyForm={{
        title: '',
        description: '',
        event_date: '',
        start_time: '',
        end_time: '',
        venue: '',
        featured_image_media_id: '',
        registration_required: false,
        status: 'draft',
      }}
      toForm={(r) => ({
        title: r.title,
        description: r.description ?? '',
        event_date: (r.event_date as string) ?? '',
        start_time: r.start_time ?? '',
        end_time: r.end_time ?? '',
        venue: r.venue ?? '',
        featured_image_media_id: (r.featured_image_media_id as number) ?? '',
        registration_required: Boolean(r.registration_required),
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search events…"
      sort="id"
    />
  );
}
