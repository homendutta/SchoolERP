/* Document History — immutable generated documents; regenerate creates a new version. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { documentsApi, type Ref } from './api';

const TONES: Record<string, 'green' | 'navy' | 'red'> = {
  generated: 'navy',
  issued: 'green',
  revoked: 'red',
};

export function HistoryPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Ref[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const load = useMemo(
    () => () =>
      documentsApi.history({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<Ref>[] = [
    {
      key: 'no',
      header: 'Document #',
      render: (r) => <span className="font-medium">{String(r.document_number)}</span>,
    },
    {
      key: 'type',
      header: 'Certificate',
      render: (r) => String((r.certificateType as { name?: string })?.name ?? '—'),
    },
    { key: 'version', header: 'Version', render: (r) => `v${String(r.version ?? 1)}` },
    {
      key: 'code',
      header: 'Verify code',
      render: (r) => (
        <code className="text-xs text-gray-500">{String(r.verification_code ?? '—')}</code>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'navy'}>{String(r.status)}</AXBadge>,
    },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          className="text-xs font-semibold text-[var(--navy-accent)]"
          onClick={() => documentsApi.regenerate(r.id).then(load)}
        >
          Regenerate
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-clock-rotate-left text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Document History</h2>
      </div>
      <AXTable
        columns={columns}
        rows={rows}
        rowKey={(r) => r.id}
        empty="No documents generated yet."
      />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
