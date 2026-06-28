/* Manual Attendance — pick a class + date, load students, mark each, submit.
 * Writes through the same Attendance Engine as import/biometric. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { useClasses, useMasterValues } from '@features/academic/useReference';
import { studentsApi, type Student } from '@features/students/api';
import { ATTENDANCE_STATUS, attendanceApi } from './api';

export function ManualAttendancePage() {
  const { user } = useAuth();
  const classes = useClasses();
  const sessions = useMasterValues('attendance_sessions');
  const [classId, setClassId] = useState('');
  const [sessionId, setSessionId] = useState('');
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [students, setStudents] = useState<Student[]>([]);
  const [marks, setMarks] = useState<Record<number, string>>({});
  const [busy, setBusy] = useState(false);
  const [result, setResult] = useState<{
    marked: number;
    skipped: number;
    unmatched: number;
  } | null>(null);

  useEffect(() => {
    setResult(null);
    if (!classId) {
      setStudents([]);
      return;
    }
    studentsApi.list({ filter: { class_id: classId }, per_page: 200, sort: 'name' }).then((r) => {
      setStudents(r.data);
      setMarks(Object.fromEntries(r.data.map((s) => [s.id, 'present'])));
    });
  }, [classId]);

  const setMark = (id: number, status: string) => setMarks((m) => ({ ...m, [id]: status }));
  const markAll = (status: string) =>
    setMarks(Object.fromEntries(students.map((s) => [s.id, status])));

  const submit = async () => {
    setBusy(true);
    setResult(null);
    try {
      const entries = students
        .filter((s) => s.identity_id)
        .map((s) => ({ identity_id: s.identity_id, status: marks[s.id] ?? 'present' }));
      const res = await attendanceApi.mark({
        school_id: user?.school_id,
        date,
        session_id: sessionId ? Number(sessionId) : null,
        entries,
      });
      setResult(res);
    } finally {
      setBusy(false);
    }
  };

  const columns: AXColumn<Student>[] = [
    {
      key: 'admission_number',
      header: 'Adm. No.',
      render: (r) => <code className="text-xs text-gray-500">{r.admission_number}</code>,
    },
    {
      key: 'name',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.name}</span>,
    },
    {
      key: 'status',
      header: 'Status',
      className: 'w-48',
      render: (r) => (
        <AXSelect
          value={marks[r.id] ?? 'present'}
          onChange={(e) => setMark(r.id, e.target.value)}
          options={ATTENDANCE_STATUS.map((s) => ({ value: s, label: s }))}
        />
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-list-check text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Manual Attendance</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-52">
          <AXSelect
            label="Class"
            value={classId}
            onChange={(e) => setClassId(e.target.value)}
            options={[{ value: '', label: 'Select class…' }, ...classes]}
          />
        </div>
        <div className="w-44">
          <AXSelect
            label="Session"
            value={sessionId}
            onChange={(e) => setSessionId(e.target.value)}
            options={[{ value: '', label: '—' }, ...sessions]}
          />
        </div>
        <div className="w-44">
          <AXInput
            label="Date"
            type="date"
            value={date}
            onChange={(e) => setDate(e.target.value)}
          />
        </div>
        {students.length > 0 && (
          <div className="flex gap-2">
            <button
              onClick={() => markAll('present')}
              className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
            >
              All present
            </button>
            <button
              onClick={() => markAll('absent')}
              className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
            >
              All absent
            </button>
            <button
              onClick={submit}
              disabled={busy}
              className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
            >
              <i className="fas fa-check mr-1" /> Submit
            </button>
          </div>
        )}
      </div>

      {result && (
        <div className="flex gap-3 text-sm">
          <AXBadge tone="green">Marked: {result.marked}</AXBadge>
          <AXBadge tone="amber">Skipped: {result.skipped}</AXBadge>
          {result.unmatched > 0 && <AXBadge tone="red">Unmatched: {result.unmatched}</AXBadge>}
        </div>
      )}

      {classId && (
        <AXTable
          columns={columns}
          rows={students}
          rowKey={(r) => r.id}
          empty="No students in this class."
        />
      )}
    </div>
  );
}
