/* Reservations — queue against a title; order preserved. */
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
import { libraryApi, type Book, type Reservation } from './api';

const TONES: Record<string, 'amber' | 'green' | 'gray' | 'navy'> = {
  pending: 'amber',
  available: 'green',
  fulfilled: 'navy',
  cancelled: 'gray',
  expired: 'gray',
};

export function ReservationsPage() {
  const { user } = useAuth();
  const [books, setBooks] = useState<Book[]>([]);
  const [form, setForm] = useState({ identity_number: '', book_id: '' });
  const [error, setError] = useState<string | null>(null);
  const [rows, setRows] = useState<Reservation[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const load = useMemo(
    () => () =>
      libraryApi.reservations({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );
  useEffect(() => {
    libraryApi.catalog
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setBooks(r.data));
  }, [user?.school_id]);
  useEffect(() => {
    load();
  }, [load]);

  const submit = async () => {
    setError(null);
    try {
      await libraryApi.reserve({
        school_id: user?.school_id,
        identity_number: form.identity_number,
        book_id: Number(form.book_id),
      });
      setForm({ identity_number: '', book_id: '' });
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not reserve.');
    }
  };

  const columns: AXColumn<Reservation>[] = [
    { key: 'pos', header: '#', render: (r) => r.queue_position },
    { key: 'book', header: 'Book', render: (r) => <span className="font-medium">{r.book}</span> },
    {
      key: 'borrower',
      header: 'Borrower',
      render: (r) => `${r.borrower ?? ''} (${r.identity_number ?? ''})`,
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'act',
      header: '',
      render: (r) =>
        r.status === 'pending' || r.status === 'available' ? (
          <button
            onClick={() => libraryApi.cancelReservation(r.id).then(load)}
            className="text-xs font-semibold text-[var(--danger)]"
          >
            Cancel
          </button>
        ) : null,
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-bookmark text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Reservations</h2>
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
            label="Title"
            value={form.book_id}
            onChange={(e) => setForm((f) => ({ ...f, book_id: e.target.value }))}
            options={[
              { value: '', label: 'Select title…' },
              ...books.map((b) => ({ value: String(b.id), label: b.title })),
            ]}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.identity_number || !form.book_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          <i className="fas fa-bookmark mr-1" /> Reserve
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No reservations." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
