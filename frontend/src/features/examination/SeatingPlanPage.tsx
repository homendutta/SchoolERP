/* Seating Plan — allocate students to seats in a room (capacity validated). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import type { FieldOption } from '@features/academic/EntityManager';
import { examApi, type AssignedStudent, type ExamSchedule, type ExamSession } from './api';

export function SeatingPlanPage() {
  const { user } = useAuth();
  const [sessions, setSessions] = useState<FieldOption[]>([]);
  const [sessionId, setSessionId] = useState('');
  const [schedules, setSchedules] = useState<ExamSchedule[]>([]);
  const [scheduleId, setScheduleId] = useState('');
  const [students, setStudents] = useState<AssignedStudent[]>([]);
  const [rows, setRows] = useState<Array<Record<string, unknown>>>([]);
  const [form, setForm] = useState({ student_id: '', seat_number: '' });
  const [error, setError] = useState<string | null>(null);

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

  const load = () => {
    if (!scheduleId) return;
    examApi.seating
      .list({ filter: { exam_schedule_id: scheduleId }, per_page: 300 })
      .then((r) => setRows(r.data));
    if (schedule) examApi.subjectStudents(schedule.exam_subject_id).then(setStudents);
  };
  useEffect(() => {
    setError(null);
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [scheduleId]);

  const allocate = async () => {
    if (!schedule) return;
    setError(null);
    try {
      await examApi.seating.create({
        school_id: user?.school_id,
        exam_schedule_id: schedule.id,
        room_id: schedule.room_id,
        student_id: Number(form.student_id),
        seat_number: form.seat_number,
      });
      setForm({ student_id: '', seat_number: '' });
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Allocation failed (capacity?).');
    }
  };

  const columns: AXColumn<Record<string, unknown>>[] = [
    { key: 'seat', header: 'Seat', render: (r) => String(r.seat_number ?? '—') },
    { key: 'student', header: 'Student', render: (r) => String(r.student ?? '—') },
    { key: 'room', header: 'Room', render: (r) => String(r.room ?? '—') },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-chair text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Seating Plan</h2>
      </div>

      <div className="erp-card space-y-3">
        <div className="flex flex-wrap items-end gap-3">
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
                  label: `${s.subject ?? ''} · ${s.exam_date ?? ''} · ${s.room ?? 'no room'}`,
                })),
              ]}
            />
          </div>
          {schedule && !schedule.room_id && (
            <AXBadge tone="amber">No room on this exam — set one first.</AXBadge>
          )}
        </div>
        {scheduleId && schedule?.room_id && (
          <div className="flex flex-wrap items-end gap-2">
            <div className="w-64">
              <AXSelect
                label="Student"
                value={form.student_id}
                onChange={(e) => setForm((f) => ({ ...f, student_id: e.target.value }))}
                options={[
                  { value: '', label: 'Select…' },
                  ...students.map((s) => ({
                    value: String(s.student_id),
                    label: `${s.admission_number} — ${s.student}`,
                  })),
                ]}
              />
            </div>
            <div className="w-32">
              <AXInput
                label="Seat no."
                value={form.seat_number}
                onChange={(e) => setForm((f) => ({ ...f, seat_number: e.target.value }))}
              />
            </div>
            <button
              onClick={allocate}
              disabled={!form.student_id}
              className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
            >
              Allocate
            </button>
          </div>
        )}
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      {scheduleId && (
        <AXTable
          columns={columns}
          rows={rows}
          rowKey={(r) => Number(r.id)}
          empty="No seats allocated yet."
        />
      )}
    </div>
  );
}
