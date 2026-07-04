/* Export History — the export queue + completed/failed runs. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { reportsApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'amber' | 'green' | 'red'> = {
  queued: 'gray',
  processing: 'amber',
  completed: 'green',
  failed: 'red',
};

export function ExportsPage() {
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
      reportsApi
        .exports({ page, filter: { school_id: user?.school_id }, sort: '-id' })
        .then((r) => {
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
      key: 'report',
      header: 'Report',
      render: (r) => <span className="font-medium">{String(r.report_name ?? r.report_key)}</span>,
    },
    { key: 'format', header: 'Format', render: (r) => String(r.format).toUpperCase() },
    { key: 'rows', header: 'Rows', render: (r) => String(r.row_count ?? 0) },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
    { key: 'when', header: 'When', render: (r) => String(r.created_at ?? '—').slice(0, 16) },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-file-export text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Export History</h2>
      </div>
      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No exports yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
