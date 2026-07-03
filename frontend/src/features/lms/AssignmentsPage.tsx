/* Assignments — LMS teacher content (auto-generated CRUD page). */
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

export function AssignmentsPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'subject_id', label: 'Subject id', type: 'number' },
    { name: 'class_id', label: 'Class id', type: 'number' },
    { name: 'section_id', label: 'Section id', type: 'number' },
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'description', label: 'Description', type: 'text' },
    { name: 'instructions', label: 'Instructions', type: 'text' },
    { name: 'publish_date', label: 'Publish date', type: 'date' },
    { name: 'due_date', label: 'Due date', type: 'date' },
    { name: 'max_marks', label: 'Max marks', type: 'number' },
    { name: 'allow_late', label: 'Allow late', type: 'checkbox' },
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
    { key: 'due', header: 'Due', render: (r) => String(r.due_date ?? '—') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Ref>
      title="Assignments"
      icon="file-signature"
      unitLabel="items"
      api={lmsApi.assignments}
      columns={columns}
      fields={fields}
      emptyForm={{
        subject_id: '',
        class_id: '',
        section_id: '',
        title: '',
        description: '',
        instructions: '',
        publish_date: '',
        due_date: '',
        max_marks: '',
        allow_late: false,
        status: 'draft',
      }}
      toForm={(r) => ({
        subject_id: (r.subject_id as number) ?? '',
        class_id: (r.class_id as number) ?? '',
        section_id: (r.section_id as number) ?? '',
        title: (r.title as string) ?? '',
        description: (r.description as string) ?? '',
        instructions: (r.instructions as string) ?? '',
        publish_date: (r.publish_date as string) ?? '',
        due_date: (r.due_date as string) ?? '',
        max_marks: (r.max_marks as number) ?? '',
        allow_late: Boolean(r.allow_late),
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search…"
      sort="id"
    />
  );
}
