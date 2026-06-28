/* Exam Sessions — the hub. Manage sessions, map subjects (core auto-assign +
 * elective opt-in), then process & publish results. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXModal, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { useAllSections, useClasses, useSubjects, useYears } from '@features/academic/useReference';
import {
  examApi,
  EXAM_SESSION_STATUSES,
  RANKING_METHODS,
  type ExamSession,
  type ExamSubject,
} from './api';

const TONES: Record<string, 'gray' | 'amber' | 'navy' | 'green'> = {
  draft: 'gray',
  scheduled: 'amber',
  ongoing: 'amber',
  completed: 'navy',
  published: 'green',
};

export function ExamSessionsPage() {
  const { user } = useAuth();
  const years = useYears();
  const classes = useClasses();
  const sections = useAllSections();
  const subjects = useSubjects();
  const [examTypes, setExamTypes] = useState<FieldOption[]>([]);
  const [subjectModal, setSubjectModal] = useState<ExamSession | null>(null);
  const [msg, setMsg] = useState<string | null>(null);

  useEffect(() => {
    examApi.types
      .list({ per_page: 100 })
      .then((r) => setExamTypes(r.data.map((t) => ({ value: String(t.id), label: t.name }))));
  }, []);

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    {
      name: 'academic_year_id',
      label: 'Academic year',
      type: 'select',
      options: [{ value: '', label: '—' }, ...years],
    },
    {
      name: 'exam_type_id',
      label: 'Exam type',
      type: 'select',
      options: [{ value: '', label: '—' }, ...examTypes],
    },
    {
      name: 'ranking_method',
      label: 'Ranking',
      type: 'select',
      options: RANKING_METHODS.map((m) => ({ value: m, label: m })),
    },
    { name: 'start_date', label: 'Start date', type: 'date' },
    { name: 'end_date', label: 'End date', type: 'date' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: EXAM_SESSION_STATUSES.map((s) => ({ value: s, label: s })),
    },
    { name: 'description', label: 'Description', type: 'text' },
  ];

  const columns: AXColumn<ExamSession>[] = [
    {
      key: 'name',
      header: 'Session',
      render: (r) => <span className="font-medium">{r.name}</span>,
    },
    { key: 'exam_type', header: 'Type', render: (r) => r.exam_type ?? '—' },
    { key: 'year', header: 'Year', render: (r) => r.academic_year ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
  ];

  const act = async (fn: () => Promise<unknown>, label: string, reload?: () => void) => {
    setMsg(null);
    try {
      await fn();
      setMsg(label);
      reload?.();
    } catch (e) {
      setMsg(e instanceof Error ? e.message : 'Action failed');
    }
  };

  return (
    <div className="space-y-3">
      {msg && <AXBadge tone="green">{msg}</AXBadge>}
      <EntityManager<ExamSession>
        title="Exam Sessions"
        icon="file-pen"
        unitLabel="sessions"
        api={examApi.sessions}
        columns={columns}
        fields={fields}
        emptyForm={{
          name: '',
          academic_year_id: '',
          exam_type_id: '',
          ranking_method: 'competition',
          start_date: '',
          end_date: '',
          status: 'draft',
          description: '',
        }}
        toForm={(r) => ({
          name: r.name,
          academic_year_id: String(r.academic_year_id),
          exam_type_id: String(r.exam_type_id),
          ranking_method: r.ranking_method,
          start_date: r.start_date ?? '',
          end_date: r.end_date ?? '',
          status: r.status,
          description: r.description ?? '',
        })}
        createDefaults={{ school_id: user?.school_id }}
        searchKey="name"
        searchPlaceholder="Search sessions…"
        sort="name"
        rowExtras={(row, reload) => (
          <div className="flex flex-wrap gap-1">
            <button
              onClick={() => setSubjectModal(row)}
              className="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700"
            >
              Subjects
            </button>
            <button
              onClick={() =>
                act(() => examApi.assignSubjects(row.id), 'Core subjects assigned', reload)
              }
              className="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700"
            >
              Assign core
            </button>
            <button
              onClick={() => act(() => examApi.processResults(row.id), 'Results processed', reload)}
              className="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-[var(--navy-accent)]"
            >
              Process
            </button>
            <button
              onClick={() => act(() => examApi.publishResults(row.id), 'Results published', reload)}
              className="rounded bg-[var(--navy-primary)] px-2 py-1 text-xs font-semibold text-white"
            >
              Publish
            </button>
          </div>
        )}
      />

      {subjectModal && (
        <SubjectMappingModal
          session={subjectModal}
          schoolId={user?.school_id}
          classes={classes}
          sections={sections}
          subjects={subjects}
          onClose={() => setSubjectModal(null)}
        />
      )}
    </div>
  );
}

function SubjectMappingModal({
  session,
  schoolId,
  classes,
  sections,
  subjects,
  onClose,
}: {
  session: ExamSession;
  schoolId: number | null | undefined;
  classes: FieldOption[];
  sections: FieldOption[];
  subjects: FieldOption[];
  onClose: () => void;
}) {
  const [rows, setRows] = useState<ExamSubject[]>([]);
  const [form, setForm] = useState({
    class_id: '',
    section_id: '',
    subject_id: '',
    is_elective: 'false',
    max_marks: '100',
    passing_marks: '33',
  });

  const load = () =>
    examApi.subjects
      .list({ filter: { exam_session_id: session.id }, per_page: 200 })
      .then((r) => setRows(r.data));
  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const add = async () => {
    await examApi.subjects.create({
      school_id: schoolId,
      exam_session_id: session.id,
      class_id: Number(form.class_id),
      section_id: form.section_id ? Number(form.section_id) : null,
      subject_id: Number(form.subject_id),
      is_elective: form.is_elective === 'true',
      max_marks: Number(form.max_marks),
      passing_marks: Number(form.passing_marks),
    });
    load();
  };

  const columns: AXColumn<ExamSubject>[] = [
    { key: 'subject', header: 'Subject', render: (r) => r.subject ?? `#${r.subject_id}` },
    { key: 'class', header: 'Class', render: (r) => r.class ?? '—' },
    {
      key: 'type',
      header: 'Type',
      render: (r) => (
        <AXBadge tone={r.is_elective ? 'amber' : 'navy'}>
          {r.is_elective ? 'Elective' : 'Core'}
        </AXBadge>
      ),
    },
    { key: 'marks', header: 'Max / Pass', render: (r) => `${r.max_marks} / ${r.passing_marks}` },
  ];

  return (
    <AXModal open title={`Subjects — ${session.name}`} onClose={onClose}>
      <div className="space-y-3">
        <div className="grid grid-cols-2 gap-2">
          <AXSelect
            label="Class"
            value={form.class_id}
            onChange={(e) => setForm((f) => ({ ...f, class_id: e.target.value }))}
            options={[{ value: '', label: 'Select…' }, ...classes]}
          />
          <AXSelect
            label="Section"
            value={form.section_id}
            onChange={(e) => setForm((f) => ({ ...f, section_id: e.target.value }))}
            options={[{ value: '', label: 'All' }, ...sections]}
          />
          <AXSelect
            label="Subject"
            value={form.subject_id}
            onChange={(e) => setForm((f) => ({ ...f, subject_id: e.target.value }))}
            options={[{ value: '', label: 'Select…' }, ...subjects]}
          />
          <AXSelect
            label="Type"
            value={form.is_elective}
            onChange={(e) => setForm((f) => ({ ...f, is_elective: e.target.value }))}
            options={[
              { value: 'false', label: 'Core' },
              { value: 'true', label: 'Elective' },
            ]}
          />
        </div>
        <button
          onClick={add}
          disabled={!form.class_id || !form.subject_id}
          className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          <i className="fas fa-plus mr-1" /> Map subject
        </button>
        <p className="text-xs text-gray-400">
          Core subjects auto-assign to current students. Electives are opt-in (assign students from
          Marks Entry).
        </p>
        <AXTable
          columns={columns}
          rows={rows}
          rowKey={(r) => r.id}
          empty="No subjects mapped yet."
        />
      </div>
    </AXModal>
  );
}
