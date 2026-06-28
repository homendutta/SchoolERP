/* Enrollment — turn an approved application into a Student in one transaction.
 * Shows the freshly generated login credentials once on success. */
import { useEffect, useState, useCallback } from 'react';
import { AXBadge, AXModal, AXTable, type AXColumn } from '@ui/ax';
import { admissionsApi, type Application, type EnrollmentResult } from './api';

export function EnrollmentPage() {
  const [rows, setRows] = useState<Application[]>([]);
  const [loading, setLoading] = useState(false);
  const [busy, setBusy] = useState<number | null>(null);
  const [result, setResult] = useState<EnrollmentResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(() => {
    setLoading(true);
    admissionsApi.applications
      .list({ filter: { status: 'approved' }, per_page: 100 })
      .then((r) => setRows(r.data))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const enroll = async (id: number) => {
    setBusy(id);
    setError(null);
    try {
      const res = await admissionsApi.enroll(id);
      setResult(res);
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Enrollment failed.');
    } finally {
      setBusy(null);
    }
  };

  const columns: AXColumn<Application>[] = [
    {
      key: 'application_number',
      header: 'No.',
      render: (r) => <code className="text-xs text-gray-500">{r.application_number}</code>,
    },
    {
      key: 'student_name',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student_name}</span>,
    },
    { key: 'guardian_name', header: 'Guardian', render: (r) => r.guardian_name },
    { key: 'status', header: 'Status', render: (r) => <AXBadge tone="green">{r.status}</AXBadge> },
    {
      key: 'actions',
      header: '',
      className: 'text-right',
      render: (r) => (
        <button
          onClick={() => enroll(r.id)}
          disabled={busy === r.id}
          className="rounded-md bg-[var(--navy-primary)] px-3 py-1.5 text-sm font-semibold text-white disabled:opacity-60"
        >
          {busy === r.id ? (
            <i className="fas fa-spinner fa-spin" />
          ) : (
            <>
              <i className="fas fa-user-plus mr-1" /> Enroll
            </>
          )}
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-user-graduate text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Enrollment</h2>
        <AXBadge tone="navy">{rows.length} approved</AXBadge>
      </div>

      {error && (
        <div className="rounded-md bg-red-50 px-3 py-2 text-sm text-[var(--danger)]">{error}</div>
      )}

      <AXTable
        columns={columns}
        rows={rows}
        rowKey={(r) => r.id}
        loading={loading}
        empty="No approved applications awaiting enrollment."
      />

      <AXModal open={result !== null} title="Student Enrolled" onClose={() => setResult(null)}>
        {result && (
          <div className="space-y-3 text-sm">
            <p>
              <strong>{result.student.name}</strong> enrolled with Admission #
              <code className="ml-1">{result.student.admission_number}</code>.
            </p>
            <div className="rounded-md border border-gray-200 p-3">
              <div className="mb-1 font-semibold text-[var(--navy-primary)]">Student login</div>
              <div>
                Username: <code>{result.credentials.student.username}</code>
              </div>
              <div>
                Password: <code>{result.credentials.student.password}</code>
              </div>
            </div>
            <div className="rounded-md border border-gray-200 p-3">
              <div className="mb-1 font-semibold text-[var(--navy-primary)]">Parent login</div>
              <div>
                Username: <code>{result.credentials.parent.username}</code>
              </div>
              <div>
                Password: <code>{result.credentials.parent.password}</code>
              </div>
            </div>
            <p className="text-xs text-gray-400">Copy these now — passwords are shown only once.</p>
          </div>
        )}
      </AXModal>
    </div>
  );
}
