/* Lessons — LMS teacher content (auto-generated CRUD page). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { LMS_STATUSES, lmsApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'green' | 'amber' | 'navy'> = {
  draft: 'gray',
  published: 'green',
  scheduled: 'amber',
  archived: 'navy',
};

export function LessonsPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'lesson_plan_id', label: 'Lesson plan id', type: 'number' },
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'body', label: 'Body (rich text)', type: 'text' },
    { name: 'estimated_duration', label: 'Duration (min)', type: 'number' },
    { name: 'reading_time', label: 'Reading time (min)', type: 'number' },
    { name: 'scheduled_at', label: 'Scheduled at', type: 'date' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: LMS_STATUSES.map((s) => ({ value: s, label: s })),
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
      title="Lessons"
      icon="book-open"
      unitLabel="items"
      api={lmsApi.lessons}
      columns={columns}
      fields={fields}
      emptyForm={{
        lesson_plan_id: '',
        title: '',
        body: '',
        estimated_duration: '',
        reading_time: '',
        scheduled_at: '',
        status: 'draft',
      }}
      toForm={(r) => ({
        lesson_plan_id: (r.lesson_plan_id as number) ?? '',
        title: (r.title as string) ?? '',
        body: (r.body as string) ?? '',
        estimated_duration: (r.estimated_duration as number) ?? '',
        reading_time: (r.reading_time as number) ?? '',
        scheduled_at: (r.scheduled_at as string) ?? '',
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search…"
      sort="id"
    />
  );
}
