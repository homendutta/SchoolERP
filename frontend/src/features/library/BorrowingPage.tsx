/* Borrowing — issue a physical copy to a borrower resolved via Identity Number. */
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
import { libraryApi, type Borrowing, type Copy } from './api';

const TONES: Record<string, 'navy' | 'amber' | 'green' | 'red'> = {
  borrowed: 'navy',
  overdue: 'red',
  returned: 'green',
  lost: 'red',
};

export function BorrowingPage() {
  const { user } = useAuth();
  const [copies, setCopies] = useState<Copy[]>([]);
  const [form, setForm] = useState({ identity_number: '', copy_id: '' });
  const [error, setError] = useState<string | null>(null);
  const [rows, setRows] = useState<Borrowing[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const loadCopies = () =>
    libraryApi.copies
      .list({ filter: { school_id: user?.school_id, status: 'available' }, per_page: 200 })
      .then((r) => setCopies(r.data));

  const load = useMemo(
    () => () =>
      libraryApi
        .borrowings({ page, filter: { school_id: user?.school_id, status: 'borrowed' } })
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        }),
    [page, user?.school_id]
  );

  useEffect(() => {
    loadCopies();
  }, [user?.school_id]); // eslint-disable-line react-hooks/exhaustive-deps
  useEffect(() => {
    load();
  }, [load]);

  const submit = async () => {
    setError(null);
    try {
      await libraryApi.borrow({
        school_id: user?.school_id,
        identity_number: form.identity_number,
        copy_id: Number(form.copy_id),
      });
      setForm({ identity_number: '', copy_id: '' });
      loadCopies();
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not borrow.');
    }
  };

  const columns: AXColumn<Borrowing>[] = [
    { key: 'book', header: 'Book', render: (r) => <span className="font-medium">{r.book}</span> },
    { key: 'copy', header: 'Copy', render: (r) => r.copy_number },
    {
      key: 'borrower',
      header: 'Borrower',
      render: (r) => `${r.borrower ?? ''} (${r.identity_number ?? ''})`,
    },
    { key: 'due', header: 'Due', render: (r) => r.due_date },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'navy'}>{r.status}</AXBadge>,
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-book-open-reader text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Borrowing</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-52">
          <AXInput
            label="Borrower identity number"
            value={form.identity_number}
            onChange={(e) => setForm((f) => ({ ...f, identity_number: e.target.value }))}
          />
        </div>
        <div className="w-64">
          <AXSelect
            label="Available copy"
            value={form.copy_id}
            onChange={(e) => setForm((f) => ({ ...f, copy_id: e.target.value }))}
            options={[
              { value: '', label: 'Select copy…' },
              ...copies.map((c) => ({
                value: String(c.id),
                label: `${c.copy_number} — ${c.book ?? ''}`,
              })),
            ]}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.identity_number || !form.copy_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          <i className="fas fa-check mr-1" /> Issue
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No active borrowings." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
