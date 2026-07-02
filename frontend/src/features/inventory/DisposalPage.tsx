/* Disposal — sold/scrapped/donated/written off; assets never deleted. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import {
  AXBadge,
  AXInput,
  AXPagination,
  AXSelect,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { DISPOSAL_METHODS, inventoryApi, type Asset, type Ref } from './api';

export function DisposalPage() {
  const { user } = useAuth();
  const [assets, setAssets] = useState<Asset[]>([]);
  const [form, setForm] = useState({ asset_id: '', method: 'scrapped', value: '', reason: '' });
  const [rows, setRows] = useState<Ref[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const loadAssets = () =>
    inventoryApi.assets
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setAssets(r.data.filter((a) => a.status !== 'disposed')));
  const load = useMemo(
    () => () =>
      inventoryApi.disposals({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );

  useEffect(() => {
    loadAssets();
  }, [user?.school_id]); // eslint-disable-line react-hooks/exhaustive-deps
  useEffect(() => {
    load();
  }, [load]);

  const submit = async () => {
    await inventoryApi.dispose({
      asset_id: Number(form.asset_id),
      method: form.method,
      value: form.value ? Number(form.value) : null,
      reason: form.reason || null,
    });
    setForm({ asset_id: '', method: 'scrapped', value: '', reason: '' });
    loadAssets();
    load();
  };

  const columns: AXColumn<Ref>[] = [
    {
      key: 'asset',
      header: 'Asset',
      render: (r) => (
        <span className="font-medium">
          {String((r.asset as { asset_number?: string })?.asset_number ?? r.asset_id)}
        </span>
      ),
    },
    {
      key: 'method',
      header: 'Method',
      render: (r) => <AXBadge tone="gray">{String(r.method).replace('_', ' ')}</AXBadge>,
    },
    { key: 'value', header: 'Value', render: (r) => String(r.value ?? '—') },
    { key: 'date', header: 'Date', render: (r) => String(r.disposal_date ?? '—') },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-trash-can text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Disposal</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-52">
          <AXSelect
            label="Asset"
            value={form.asset_id}
            onChange={(e) => setForm((f) => ({ ...f, asset_id: e.target.value }))}
            options={[
              { value: '', label: 'Select…' },
              ...assets.map((a) => ({ value: String(a.id), label: a.asset_number })),
            ]}
          />
        </div>
        <div className="w-40">
          <AXSelect
            label="Method"
            value={form.method}
            onChange={(e) => setForm((f) => ({ ...f, method: e.target.value }))}
            options={DISPOSAL_METHODS.map((m) => ({ value: m, label: m.replace('_', ' ') }))}
          />
        </div>
        <div className="w-28">
          <AXInput
            label="Value"
            type="number"
            value={form.value}
            onChange={(e) => setForm((f) => ({ ...f, value: e.target.value }))}
          />
        </div>
        <div className="w-44">
          <AXInput
            label="Reason"
            value={form.reason}
            onChange={(e) => setForm((f) => ({ ...f, reason: e.target.value }))}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.asset_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Dispose
        </button>
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No disposals yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
