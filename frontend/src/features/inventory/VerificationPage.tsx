/* Physical Verification — historical audit records + latest-status report. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { VERIFICATION_STATUSES, inventoryApi, type Asset } from './api';

export function VerificationPage() {
  const { user } = useAuth();
  const [assets, setAssets] = useState<Asset[]>([]);
  const [form, setForm] = useState({ asset_id: '', status: 'verified', notes: '' });
  const [report, setReport] = useState<Record<string, number>>({});
  const [saved, setSaved] = useState(false);

  const loadReport = useMemo(
    () => () => inventoryApi.verificationReport(user?.school_id ?? undefined).then(setReport),
    [user?.school_id]
  );

  useEffect(() => {
    inventoryApi.assets
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setAssets(r.data));
  }, [user?.school_id]);
  useEffect(() => {
    loadReport();
  }, [loadReport]);

  const submit = async () => {
    await inventoryApi.verify({
      asset_id: Number(form.asset_id),
      status: form.status,
      notes: form.notes || null,
    });
    setForm({ asset_id: '', status: 'verified', notes: '' });
    setSaved(true);
    loadReport();
  };

  const reportRows = VERIFICATION_STATUSES.map((s) => ({ status: s, count: report[s] ?? 0 }));
  const columns: AXColumn<{ status: string; count: number }>[] = [
    {
      key: 'status',
      header: 'Status',
      render: (r) => <span className="font-medium capitalize">{r.status}</span>,
    },
    { key: 'count', header: 'Assets', render: (r) => <AXBadge tone="navy">{r.count}</AXBadge> },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-clipboard-check text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Physical Verification</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-56">
          <AXSelect
            label="Asset"
            value={form.asset_id}
            onChange={(e) => {
              setSaved(false);
              setForm((f) => ({ ...f, asset_id: e.target.value }));
            }}
            options={[
              { value: '', label: 'Select…' },
              ...assets.map((a) => ({ value: String(a.id), label: a.asset_number })),
            ]}
          />
        </div>
        <div className="w-44">
          <AXSelect
            label="Result"
            value={form.status}
            onChange={(e) => setForm((f) => ({ ...f, status: e.target.value }))}
            options={VERIFICATION_STATUSES.map((s) => ({ value: s, label: s }))}
          />
        </div>
        <div className="w-52">
          <AXInput
            label="Notes"
            value={form.notes}
            onChange={(e) => setForm((f) => ({ ...f, notes: e.target.value }))}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.asset_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Record
        </button>
        {saved && <AXBadge tone="green">Recorded</AXBadge>}
      </div>

      <div className="erp-card">
        <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
          Verification report (latest status per asset)
        </h3>
        <AXTable
          columns={columns}
          rows={reportRows}
          rowKey={(r) => r.status}
          empty="No verifications yet."
        />
      </div>
    </div>
  );
}
