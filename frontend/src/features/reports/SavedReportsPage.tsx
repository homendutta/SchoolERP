/* Saved Reports — reusable filter/column/sort configurations. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXPagination, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { reportsApi, type Ref } from './api';

export function SavedReportsPage() {
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
      reportsApi.saved.list({ page, filter: { school_id: user?.school_id } }).then((r) => {
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
      key: 'name',
      header: 'Name',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    {
      key: 'report',
      header: 'Report',
      render: (r) => <code className="text-xs text-gray-500">{String(r.report_key)}</code>,
    },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          onClick={() => reportsApi.saved.archive(r.id).then(load)}
          className="text-xs font-semibold text-[var(--danger)]"
        >
          Delete
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-bookmark text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Saved Reports</h2>
      </div>
      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No saved reports yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
