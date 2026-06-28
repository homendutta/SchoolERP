/* Tabulation Sheet — class-wise marks matrix with totals, percentage and rank. */
import { useEffect, useState } from 'react';
import { AXSelect } from '@ui/ax';
import type { FieldOption } from '@features/academic/EntityManager';
import { useClasses } from '@features/academic/useReference';
import { examApi, type ExamSession } from './api';

interface SubjectCol {
  exam_subject_id: number;
  subject: string | null;
  code: string | null;
}
interface Row {
  student_id: number;
  student: string | null;
  admission_number: string | null;
  marks: Record<string, { obtained: number; is_absent: boolean; passed: boolean }>;
  total_obtained: number;
  total_max: number;
  percentage: number;
  result_status: string;
  rank: number | null;
}

export function TabulationPage() {
  const classes = useClasses();
  const [sessions, setSessions] = useState<FieldOption[]>([]);
  const [sessionId, setSessionId] = useState('');
  const [classId, setClassId] = useState('');
  const [data, setData] = useState<{ subjects: SubjectCol[]; rows: Row[] } | null>(null);

  useEffect(() => {
    examApi.sessions
      .list({ per_page: 100 })
      .then((r) =>
        setSessions(r.data.map((s: ExamSession) => ({ value: String(s.id), label: s.name })))
      );
  }, []);

  useEffect(() => {
    if (sessionId && classId) {
      examApi
        .tabulation({ exam_session_id: sessionId, class_id: classId })
        .then((d) => setData(d as unknown as { subjects: SubjectCol[]; rows: Row[] }));
    } else {
      setData(null);
    }
  }, [sessionId, classId]);

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-table-list text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Tabulation Sheet</h2>
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
        <div className="w-44">
          <AXSelect
            label="Class"
            value={classId}
            onChange={(e) => setClassId(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...classes]}
          />
        </div>
      </div>

      {data && (
        <div className="erp-card overflow-x-auto">
          <table className="w-full border-collapse text-sm">
            <thead>
              <tr>
                <th className="border bg-gray-50 p-2 text-left text-gray-600">Rank</th>
                <th className="border bg-gray-50 p-2 text-left text-gray-600">Student</th>
                {data.subjects.map((s) => (
                  <th
                    key={s.exam_subject_id}
                    className="border bg-gray-50 p-2 text-center text-gray-600"
                  >
                    {s.code ?? s.subject}
                  </th>
                ))}
                <th className="border bg-gray-50 p-2 text-center text-gray-600">Total</th>
                <th className="border bg-gray-50 p-2 text-center text-gray-600">%</th>
              </tr>
            </thead>
            <tbody>
              {data.rows.map((row) => (
                <tr key={row.student_id}>
                  <td className="border p-2 text-center font-semibold">{row.rank ?? '—'}</td>
                  <td className="border p-2">
                    <div className="font-medium">{row.student}</div>
                    <div className="text-xs text-gray-400">{row.admission_number}</div>
                  </td>
                  {data.subjects.map((s) => {
                    const m = row.marks[String(s.exam_subject_id)];
                    return (
                      <td
                        key={s.exam_subject_id}
                        className={`border p-2 text-center ${m && !m.passed ? 'text-[var(--danger)]' : ''}`}
                      >
                        {m ? (
                          m.is_absent ? (
                            'AB'
                          ) : (
                            m.obtained
                          )
                        ) : (
                          <span className="text-gray-300">—</span>
                        )}
                      </td>
                    );
                  })}
                  <td className="border p-2 text-center">
                    {row.total_obtained}/{row.total_max}
                  </td>
                  <td className="border p-2 text-center font-medium">{row.percentage}%</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
