/* Number Generator — configure sequences, preview the next number, reset. */
import { useEffect, useState } from 'react';
import {
  AXBadge, AXConfirm, AXForm, AXInput, AXModal, AXTable, useConfirm, type AXColumn,
} from '@ui/ax';
import { adminApi, type NumberSequence } from './api';

export function NumberGeneratorPage() {
  const [rows, setRows] = useState<NumberSequence[]>([]);
  const [loading, setLoading] = useState(true);
  const [previews, setPreviews] = useState<Record<string, string>>({});
  const [editing, setEditing] = useState<NumberSequence | null>(null);
  const [form, setForm] = useState<Partial<NumberSequence>>({});
  const [saving, setSaving] = useState(false);
  const { confirmProps, ask } = useConfirm();

  const load = () => {
    setLoading(true);
    adminApi.listSequences().then((r) => setRows(r.data)).finally(() => setLoading(false));
  };
  useEffect(load, []);

  const preview = async (key: string) => {
    const r = await adminApi.previewNumber(key);
    setPreviews((p) => ({ ...p, [key]: r.next }));
  };

  const save = async () => {
    if (!editing) return;
    setSaving(true);
    try {
      await adminApi.updateSequence(editing.id, form);
      setEditing(null);
      load();
    } finally {
      setSaving(false);
    }
  };

  const columns: AXColumn<NumberSequence>[] = [
    { key: 'key', header: 'Key', render: (r) => <span className="font-medium">{r.label ?? r.key}</span> },
    { key: 'format', header: 'Format', render: (r) => <code className="text-xs text-gray-500">{r.prefix}{r.format}{r.suffix}</code> },
    { key: 'current_number', header: 'Current' },
    { key: 'reset_policy', header: 'Reset', render: (r) => <AXBadge tone="navy">{r.reset_policy ?? 'none'}</AXBadge> },
    {
      key: 'preview', header: 'Next', render: (r) => (
        <button onClick={() => preview(r.key)} className="text-[var(--navy-accent)] hover:underline">
          {previews[r.key] ?? 'Preview'}
        </button>
      ),
    },
    {
      key: 'actions', header: '', className: 'text-right', render: (r) => (
        <div className="flex justify-end gap-2 text-gray-500">
          <button onClick={() => { setEditing(r); setForm({ prefix: r.prefix, suffix: r.suffix, padding: r.padding, increment: r.increment, maximum_number: r.maximum_number ?? undefined }); }} title="Configure" className="hover:text-[var(--navy-accent)]"><i className="fas fa-gear" /></button>
          <button onClick={() => ask(`Reset “${r.key}” to its initial number?`, () => adminApi.resetNumber(r.key).then(load))} title="Reset" className="hover:text-[var(--danger)]"><i className="fas fa-rotate-left" /></button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-hashtag text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Number Generator</h2>
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} loading={loading} empty="No sequences configured." />

      <AXModal open={editing !== null} title={`Configure: ${editing?.key ?? ''}`} onClose={() => setEditing(null)}>
        <AXForm onSubmit={save} submitting={saving} onCancel={() => setEditing(null)}>
          <AXInput label="Prefix" value={(form.prefix as string) ?? ''} onChange={(e) => setForm((f) => ({ ...f, prefix: e.target.value }))} />
          <AXInput label="Suffix" value={(form.suffix as string) ?? ''} onChange={(e) => setForm((f) => ({ ...f, suffix: e.target.value }))} />
          <AXInput label="Padding" type="number" value={form.padding ?? 0} onChange={(e) => setForm((f) => ({ ...f, padding: Number(e.target.value) }))} />
          <AXInput label="Increment" type="number" value={form.increment ?? 1} onChange={(e) => setForm((f) => ({ ...f, increment: Number(e.target.value) }))} />
          <AXInput label="Maximum (optional)" type="number" value={form.maximum_number ?? ''} onChange={(e) => setForm((f) => ({ ...f, maximum_number: e.target.value === '' ? null : Number(e.target.value) }))} />
        </AXForm>
      </AXModal>

      <AXConfirm {...confirmProps} confirmLabel="Reset" />
    </div>
  );
}
