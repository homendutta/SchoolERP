/* Admission Verification — verify an application and its documents (Pending /
 * Verified / Rejected / On Hold), with full history. */
import { useEffect, useState, useCallback } from 'react';
import { AXBadge, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import {
  admissionsApi,
  type AdmissionDocument,
  type Application,
  type VerificationLog,
} from './api';

const TONES: Record<string, 'navy' | 'green' | 'amber' | 'red' | 'gray'> = {
  pending: 'amber',
  verified: 'green',
  rejected: 'red',
  on_hold: 'gray',
};

export function VerificationPage() {
  const [apps, setApps] = useState<Application[]>([]);
  const [selectedId, setSelectedId] = useState('');
  const [app, setApp] = useState<Application | null>(null);
  const [history, setHistory] = useState<VerificationLog[]>([]);

  useEffect(() => {
    admissionsApi.applications
      .list({ per_page: 100, sort: 'created_at' })
      .then((r) => setApps(r.data));
  }, []);

  const refresh = useCallback((id: number) => {
    admissionsApi.applications.get(id).then(setApp);
    admissionsApi.verification.history(id).then((h) => setHistory(Array.isArray(h) ? h : []));
  }, []);

  useEffect(() => {
    if (selectedId) refresh(Number(selectedId));
    else {
      setApp(null);
      setHistory([]);
    }
  }, [selectedId, refresh]);

  const setAppStatus = (status: string) =>
    app && admissionsApi.verification.application(app.id, status).then(() => refresh(app.id));
  const setDocStatus = (docId: number, status: string) =>
    app && admissionsApi.verification.document(docId, status).then(() => refresh(app.id));

  const docColumns: AXColumn<AdmissionDocument>[] = [
    {
      key: 'title',
      header: 'Document',
      render: (r) => (
        <span className="font-medium">{r.title ?? r.document_type?.label ?? `#${r.id}`}</span>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'actions',
      header: '',
      className: 'text-right',
      render: (r) => (
        <div className="flex justify-end gap-2 text-gray-500">
          <button
            onClick={() => setDocStatus(r.id, 'verified')}
            title="Verify"
            className="hover:text-[var(--success)]"
          >
            <i className="fas fa-check" />
          </button>
          <button
            onClick={() => setDocStatus(r.id, 'on_hold')}
            title="Hold"
            className="hover:text-amber-600"
          >
            <i className="fas fa-pause" />
          </button>
          <button
            onClick={() => setDocStatus(r.id, 'rejected')}
            title="Reject"
            className="hover:text-[var(--danger)]"
          >
            <i className="fas fa-xmark" />
          </button>
        </div>
      ),
    },
  ];

  const historyColumns: AXColumn<VerificationLog>[] = [
    {
      key: 'created_at',
      header: 'When',
      render: (r) => (
        <span className="text-xs text-gray-500">
          {r.created_at?.slice(0, 19).replace('T', ' ')}
        </span>
      ),
    },
    {
      key: 'change',
      header: 'Change',
      render: (r) => (
        <span>
          {r.from_status ?? '—'} → <strong>{r.to_status}</strong>
          {r.document_id ? ` (doc #${r.document_id})` : ''}
        </span>
      ),
    },
    { key: 'remarks', header: 'Remarks', render: (r) => r.remarks ?? '—' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-clipboard-check text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Verification</h2>
        </div>
        <div className="w-80">
          <AXSelect
            value={selectedId}
            onChange={(e) => setSelectedId(e.target.value)}
            options={[
              { value: '', label: 'Select an application…' },
              ...apps.map((a) => ({
                value: String(a.id),
                label: `${a.application_number} — ${a.student_name}`,
              })),
            ]}
          />
        </div>
      </div>

      {app && (
        <>
          <div className="erp-card flex flex-wrap items-center gap-3">
            <span className="font-medium">{app.student_name}</span>
            <AXBadge tone="navy">{app.application_number}</AXBadge>
            <span className="text-sm text-gray-500">Verification:</span>
            <AXBadge tone={TONES[app.verification_status] ?? 'gray'}>
              {app.verification_status}
            </AXBadge>
            <div className="ml-auto flex gap-2">
              <button
                onClick={() => setAppStatus('verified')}
                className="rounded-md bg-[var(--success)] px-3 py-1.5 text-sm font-semibold text-white"
              >
                Verify
              </button>
              <button
                onClick={() => setAppStatus('on_hold')}
                className="rounded-md bg-amber-500 px-3 py-1.5 text-sm font-semibold text-white"
              >
                Hold
              </button>
              <button
                onClick={() => setAppStatus('rejected')}
                className="rounded-md bg-[var(--danger)] px-3 py-1.5 text-sm font-semibold text-white"
              >
                Reject
              </button>
            </div>
          </div>

          <h3 className="text-sm font-semibold text-[var(--navy-primary)]">Documents</h3>
          <AXTable
            columns={docColumns}
            rows={app.documents ?? []}
            rowKey={(r) => r.id}
            empty="No documents uploaded."
          />

          <h3 className="text-sm font-semibold text-[var(--navy-primary)]">History</h3>
          <AXTable
            columns={historyColumns}
            rows={history}
            rowKey={(r) => r.id}
            empty="No verification history yet."
          />
        </>
      )}
    </div>
  );
}
