/* Exam Attendance — separate from daily attendance. Mark present / absent /
 * malpractice / medical leave per scheduled exam. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import type { FieldOption } from '@features/academic/EntityManager';
import {
  examApi,
  EXAM_ATTENDANCE_STATUSES,
  type AssignedStudent,
  type ExamSchedule,
  type ExamSession,
} from './api';

export function ExamAttendancePage() {
  const { user } = useAuth();
  const [sessions, setSessions] = useState<FieldOption[]>([]);
  const [sessionId, setSessionId] = useState('');
  const [schedules, setSchedules] = useState<ExamSchedule[]>([]);
  const [scheduleId, setScheduleId] = useState('');
  const [students, setStudents] = useState<AssignedStudent[]>([]);
  const [status, setStatus] = useState<Record<number, string>>({});
  const [result, setResult] = useState<string | null>(null);

  const schedule = schedules.find((s) => String(s.id) === scheduleId);

  useEffect(() => {
    examApi.sessions
      .list({ per_page: 100 })
      .then((r) =>
        setSessions(r.data.map((s: ExamSession) => ({ value: String(s.id), label: s.name })))
      );
  }, []);

  useEffect(() => {
    setScheduleId('');
    if (!sessionId) return;
    examApi.schedules
      .list({ filter: { exam_session_id: sessionId }, per_page: 200 })
      .then((r) => setSchedules(r.data));
  }, [sessionId]);

  useEffect(() => {
    setResult(null);
    setStudents([]);
    if (schedule) {
      examApi.subjectStudents(schedule.exam_subject_id).then((rows) => {
        setStudents(rows);
        setStatus(Object.fromEntries(rows.map((s) => [s.student_id, 'present'])));
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [scheduleId]);

  const save = async () => {
    if (!schedule) return;
    const entries = students.map((s) => ({
      student_id: s.student_id,
      status: status[s.student_id] ?? 'present',
    }));
    const res = await examApi.examAttendance.mark({
      school_id: user?.school_id,
      exam_schedule_id: schedule.id,
      entries,
    });
    setResult(`Marked ${res.marked}`);
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
      key: 'status',
      header: 'Status',
      className: 'w-48',
      render: (r) => (
        <AXSelect
          value={status[r.student_id] ?? 'present'}
          onChange={(e) => setStatus((m) => ({ ...m, [r.student_id]: e.target.value }))}
          options={EXAM_ATTENDANCE_STATUSES.map((s) => ({ value: s, label: s.replace('_', ' ') }))}
        />
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-user-check text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Exam Attendance</h2>
        <AXBadge tone="gray">separate from daily</AXBadge>
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
            label="Scheduled exam"
            value={scheduleId}
            onChange={(e) => setScheduleId(e.target.value)}
            options={[
              { value: '', label: 'Select…' },
              ...schedules.map((s) => ({
                value: String(s.id),
                label: `${s.subject ?? ''} · ${s.exam_date ?? ''}`,
              })),
            ]}
          />
        </div>
        {students.length > 0 && (
          <div className="flex items-center gap-3">
            {result && <AXBadge tone="green">{result}</AXBadge>}
            <button
              onClick={save}
              className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white"
            >
              <i className="fas fa-save mr-1" /> Save
            </button>
          </div>
        )}
      </div>

      {scheduleId && (
        <AXTable
          columns={columns}
          rows={students}
          rowKey={(r) => r.student_id}
          empty="No students assigned to this exam."
        />
      )}
    </div>
  );
}
