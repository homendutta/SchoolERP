/* Results — processed results per session, with process/publish actions. */
import { useEffect, useState } from 'react';
import { AXBadge, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import type { FieldOption } from '@features/academic/EntityManager';
import { examApi, type ExamResult, type ExamSession } from './api';

const TONES: Record<string, 'green' | 'red' | 'gray'> = {
  pass: 'green',
  fail: 'red',
  pending: 'gray',
};

export function ResultsPage() {
  const [sessions, setSessions] = useState<FieldOption[]>([]);
  const [sessionId, setSessionId] = useState('');
  const [rows, setRows] = useState<ExamResult[]>([]);
  const [msg, setMsg] = useState<string | null>(null);

  useEffect(() => {
    examApi.sessions
      .list({ per_page: 100 })
      .then((r) =>
        setSessions(r.data.map((s: ExamSession) => ({ value: String(s.id), label: s.name })))
      );
  }, []);

  const load = () => {
    if (!sessionId) return;
    examApi
      .results({ filter: { exam_session_id: sessionId }, per_page: 300, sort: 'rank' })
      .then((r) => setRows(r.data));
  };
  useEffect(() => {
    setMsg(null);
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [sessionId]);

  const run = async (fn: () => Promise<unknown>, label: string) => {
    setMsg(null);
    await fn();
    setMsg(label);
    load();
  };

  const columns: AXColumn<ExamResult>[] = [
    { key: 'rank', header: 'Rank', render: (r) => r.rank ?? '—' },
    {
      key: 'student',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student}</span>,
    },
    {
      key: 'adm',
      header: 'Adm. No.',
      render: (r) => <code className="text-xs text-gray-500">{r.admission_number}</code>,
    },
    { key: 'marks', header: 'Total', render: (r) => `${r.total_obtained} / ${r.total_max}` },
    { key: 'pct', header: '%', render: (r) => `${r.percentage}%` },
    { key: 'grade', header: 'Grade', render: (r) => r.grade ?? '—' },
    {
      key: 'status',
      header: 'Result',
      render: (r) => <AXBadge tone={TONES[r.result_status] ?? 'gray'}>{r.result_status}</AXBadge>,
    },
    {
      key: 'pub',
      header: '',
      render: (r) => (r.is_published ? <AXBadge tone="green">published</AXBadge> : null),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-ranking-star text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Results</h2>
        </div>
        <div className="flex items-end gap-2">
          <div className="w-56">
            <AXSelect
              value={sessionId}
              onChange={(e) => setSessionId(e.target.value)}
              options={[{ value: '', label: 'Select session…' }, ...sessions]}
            />
          </div>
          {sessionId && (
            <>
              <button
                onClick={() => run(() => examApi.processResults(Number(sessionId)), 'Processed')}
                className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
              >
                Process
              </button>
              <button
                onClick={() => run(() => examApi.publishResults(Number(sessionId)), 'Published')}
                className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
              >
                Publish
              </button>
            </>
          )}
        </div>
      </div>
      {msg && <AXBadge tone="green">{msg}</AXBadge>}
      {sessionId && (
        <AXTable
          columns={columns}
          rows={rows}
          rowKey={(r) => r.id}
          empty="No results yet — process the session."
        />
      )}
    </div>
  );
}
