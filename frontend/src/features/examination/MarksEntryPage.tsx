/* Marks Entry — pick a session + subject, then enter marks for the students
 * ASSIGNED to that subject (optional-subject safe). Autosaves on submit. */
import { useEffect, useState } from 'react';
import { AXBadge, AXInput, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import type { FieldOption } from '@features/academic/EntityManager';
import { studentsApi, type Student } from '@features/students/api';
import { examApi, type AssignedStudent, type ExamSession, type ExamSubject } from './api';

export function MarksEntryPage() {
  const [sessions, setSessions] = useState<FieldOption[]>([]);
  const [sessionId, setSessionId] = useState('');
  const [subjects, setSubjects] = useState<ExamSubject[]>([]);
  const [subjectId, setSubjectId] = useState('');
  const [students, setStudents] = useState<AssignedStudent[]>([]);
  const [marks, setMarks] = useState<
    Record<number, { marks_obtained: string; is_absent: boolean }>
  >({});
  const [result, setResult] = useState<string | null>(null);
  const [addStudent, setAddStudent] = useState('');
  const [classStudents, setClassStudents] = useState<Student[]>([]);

  const subject = subjects.find((s) => String(s.id) === subjectId);

  useEffect(() => {
    examApi.sessions
      .list({ per_page: 100 })
      .then((r) =>
        setSessions(r.data.map((s: ExamSession) => ({ value: String(s.id), label: s.name })))
      );
  }, []);

  useEffect(() => {
    setSubjectId('');
    if (!sessionId) return;
    examApi.subjects
      .list({ filter: { exam_session_id: sessionId }, per_page: 200 })
      .then((r) => setSubjects(r.data));
  }, [sessionId]);

  const loadStudents = () => {
    if (!subjectId) return;
    examApi.subjectStudents(Number(subjectId)).then((rows) => {
      setStudents(rows);
      setMarks(
        Object.fromEntries(
          rows.map((s) => [s.student_id, { marks_obtained: '', is_absent: false }])
        )
      );
    });
    examApi.marks.list(Number(subjectId)).then((rows) => {
      setMarks((m) => {
        const next = { ...m };
        for (const row of rows as Array<Record<string, unknown>>) {
          const sid = Number(row.student_id);
          next[sid] = {
            marks_obtained: row.marks_obtained != null ? String(row.marks_obtained) : '',
            is_absent: Boolean(row.is_absent),
          };
        }
        return next;
      });
    });
  };

  useEffect(() => {
    setResult(null);
    setStudents([]);
    if (subject) {
      studentsApi
        .list({ filter: { class_id: subject.class_id }, per_page: 300, sort: 'name' })
        .then((r) => setClassStudents(r.data));
      loadStudents();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [subjectId]);

  const setMark = (sid: number, patch: Partial<{ marks_obtained: string; is_absent: boolean }>) =>
    setMarks((m) => ({ ...m, [sid]: { ...m[sid], ...patch } }));

  const save = async () => {
    if (!subject) return;
    const entries = students.map((s) => ({
      student_id: s.student_id,
      marks_obtained: marks[s.student_id]?.is_absent
        ? null
        : Number(marks[s.student_id]?.marks_obtained || 0),
      is_absent: marks[s.student_id]?.is_absent ?? false,
    }));
    const res = await examApi.marks.save({ exam_subject_id: subject.id, entries });
    setResult(`Saved ${res.saved}, skipped ${res.skipped}`);
  };

  const assign = async () => {
    if (!subject || !addStudent) return;
    await examApi.assignStudent(subject.id, Number(addStudent));
    setAddStudent('');
    loadStudents();
  };

  const columns: AXColumn<AssignedStudent>[] = [
    {
      key: 'adm',
      header: 'Adm. No.',
      render: (r) => <code className="text-xs text-gray-500">{r.admission_number}</code>,
    },
    {
      key: 'name',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student}</span>,
    },
    {
      key: 'marks',
      header: 'Marks',
      className: 'w-40',
      render: (r) => (
        <AXInput
          type="number"
          value={marks[r.student_id]?.marks_obtained ?? ''}
          disabled={marks[r.student_id]?.is_absent}
          onChange={(e) => setMark(r.student_id, { marks_obtained: e.target.value })}
        />
      ),
    },
    {
      key: 'absent',
      header: 'Absent',
      className: 'w-20',
      render: (r) => (
        <input
          type="checkbox"
          checked={marks[r.student_id]?.is_absent ?? false}
          onChange={(e) => setMark(r.student_id, { is_absent: e.target.checked })}
        />
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-pen-to-square text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Marks Entry</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-56">
          <AXSelect
            label="Exam session"
            value={sessionId}
            onChange={(e) => setSessionId(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...sessions]}
          />
        </div>
        <div className="w-64">
          <AXSelect
            label="Subject"
            value={subjectId}
            onChange={(e) => setSubjectId(e.target.value)}
            options={[
              { value: '', label: 'Select…' },
              ...subjects.map((s) => ({
                value: String(s.id),
                label: `${s.subject ?? s.subject_id}${s.is_elective ? ' (elective)' : ''}`,
              })),
            ]}
          />
        </div>
        {subject && (
          <AXBadge tone="navy">
            Max {subject.max_marks} · Pass {subject.passing_marks}
          </AXBadge>
        )}
      </div>

      {subject?.is_elective && (
        <div className="erp-card flex flex-wrap items-end gap-2">
          <div className="w-72">
            <AXSelect
              label="Assign a student to this elective"
              value={addStudent}
              onChange={(e) => setAddStudent(e.target.value)}
              options={[
                { value: '', label: 'Select student…' },
                ...classStudents.map((s) => ({
                  value: String(s.id),
                  label: `${s.admission_number} — ${s.name}`,
                })),
              ]}
            />
          </div>
          <button
            onClick={assign}
            disabled={!addStudent}
            className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 disabled:opacity-60"
          >
            Assign
          </button>
        </div>
      )}

      {subjectId && (
        <>
          {students.length > 0 && (
            <div className="flex items-center justify-between">
              <span className="text-sm text-gray-500">{students.length} assigned student(s)</span>
              <div className="flex items-center gap-3">
                {result && <AXBadge tone="green">{result}</AXBadge>}
                <button
                  onClick={save}
                  className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white"
                >
                  <i className="fas fa-save mr-1" /> Save marks
                </button>
              </div>
            </div>
          )}
          <AXTable
            columns={columns}
            rows={students}
            rowKey={(r) => r.student_id}
            empty="No students assigned to this subject."
          />
        </>
      )}
    </div>
  );
}
