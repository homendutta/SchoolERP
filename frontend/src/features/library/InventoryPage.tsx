/* Inventory Verification — audit copies (verified/missing/misplaced/damaged). */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { INVENTORY_STATUSES, libraryApi, type Copy } from './api';

export function InventoryPage() {
  const { user } = useAuth();
  const [copies, setCopies] = useState<Copy[]>([]);
  const [form, setForm] = useState({ copy_id: '', status: 'verified', notes: '' });
  const [report, setReport] = useState<Record<string, number>>({});
  const [saved, setSaved] = useState(false);

  const loadReport = useMemo(
    () => () => {
      if (user?.school_id) libraryApi.inventoryReport(user.school_id).then(setReport);
    },
    [user?.school_id]
  );

  useEffect(() => {
    libraryApi.copies
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setCopies(r.data));
  }, [user?.school_id]);
  useEffect(() => {
    loadReport();
  }, [loadReport]);

  const submit = async () => {
    await libraryApi.recordInventory({
      copy_id: Number(form.copy_id),
      status: form.status,
      notes: form.notes || null,
    });
    setForm({ copy_id: '', status: 'verified', notes: '' });
    setSaved(true);
    loadReport();
  };

  const reportRows = INVENTORY_STATUSES.map((s) => ({ status: s, count: report[s] ?? 0 }));
  const columns: AXColumn<{ status: string; count: number }>[] = [
    {
      key: 'status',
      header: 'Status',
      render: (r) => <span className="font-medium capitalize">{r.status}</span>,
    },
    { key: 'count', header: 'Copies', render: (r) => <AXBadge tone="navy">{r.count}</AXBadge> },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-clipboard-check text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Inventory Verification</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-64">
          <AXSelect
            label="Copy"
            value={form.copy_id}
            onChange={(e) => {
              setSaved(false);
              setForm((f) => ({ ...f, copy_id: e.target.value }));
            }}
            options={[
              { value: '', label: 'Select copy…' },
              ...copies.map((c) => ({
                value: String(c.id),
                label: `${c.copy_number} — ${c.book ?? ''}`,
              })),
            ]}
          />
        </div>
        <div className="w-44">
          <AXSelect
            label="Result"
            value={form.status}
            onChange={(e) => setForm((f) => ({ ...f, status: e.target.value }))}
            options={INVENTORY_STATUSES.map((s) => ({ value: s, label: s }))}
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
          disabled={!form.copy_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          <i className="fas fa-check mr-1" /> Record
        </button>
        {saved && <AXBadge tone="green">Recorded</AXBadge>}
      </div>

      <div className="erp-card">
        <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
          Inventory report (latest status per copy)
        </h3>
        <AXTable
          columns={columns}
          rows={reportRows}
          rowKey={(r) => r.status}
          empty="No checks yet."
        />
      </div>
    </div>
  );
}
