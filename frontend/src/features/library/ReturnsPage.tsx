/* Returns — process a return; the engine computes late days + fine. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import {
  AXBadge,
  AXInput,
  AXModal,
  AXPagination,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { libraryApi, type Borrowing } from './api';

export function ReturnsPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Borrowing[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);
  const [active, setActive] = useState<Borrowing | null>(null);
  const [damage, setDamage] = useState('');
  const [result, setResult] = useState<string | null>(null);

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

  const submit = async () => {
    if (!active) return;
    const res = await libraryApi.returnCopy({
      borrowing_id: active.id,
      damage_notes: damage || null,
    });
    setResult(`Returned '${res.book}'. Late days: ${res.late_days}, fine: ${res.fine_amount}.`);
    setActive(null);
    setDamage('');
    load();
  };

  const columns: AXColumn<Borrowing>[] = [
    { key: 'book', header: 'Book', render: (r) => <span className="font-medium">{r.book}</span> },
    { key: 'copy', header: 'Copy', render: (r) => r.copy_number },
    { key: 'borrower', header: 'Borrower', render: (r) => r.borrower ?? r.identity_number },
    { key: 'due', header: 'Due', render: (r) => r.due_date },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          onClick={() => setActive(r)}
          className="text-xs font-semibold text-[var(--navy-accent)]"
        >
          Return
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-rotate-left text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Returns</h2>
      </div>
      {result && <AXBadge tone="green">{result}</AXBadge>}

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="Nothing to return." />
      <AXPagination meta={meta} onPage={setPage} />

      <AXModal
        open={active !== null}
        title={`Return: ${active?.book ?? ''}`}
        onClose={() => setActive(null)}
      >
        <div className="space-y-3">
          <p className="text-sm text-gray-500">
            Fine (if overdue) is calculated automatically. Collection is handled by Finance.
          </p>
          <AXInput
            label="Damage notes (optional)"
            value={damage}
            onChange={(e) => setDamage(e.target.value)}
          />
          <div className="flex justify-end gap-2">
            <button
              onClick={() => setActive(null)}
              className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
            >
              Cancel
            </button>
            <button
              onClick={submit}
              className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
            >
              Confirm return
            </button>
          </div>
        </div>
      </AXModal>
    </div>
  );
}
