/* Exam Schedule — schedule subjects on dates/periods/rooms with clash detection. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import type { FieldOption } from '@features/academic/EntityManager';
import { useRooms } from '@features/academic/useReference';
import { timetableApi } from '@features/timetable/api';
import { examApi, type ExamSchedule, type ExamSession, type ExamSubject } from './api';

export function ExamSchedulePage() {
  const { user } = useAuth();
  const rooms = useRooms();
  const [sessions, setSessions] = useState<FieldOption[]>([]);
  const [sessionId, setSessionId] = useState('');
  const [subjects, setSubjects] = useState<ExamSubject[]>([]);
  const [rows, setRows] = useState<ExamSchedule[]>([]);
  const [periods, setPeriods] = useState<FieldOption[]>([]);
  const [form, setForm] = useState({
    exam_subject_id: '',
    exam_date: '',
    period_id: '',
    room_id: '',
    duration_minutes: '180',
  });
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    examApi.sessions
      .list({ per_page: 100 })
      .then((r) =>
        setSessions(r.data.map((s: ExamSession) => ({ value: String(s.id), label: s.name })))
      );
    timetableApi.periods
      .list({ per_page: 100, sort: 'sort_order' })
      .then((r) => setPeriods(r.data.map((p) => ({ value: String(p.id), label: p.name }))));
  }, []);

  const load = () => {
    if (!sessionId) return;
    examApi.subjects
      .list({ filter: { exam_session_id: sessionId }, per_page: 200 })
      .then((r) => setSubjects(r.data));
    examApi.schedules
      .list({ filter: { exam_session_id: sessionId }, per_page: 200, sort: 'exam_date' })
      .then((r) => setRows(r.data));
  };
  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [sessionId]);

  const add = async () => {
    setError(null);
    try {
      await examApi.schedules.create({
        school_id: user?.school_id,
        exam_session_id: Number(sessionId),
        exam_subject_id: Number(form.exam_subject_id),
        exam_date: form.exam_date,
        period_id: form.period_id ? Number(form.period_id) : null,
        room_id: form.room_id ? Number(form.room_id) : null,
        duration_minutes: Number(form.duration_minutes),
      });
      setForm({
        exam_subject_id: '',
        exam_date: '',
        period_id: '',
        room_id: '',
        duration_minutes: '180',
      });
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not schedule (clash detected).');
    }
  };

  const columns: AXColumn<ExamSchedule>[] = [
    { key: 'date', header: 'Date', render: (r) => r.exam_date ?? '—' },
    {
      key: 'subject',
      header: 'Subject',
      render: (r) => <span className="font-medium">{r.subject ?? '—'}</span>,
    },
    { key: 'class', header: 'Class', render: (r) => r.class ?? '—' },
    { key: 'period', header: 'Period', render: (r) => r.period ?? '—' },
    { key: 'room', header: 'Room', render: (r) => r.room ?? '—' },
    { key: 'duration', header: 'Duration', render: (r) => `${r.duration_minutes} min` },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-calendar-check text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Exam Schedule</h2>
      </div>

      <div className="erp-card space-y-3">
        <div className="w-56">
          <AXSelect
            label="Exam session"
            value={sessionId}
            onChange={(e) => setSessionId(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...sessions]}
          />
        </div>
        {sessionId && (
          <div className="flex flex-wrap items-end gap-2">
            <div className="w-52">
              <AXSelect
                label="Subject"
                value={form.exam_subject_id}
                onChange={(e) => setForm((f) => ({ ...f, exam_subject_id: e.target.value }))}
                options={[
                  { value: '', label: 'Select…' },
                  ...subjects.map((s) => ({
                    value: String(s.id),
                    label: `${s.subject ?? s.subject_id} (${s.class ?? ''})`,
                  })),
                ]}
              />
            </div>
            <div className="w-40">
              <AXInput
                label="Date"
                type="date"
                value={form.exam_date}
                onChange={(e) => setForm((f) => ({ ...f, exam_date: e.target.value }))}
              />
            </div>
            <div className="w-36">
              <AXSelect
                label="Period"
                value={form.period_id}
                onChange={(e) => setForm((f) => ({ ...f, period_id: e.target.value }))}
                options={[{ value: '', label: '—' }, ...periods]}
              />
            </div>
            <div className="w-40">
              <AXSelect
                label="Room"
                value={form.room_id}
                onChange={(e) => setForm((f) => ({ ...f, room_id: e.target.value }))}
                options={[{ value: '', label: '—' }, ...rooms]}
              />
            </div>
            <div className="w-28">
              <AXInput
                label="Minutes"
                type="number"
                value={form.duration_minutes}
                onChange={(e) => setForm((f) => ({ ...f, duration_minutes: e.target.value }))}
              />
            </div>
            <button
              onClick={add}
              disabled={!form.exam_subject_id || !form.exam_date}
              className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
            >
              Add
            </button>
          </div>
        )}
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      {sessionId && (
        <AXTable
          columns={columns}
          rows={rows}
          rowKey={(r) => r.id}
          empty="No exams scheduled yet."
        />
      )}
    </div>
  );
}
