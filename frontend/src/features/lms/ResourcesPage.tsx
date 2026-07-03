/* Classroom Resources — LMS teacher content (auto-generated CRUD page). */
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

export function ResourcesPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'subject_id', label: 'Subject id', type: 'number' },
    { name: 'class_id', label: 'Class id', type: 'number' },
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'topic', label: 'Topic', type: 'text' },
    { name: 'type', label: 'Type (notes/worksheet/...)', type: 'text' },
    { name: 'body', label: 'Body', type: 'text' },
    { name: 'media_id', label: 'File (Media id)', type: 'number' },
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
      title="Classroom Resources"
      icon="box-archive"
      unitLabel="items"
      api={lmsApi.resources}
      columns={columns}
      fields={fields}
      emptyForm={{
        subject_id: '',
        class_id: '',
        title: '',
        topic: '',
        type: '',
        body: '',
        media_id: '',
        status: 'draft',
      }}
      toForm={(r) => ({
        subject_id: (r.subject_id as number) ?? '',
        class_id: (r.class_id as number) ?? '',
        title: (r.title as string) ?? '',
        topic: (r.topic as string) ?? '',
        type: (r.type as string) ?? '',
        body: (r.body as string) ?? '',
        media_id: (r.media_id as number) ?? '',
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search…"
      sort="id"
    />
  );
}
