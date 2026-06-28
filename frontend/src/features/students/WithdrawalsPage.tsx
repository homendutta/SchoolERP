/* Student Withdrawals — never deletes data; records the withdrawal and moves
 * status to Withdrawn. */
import { useEffect, useState } from 'react';
import { AXForm, AXInput, AXTable, type AXColumn } from '@ui/ax';
import { studentsApi, type Withdrawal } from './api';
import { StudentPicker, useStudentList } from './StudentPicker';

const EMPTY = { withdraw_date: '', reason: '', remarks: '' };

export function WithdrawalsPage() {
  const students = useStudentList();
  const [id, setId] = useState('');
  const [rows, setRows] = useState<Withdrawal[]>([]);
  const [form, setForm] = useState<Record<string, string>>(EMPTY);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = () =>
    id && studentsApi.withdrawals.list(Number(id)).then((r) => setRows(Array.isArray(r) ? r : []));
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
      await studentsApi.withdrawals.create(Number(id), form);
      setForm(EMPTY);
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed.');
    } finally {
      setSaving(false);
    }
  };

  const columns: AXColumn<Withdrawal>[] = [
    { key: 'withdraw_date', header: 'Date', render: (r) => r.withdraw_date ?? '—' },
    { key: 'reason', header: 'Reason', render: (r) => r.reason ?? '—' },
    { key: 'remarks', header: 'Remarks', render: (r) => r.remarks ?? '—' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-user-xmark text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Withdrawals</h2>
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
            <AXForm onSubmit={save} submitting={saving} submitLabel="Record Withdrawal">
              <AXInput
                label="Withdraw date"
                type="date"
                value={form.withdraw_date}
                onChange={(e) => setForm((f) => ({ ...f, withdraw_date: e.target.value }))}
                required
              />
              <AXInput
                label="Reason"
                value={form.reason}
                onChange={(e) => setForm((f) => ({ ...f, reason: e.target.value }))}
              />
              <AXInput
                label="Remarks"
                value={form.remarks}
                onChange={(e) => setForm((f) => ({ ...f, remarks: e.target.value }))}
              />
            </AXForm>
          </div>
          <AXTable
            columns={columns}
            rows={rows}
            rowKey={(r) => r.id}
            empty="No withdrawals recorded."
          />
        </>
      )}
    </div>
  );
}
