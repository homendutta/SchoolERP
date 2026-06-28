/* Refunds — never delete payments; create independent refund transactions. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import {
  AXBadge,
  AXInput,
  AXModal,
  AXPagination,
  AXSelect,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { financeApi, type Payment, type Refund } from './api';

export function RefundsPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Refund[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);
  const [open, setOpen] = useState(false);

  const load = useMemo(
    () => () => {
      financeApi.refunds({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      });
    },
    [page, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<Refund>[] = [
    {
      key: 'txn',
      header: 'Transaction',
      render: (r) => <code className="text-xs text-gray-500">{r.transaction_number}</code>,
    },
    { key: 'student', header: 'Student', render: (r) => r.student ?? '—' },
    { key: 'receipt', header: 'For Receipt', render: (r) => r.receipt_number ?? '—' },
    { key: 'amount', header: 'Amount', render: (r) => `₹${r.amount}` },
    { key: 'type', header: 'Type', render: (r) => <AXBadge tone="navy">{r.type}</AXBadge> },
    { key: 'reason', header: 'Reason', render: (r) => r.reason ?? '—' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <i className="fas fa-rotate-left text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Refunds</h2>
        </div>
        <button
          onClick={() => setOpen(true)}
          className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
        >
          <i className="fas fa-plus mr-1" /> Issue refund
        </button>
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No refunds yet." />
      <AXPagination meta={meta} onPage={setPage} />

      {open && (
        <RefundModal
          schoolId={user?.school_id}
          onClose={() => setOpen(false)}
          onDone={() => {
            setOpen(false);
            load();
          }}
        />
      )}
    </div>
  );
}

function RefundModal({
  schoolId,
  onClose,
  onDone,
}: {
  schoolId: number | null | undefined;
  onClose: () => void;
  onDone: () => void;
}) {
  const [payments, setPayments] = useState<Payment[]>([]);
  const [paymentId, setPaymentId] = useState('');
  const [amount, setAmount] = useState('');
  const [reason, setReason] = useState('');
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    financeApi
      .payments({ filter: { school_id: schoolId }, per_page: 200 })
      .then((r) => setPayments(r.data));
  }, [schoolId]);

  const submit = async () => {
    setError(null);
    try {
      await financeApi.refund({ payment_id: Number(paymentId), amount: Number(amount), reason });
      onDone();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Refund failed');
    }
  };

  return (
    <AXModal open title="Issue refund" onClose={onClose}>
      <div className="space-y-3">
        <AXSelect
          label="Payment"
          value={paymentId}
          onChange={(e) => setPaymentId(e.target.value)}
          options={[
            { value: '', label: 'Select…' },
            ...payments.map((p) => ({
              value: String(p.id),
              label: `${p.receipt_number} — ${p.student} (₹${p.amount})`,
            })),
          ]}
        />
        <AXInput
          label="Refund amount"
          type="number"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
        />
        <AXInput label="Reason" value={reason} onChange={(e) => setReason(e.target.value)} />
        {error && <AXBadge tone="red">{error}</AXBadge>}
        <div className="flex justify-end gap-2">
          <button
            onClick={onClose}
            className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
          >
            Cancel
          </button>
          <button
            onClick={submit}
            disabled={!paymentId || !amount}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            Refund
          </button>
        </div>
      </div>
    </AXModal>
  );
}
