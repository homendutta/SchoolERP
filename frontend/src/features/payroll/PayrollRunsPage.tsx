/* Payroll Runs — create a run, process (idempotent), then lock (immutable). */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXPagination, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { payrollApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'amber' | 'green' | 'navy' | 'red'> = {
  draft: 'gray',
  processing: 'amber',
  completed: 'green',
  locked: 'navy',
  cancelled: 'red',
};

export function PayrollRunsPage() {
  const { user } = useAuth();
  const now = new Date();
  const [form, setForm] = useState({
    label: '',
    period_year: now.getFullYear(),
    period_month: now.getMonth() + 1,
  });
  const [error, setError] = useState<string | null>(null);
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
      payrollApi.runs({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const create = async () => {
    setError(null);
    try {
      await payrollApi.createRun({ school_id: user?.school_id, ...form });
      setForm((f) => ({ ...f, label: '' }));
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not create run.');
    }
  };

  const act = (fn: Promise<unknown>) =>
    fn.then(load).catch((e) => setError(e instanceof Error ? e.message : 'Action failed.'));

  const columns: AXColumn<Ref>[] = [
    {
      key: 'run',
      header: 'Run #',
      render: (r) => <span className="font-medium">{String(r.run_number ?? r.id)}</span>,
    },
    {
      key: 'period',
      header: 'Period',
      render: (r) => `${r.period_year}-${String(r.period_month).padStart(2, '0')}`,
    },
    { key: 'count', header: 'Employees', render: (r) => String(r.processed_count ?? 0) },
    { key: 'net', header: 'Net total', render: (r) => String(r.total_net ?? 0) },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <div className="flex gap-2 text-xs font-semibold">
          {(r.status === 'draft' || r.status === 'completed') && (
            <button
              onClick={() => act(payrollApi.processRun(r.id))}
              className="text-[var(--navy-accent)]"
            >
              Process
            </button>
          )}
          {r.status === 'completed' && (
            <button
              onClick={() => act(payrollApi.lockRun(r.id))}
              className="text-[var(--navy-primary)]"
            >
              Lock
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-list-check text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Payroll Runs</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-52">
          <AXInput
            label="Label"
            value={form.label}
            onChange={(e) => setForm((f) => ({ ...f, label: e.target.value }))}
          />
        </div>
        <div className="w-28">
          <AXInput
            label="Year"
            type="number"
            value={String(form.period_year)}
            onChange={(e) => setForm((f) => ({ ...f, period_year: Number(e.target.value) }))}
          />
        </div>
        <div className="w-28">
          <AXInput
            label="Month"
            type="number"
            value={String(form.period_month)}
            onChange={(e) => setForm((f) => ({ ...f, period_month: Number(e.target.value) }))}
          />
        </div>
        <button
          onClick={create}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white"
        >
          Create run
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No payroll runs yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
