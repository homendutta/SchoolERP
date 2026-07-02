/* Stock Movements — append-only; balances snapshot per movement. */
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
import { MOVEMENT_TYPES, inventoryApi, type Ref } from './api';

const TONES: Record<string, 'green' | 'red' | 'amber' | 'navy'> = {
  in: 'green',
  out: 'red',
  adjustment: 'amber',
  transfer: 'navy',
};

export function StockMovementsPage() {
  const { user } = useAuth();
  const [consumables, setConsumables] = useState<Ref[]>([]);
  const [form, setForm] = useState({ consumable_id: '', type: 'in', quantity: '', reference: '' });
  const [rows, setRows] = useState<Ref[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const loadConsumables = () =>
    inventoryApi.consumables
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setConsumables(r.data));
  const load = useMemo(
    () => () =>
      inventoryApi.movements({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );

  useEffect(() => {
    loadConsumables();
  }, [user?.school_id]); // eslint-disable-line react-hooks/exhaustive-deps
  useEffect(() => {
    load();
  }, [load]);

  const submit = async () => {
    await inventoryApi.recordMovement({
      consumable_id: Number(form.consumable_id),
      type: form.type,
      quantity: Number(form.quantity),
      reference: form.reference || null,
    });
    setForm({ consumable_id: '', type: 'in', quantity: '', reference: '' });
    loadConsumables();
    load();
  };

  const columns: AXColumn<Ref>[] = [
    {
      key: 'consumable',
      header: 'Item',
      render: (r) => (
        <span className="font-medium">
          {String((r.consumable as { name?: string })?.name ?? r.consumable_id)}
        </span>
      ),
    },
    {
      key: 'type',
      header: 'Type',
      render: (r) => <AXBadge tone={TONES[String(r.type)] ?? 'navy'}>{String(r.type)}</AXBadge>,
    },
    { key: 'qty', header: 'Qty', render: (r) => String(r.quantity) },
    { key: 'balance', header: 'Balance after', render: (r) => String(r.balance_after) },
    { key: 'ref', header: 'Reference', render: (r) => String(r.reference ?? '—') },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-right-left text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Stock Movements</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-52">
          <AXSelect
            label="Consumable"
            value={form.consumable_id}
            onChange={(e) => setForm((f) => ({ ...f, consumable_id: e.target.value }))}
            options={[
              { value: '', label: 'Select…' },
              ...consumables.map((c) => ({
                value: String(c.id),
                label: `${c.name} (${c.current_stock} ${c.unit})`,
              })),
            ]}
          />
        </div>
        <div className="w-36">
          <AXSelect
            label="Type"
            value={form.type}
            onChange={(e) => setForm((f) => ({ ...f, type: e.target.value }))}
            options={MOVEMENT_TYPES.map((t) => ({ value: t, label: t }))}
          />
        </div>
        <div className="w-28">
          <AXInput
            label="Quantity"
            type="number"
            value={form.quantity}
            onChange={(e) => setForm((f) => ({ ...f, quantity: e.target.value }))}
          />
        </div>
        <div className="w-40">
          <AXInput
            label="Reference"
            value={form.reference}
            onChange={(e) => setForm((f) => ({ ...f, reference: e.target.value }))}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.consumable_id || !form.quantity}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Record
        </button>
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No movements yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
