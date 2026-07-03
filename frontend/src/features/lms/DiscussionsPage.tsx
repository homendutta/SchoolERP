/* Classroom Discussions — LMS teacher content (auto-generated CRUD page). */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { lmsApi, type Ref } from './api';

export function DiscussionsPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'subject_id', label: 'Subject id', type: 'number' },
    { name: 'class_id', label: 'Class id', type: 'number' },
    { name: 'section_id', label: 'Section id', type: 'number' },
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'body', label: 'Body', type: 'text' },
    { name: 'locked', label: 'Locked', type: 'checkbox' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'title',
      header: 'Topic',
      render: (r) => <span className="font-medium">{String(r.title)}</span>,
    },
    { key: 'locked', header: 'Locked', render: (r) => (r.locked ? '🔒' : '') },
  ];

  return (
    <EntityManager<Ref>
      title="Classroom Discussions"
      icon="comments"
      unitLabel="items"
      api={lmsApi.discussions}
      columns={columns}
      fields={fields}
      emptyForm={{
        subject_id: '',
        class_id: '',
        section_id: '',
        title: '',
        body: '',
        locked: false,
      }}
      toForm={(r) => ({
        subject_id: (r.subject_id as number) ?? '',
        class_id: (r.class_id as number) ?? '',
        section_id: (r.section_id as number) ?? '',
        title: (r.title as string) ?? '',
        body: (r.body as string) ?? '',
        locked: Boolean(r.locked),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search…"
      sort="id"
    />
  );
}
