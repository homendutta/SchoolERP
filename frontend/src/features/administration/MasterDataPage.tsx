/* Reusable Master Data manager — one page manages every list (blood groups,
 * departments, designations, fee categories, …) by selecting a type. Built
 * entirely from the AX component library. */
import { useEffect, useMemo, useState } from 'react';
import {
  AXBadge, AXConfirm, AXForm, AXInput, AXModal, AXPagination, AXSearch, AXSelect,
  AXStatus, AXTable, useConfirm, type AXColumn, type AXPageMeta,
} from '@ui/ax';
import { adminApi, type MasterDataType, type MasterDataValue } from './api';

const EMPTY = { label: '', value: '', sort_order: 0, is_active: true };

export function MasterDataPage() {
  const [types, setTypes] = useState<MasterDataType[]>([]);
  const [typeId, setTypeId] = useState<string>('');
  const [rows, setRows] = useState<MasterDataValue[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({ current_page: 1, last_page: 1, total: 0, per_page: 15 });
  const [loading, setLoading] = useState(false);
  const [q, setQ] = useState('');
  const [page, setPage] = useState(1);
  const [selected, setSelected] = useState<Array<string | number>>([]);
  const [modal, setModal] = useState<{ open: boolean; editing: MasterDataValue | null }>({ open: false, editing: null });
  const [form, setForm] = useState<typeof EMPTY & { id?: number; type_id?: number }>(EMPTY);
  const [saving, setSaving] = useState(false);
  const { confirmProps, ask } = useConfirm();

  useEffect(() => {
    adminApi.listTypes().then((r) => {
      setTypes(r.data);
      if (r.data[0]) setTypeId(String(r.data[0].id));
    });
  }, []);

  const load = useMemo(
    () => () => {
      if (!typeId) return;
      setLoading(true);
      adminApi
        .listValues({ filter: { type_id: typeId }, search: { label: q }, sort: 'sort_order', page })
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        })
        .finally(() => setLoading(false));
    },
    [typeId, q, page]
  );

  useEffect(() => {
    load();
    setSelected([]);
  }, [load]);

  const openCreate = () => {
    setForm({ ...EMPTY, type_id: Number(typeId) });
    setModal({ open: true, editing: null });
  };
  const openEdit = (row: MasterDataValue) => {
    setForm({ id: row.id, type_id: row.type_id, label: row.label, value: row.value, sort_order: row.sort_order, is_active: row.is_active });
    setModal({ open: true, editing: row });
  };

  const save = async () => {
    setSaving(true);
    try {
      if (form.id) await adminApi.updateValue(form.id, form);
      else await adminApi.createValue({ ...form, type_id: Number(typeId) });
      setModal({ open: false, editing: null });
      load();
    } finally {
      setSaving(false);
    }
  };

  const columns: AXColumn<MasterDataValue>[] = [
    { key: 'label', header: 'Label', render: (r) => <span className="font-medium">{r.label}</span> },
    { key: 'value', header: 'Value', render: (r) => <code className="text-xs text-gray-500">{r.value}</code> },
    { key: 'sort_order', header: 'Order' },
    { key: 'is_active', header: 'Status', render: (r) => <AXStatus active={r.is_active && !r.archived} inactiveLabel={r.archived ? 'Archived' : 'Inactive'} /> },
    {
      key: 'actions', header: '', className: 'text-right',
      render: (r) => (
        <div className="flex justify-end gap-2 text-gray-500">
          <button onClick={() => openEdit(r)} title="Edit" className="hover:text-[var(--navy-accent)]"><i className="fas fa-pen" /></button>
          {r.archived ? (
            <button onClick={() => adminApi.restoreValue(r.id).then(load)} title="Restore" className="hover:text-[var(--success)]"><i className="fas fa-rotate-left" /></button>
          ) : (
            <button onClick={() => ask(`Archive “${r.label}”?`, () => adminApi.archiveValue(r.id).then(load))} title="Archive" className="hover:text-[var(--danger)]"><i className="fas fa-box-archive" /></button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-database text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Master Data</h2>
          <AXBadge tone="navy">{meta.total} values</AXBadge>
        </div>
        <button onClick={openCreate} className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white hover:bg-[var(--navy-hover)]">
          <i className="fas fa-plus mr-1" /> Add Value
        </button>
      </div>

      <div className="flex flex-wrap items-center gap-3">
        <div className="w-56">
          <AXSelect value={typeId} onChange={(e) => { setTypeId(e.target.value); setPage(1); }} options={types.map((t) => ({ value: String(t.id), label: t.name }))} />
        </div>
        <div className="min-w-[16rem] flex-1"><AXSearch onSearch={(t) => { setQ(t); setPage(1); }} placeholder="Search values…" /></div>
        {selected.length > 0 && (
          <button
            onClick={() => ask(`Delete ${selected.length} value(s)?`, () => adminApi.bulkDeleteValues(selected.map(Number)).then(load))}
            className="rounded-md bg-[var(--danger)] px-3 py-2 text-sm font-semibold text-white"
          >
            <i className="fas fa-trash mr-1" /> Delete ({selected.length})
          </button>
        )}
      </div>

      <AXTable
        columns={columns}
        rows={rows}
        rowKey={(r) => r.id}
        loading={loading}
        selectable
        selected={selected}
        onToggle={(id) => setSelected((s) => (s.includes(id) ? s.filter((x) => x !== id) : [...s, id]))}
        onToggleAll={(checked) => setSelected(checked ? rows.map((r) => r.id) : [])}
      />
      <AXPagination meta={meta} onPage={setPage} />

      <AXModal
        open={modal.open}
        title={modal.editing ? 'Edit Value' : 'Add Value'}
        onClose={() => setModal({ open: false, editing: null })}
      >
        <AXForm onSubmit={save} submitting={saving} onCancel={() => setModal({ open: false, editing: null })}>
          <AXInput label="Label" value={form.label} onChange={(e) => setForm((f) => ({ ...f, label: e.target.value }))} required />
          <AXInput label="Value" value={form.value} onChange={(e) => setForm((f) => ({ ...f, value: e.target.value }))} required />
          <AXInput label="Sort order" type="number" value={form.sort_order} onChange={(e) => setForm((f) => ({ ...f, sort_order: Number(e.target.value) }))} />
          <label className="flex items-center gap-2 text-sm">
            <input type="checkbox" checked={form.is_active} onChange={(e) => setForm((f) => ({ ...f, is_active: e.target.checked }))} /> Active
          </label>
        </AXForm>
      </AXModal>

      <AXConfirm {...confirmProps} />
    </div>
  );
}
