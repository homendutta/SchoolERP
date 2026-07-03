/* Quizzes — LMS teacher content (auto-generated CRUD page). */
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

export function QuizzesPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'subject_id', label: 'Subject id', type: 'number' },
    { name: 'class_id', label: 'Class id', type: 'number' },
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'description', label: 'Description', type: 'text' },
    { name: 'time_limit', label: 'Time limit (min)', type: 'number' },
    { name: 'passing_marks', label: 'Passing marks', type: 'number' },
    { name: 'max_attempts', label: 'Max attempts', type: 'number' },
    { name: 'random_order', label: 'Random order', type: 'checkbox' },
    { name: 'immediate_result', label: 'Immediate result', type: 'checkbox' },
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
      title="Quizzes"
      icon="circle-question"
      unitLabel="items"
      api={lmsApi.quizzes}
      columns={columns}
      fields={fields}
      emptyForm={{
        subject_id: '',
        class_id: '',
        title: '',
        description: '',
        time_limit: '',
        passing_marks: '',
        max_attempts: 1,
        random_order: false,
        immediate_result: true,
        status: 'draft',
      }}
      toForm={(r) => ({
        subject_id: (r.subject_id as number) ?? '',
        class_id: (r.class_id as number) ?? '',
        title: (r.title as string) ?? '',
        description: (r.description as string) ?? '',
        time_limit: (r.time_limit as number) ?? '',
        passing_marks: (r.passing_marks as number) ?? '',
        max_attempts: (r.max_attempts as number) ?? '',
        random_order: Boolean(r.random_order),
        immediate_result: Boolean(r.immediate_result),
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search…"
      sort="id"
    />
  );
}
