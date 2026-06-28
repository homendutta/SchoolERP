/* Student Transfers — internal (class/section) or external (other school).
 * History is preserved. */
import { useEffect, useState } from 'react';
import { AXBadge, AXForm, AXInput, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { useClasses, useAllSections } from '@features/academic/useReference';
import { studentsApi, type Transfer } from './api';
import { StudentPicker, useStudentList } from './StudentPicker';

const EMPTY = {
  type: 'internal',
  to_class_id: '',
  to_section_id: '',
  transfer_date: '',
  reason: '',
  destination_school: '',
  notes: '',
};

export function TransfersPage() {
  const students = useStudentList();
  const classes = useClasses();
  const sections = useAllSections();
  const [id, setId] = useState('');
  const [rows, setRows] = useState<Transfer[]>([]);
  const [form, setForm] = useState<Record<string, string>>(EMPTY);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = () =>
    id && studentsApi.transfers.list(Number(id)).then((r) => setRows(Array.isArray(r) ? r : []));
  useEffect(() => {
    setRows([]);
    setForm(EMPTY);
    if (id) load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  const save = async () => {
    setSaving(true);
    setError(null);
    try {
      await studentsApi.transfers.create(Number(id), {
        ...form,
        to_class_id: form.to_class_id ? Number(form.to_class_id) : null,
        to_section_id: form.to_section_id ? Number(form.to_section_id) : null,
      });
      setForm(EMPTY);
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed.');
    } finally {
      setSaving(false);
    }
  };

  const columns: AXColumn<Transfer>[] = [
    {
      key: 'type',
      header: 'Type',
      render: (r) => <AXBadge tone={r.type === 'external' ? 'amber' : 'navy'}>{r.type}</AXBadge>,
    },
    { key: 'transfer_date', header: 'Date', render: (r) => r.transfer_date ?? '—' },
    { key: 'reason', header: 'Reason', render: (r) => r.reason ?? '—' },
    { key: 'destination', header: 'Destination', render: (r) => r.destination_school ?? '—' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-right-left text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Transfers</h2>
        </div>
        <StudentPicker value={id} onChange={setId} students={students} />
      </div>

      {id && (
        <>
          <div className="erp-card">
            {error && (
              <div className="mb-3 rounded-md bg-red-50 px-3 py-2 text-sm text-[var(--danger)]">
                {error}
              </div>
            )}
            <AXForm onSubmit={save} submitting={saving} submitLabel="Record Transfer">
              <AXSelect
                label="Type"
                value={form.type}
                onChange={(e) => setForm((f) => ({ ...f, type: e.target.value }))}
                options={[
                  { value: 'internal', label: 'Internal' },
                  { value: 'external', label: 'External' },
                ]}
              />
              {form.type === 'internal' ? (
                <>
                  <AXSelect
                    label="To class"
                    value={form.to_class_id}
                    onChange={(e) => setForm((f) => ({ ...f, to_class_id: e.target.value }))}
                    options={[{ value: '', label: '—' }, ...classes]}
                  />
                  <AXSelect
                    label="To section"
                    value={form.to_section_id}
                    onChange={(e) => setForm((f) => ({ ...f, to_section_id: e.target.value }))}
                    options={[{ value: '', label: '—' }, ...sections]}
                  />
                </>
              ) : (
                <AXInput
                  label="Destination school"
                  value={form.destination_school}
                  onChange={(e) => setForm((f) => ({ ...f, destination_school: e.target.value }))}
                />
              )}
              <AXInput
                label="Transfer date"
                type="date"
                value={form.transfer_date}
                onChange={(e) => setForm((f) => ({ ...f, transfer_date: e.target.value }))}
                required
              />
              <AXInput
                label="Reason"
                value={form.reason}
                onChange={(e) => setForm((f) => ({ ...f, reason: e.target.value }))}
              />
              <AXInput
                label="Notes"
                value={form.notes}
                onChange={(e) => setForm((f) => ({ ...f, notes: e.target.value }))}
              />
            </AXForm>
          </div>
          <AXTable
            columns={columns}
            rows={rows}
            rowKey={(r) => r.id}
            empty="No transfers recorded."
          />
        </>
      )}
    </div>
  );
}
