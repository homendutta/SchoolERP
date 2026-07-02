/* Asset Transfers — new records; full history preserved. */
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
import { ASSIGN_TARGETS, inventoryApi, type Asset, type Ref } from './api';

export function AssetTransfersPage() {
  const { user } = useAuth();
  const [assets, setAssets] = useState<Asset[]>([]);
  const [form, setForm] = useState({
    asset_id: '',
    target_type: 'room',
    target_label: '',
    reason: '',
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
      inventoryApi.transfers({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );
  useEffect(() => {
    inventoryApi.assets
      .list({ filter: { school_id: user?.school_id, status: 'assigned' }, per_page: 500 })
      .then((r) => setAssets(r.data));
  }, [user?.school_id]);
  useEffect(() => {
    load();
  }, [load]);

  const submit = async () => {
    setError(null);
    try {
      await inventoryApi.transfer({
        asset_id: Number(form.asset_id),
        target_type: form.target_type,
        target_label: form.target_label || null,
        transfer_type: form.target_type,
        reason: form.reason || null,
      });
      setForm({ asset_id: '', target_type: 'room', target_label: '', reason: '' });
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not transfer.');
    }
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
    { key: 'from', header: 'From', render: (r) => String(r.from_label ?? '—') },
    { key: 'to', header: 'To', render: (r) => String(r.to_label ?? '—') },
    { key: 'date', header: 'Date', render: (r) => String(r.transfer_date ?? '—') },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-arrows-turn-right text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Asset Transfers</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-52">
          <AXSelect
            label="Asset"
            value={form.asset_id}
            onChange={(e) => setForm((f) => ({ ...f, asset_id: e.target.value }))}
            options={[
              { value: '', label: 'Select (assigned)…' },
              ...assets.map((a) => ({ value: String(a.id), label: a.asset_number })),
            ]}
          />
        </div>
        <div className="w-40">
          <AXSelect
            label="New target"
            value={form.target_type}
            onChange={(e) => setForm((f) => ({ ...f, target_type: e.target.value }))}
            options={ASSIGN_TARGETS.map((t) => ({ value: t, label: t }))}
          />
        </div>
        <div className="w-44">
          <AXInput
            label="Target label"
            value={form.target_label}
            onChange={(e) => setForm((f) => ({ ...f, target_label: e.target.value }))}
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
          Transfer
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No transfers yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
