/* Portal Results — report card / results from the Examination module. */
import { useEffect, useState } from 'react';
import { AXTable, type AXColumn } from '@ui/ax';
import { portalApi } from './api';
import { usePortal } from './usePortal';
import { PortalShell } from './PortalShell';

export function PortalResultsPage() {
  const { context, studentId, setStudentId, error } = usePortal();
  const [sessions, setSessions] = useState<Array<Record<string, unknown>>>([]);
  const [report, setReport] = useState<Record<string, unknown> | null>(null);

  useEffect(() => {
    if (!studentId) return;
    setReport(null);
    portalApi
      .examinations(studentId)
      .then((d) => setSessions((d.sessions as Array<Record<string, unknown>>) ?? []))
      .catch(() => setSessions([]));
  }, [studentId]);

  const openSession = (sessionId: number) => {
    if (!studentId) return;
    portalApi
      .examinations(studentId, sessionId)
      .then(setReport)
      .catch(() => setReport(null));
  };

  const subjectRows = (report?.subjects as Array<Record<string, unknown>>) ?? [];
  const columns: AXColumn<Record<string, unknown>>[] = [
    {
      key: 'subject',
      header: 'Subject',
      render: (r) => String(r.subject ?? r.name ?? r.subject_name ?? '—'),
    },
    {
      key: 'marks',
      header: 'Marks',
      render: (r) => String(r.marks ?? r.obtained ?? r.total ?? '—'),
    },
    { key: 'grade', header: 'Grade', render: (r) => String(r.grade ?? '—') },
  ];

  return (
    <PortalShell
      title="Results & Report Cards"
      icon="award"
      context={context}
      studentId={studentId}
      onStudent={setStudentId}
      error={error}
    >
      <div className="flex flex-wrap gap-2">
        {sessions.map((s) => (
          <button
            key={String(s.session_id)}
            onClick={() => openSession(Number(s.session_id))}
            className="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-[var(--navy-primary)] hover:text-white"
          >
            {String(s.name ?? `Session ${s.session_id}`)}
          </button>
        ))}
        {sessions.length === 0 && (
          <p className="text-sm text-gray-400">No published results yet.</p>
        )}
      </div>

      {report && (
        <div className="erp-card">
          <AXTable
            columns={columns}
            rows={subjectRows}
            rowKey={(r) => String(r.subject_id ?? r.id ?? r.subject ?? r.name ?? '')}
            empty="No subject marks."
          />
        </div>
      )}
    </PortalShell>
  );
}
