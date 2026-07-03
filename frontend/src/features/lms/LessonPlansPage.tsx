/* Lesson Plans — LMS teacher content (auto-generated CRUD page). */
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

export function LessonPlansPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'subject_id', label: 'Subject id', type: 'number' },
    { name: 'class_id', label: 'Class id', type: 'number' },
    { name: 'section_id', label: 'Section id', type: 'number' },
    { name: 'title', label: 'Title', type: 'text' },
    { name: 'objectives', label: 'Objectives', type: 'text' },
    { name: 'topics', label: 'Topics covered', type: 'text' },
    { name: 'teaching_method', label: 'Teaching method', type: 'text' },
    { name: 'planned_date', label: 'Planned date', type: 'date' },
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
      title="Lesson Plans"
      icon="clipboard-list"
      unitLabel="items"
      api={lmsApi.lessonPlans}
      columns={columns}
      fields={fields}
      emptyForm={{
        subject_id: '',
        class_id: '',
        section_id: '',
        title: '',
        objectives: '',
        topics: '',
        teaching_method: '',
        planned_date: '',
        status: 'draft',
      }}
      toForm={(r) => ({
        subject_id: (r.subject_id as number) ?? '',
        class_id: (r.class_id as number) ?? '',
        section_id: (r.section_id as number) ?? '',
        title: (r.title as string) ?? '',
        objectives: (r.objectives as string) ?? '',
        topics: (r.topics as string) ?? '',
        teaching_method: (r.teaching_method as string) ?? '',
        planned_date: (r.planned_date as string) ?? '',
        status: String(r.status ?? 'draft'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="title"
      searchPlaceholder="Search…"
      sort="id"
    />
  );
}
