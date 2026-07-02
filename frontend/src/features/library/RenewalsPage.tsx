/* Renewals — extend the due date (blocked when reserved / limit reached). */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { libraryApi, type Borrowing } from './api';

export function RenewalsPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Borrowing[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);
  const [msg, setMsg] = useState<{ tone: 'green' | 'red'; text: string } | null>(null);

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
    load();
  }, [load]);

  const renew = async (b: Borrowing) => {
    setMsg(null);
    try {
      const res = await libraryApi.renew({ borrowing_id: b.id });
      setMsg({
        tone: 'green',
        text: `Renewed '${res.book}' → due ${res.due_date} (renewal ${res.renewals_count}).`,
      });
      load();
    } catch (e) {
      setMsg({ tone: 'red', text: e instanceof Error ? e.message : 'Could not renew.' });
    }
  };

  const columns: AXColumn<Borrowing>[] = [
    { key: 'book', header: 'Book', render: (r) => <span className="font-medium">{r.book}</span> },
    { key: 'copy', header: 'Copy', render: (r) => r.copy_number },
    { key: 'borrower', header: 'Borrower', render: (r) => r.borrower ?? r.identity_number },
    { key: 'due', header: 'Due', render: (r) => r.due_date },
    { key: 'renewals', header: 'Renewals', render: (r) => r.renewals_count },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          onClick={() => renew(r)}
          className="text-xs font-semibold text-[var(--navy-accent)]"
        >
          Renew
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-arrows-rotate text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Renewals</h2>
      </div>
      {msg && <AXBadge tone={msg.tone}>{msg.text}</AXBadge>}

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No active borrowings." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
