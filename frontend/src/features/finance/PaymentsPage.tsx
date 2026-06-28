/* Payments — collect fees (receipt + transaction numbers from the Number
 * Generator, auto-allocated), and view reusable receipt data. */
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
import { useMasterValues } from '@features/academic/useReference';
import { studentsApi, type Student } from '@features/students/api';
import { financeApi, type Payment } from './api';

export function PaymentsPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Payment[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);
  const [collectOpen, setCollectOpen] = useState(false);
  const [receipt, setReceipt] = useState<Record<string, unknown> | null>(null);

  const load = useMemo(
    () => () => {
      financeApi.payments({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      });
    },
    [page, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<Payment>[] = [
    {
      key: 'receipt',
      header: 'Receipt',
      render: (r) => <code className="text-xs text-gray-500">{r.receipt_number}</code>,
    },
    {
      key: 'student',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student}</span>,
    },
    { key: 'amount', header: 'Amount', render: (r) => `₹${r.amount}` },
    { key: 'method', header: 'Method', render: (r) => r.payment_method ?? '—' },
    { key: 'paid_on', header: 'Date', render: (r) => r.paid_on ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={r.status === 'refunded' ? 'amber' : 'green'}>{r.status}</AXBadge>
      ),
    },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          onClick={() => financeApi.receipt(r.id).then(setReceipt)}
          className="text-xs font-semibold text-[var(--navy-accent)]"
        >
          Receipt
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <i className="fas fa-money-bill-wave text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Payments</h2>
        </div>
        <button
          onClick={() => setCollectOpen(true)}
          className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
        >
          <i className="fas fa-plus mr-1" /> Collect fee
        </button>
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No payments yet." />
      <AXPagination meta={meta} onPage={setPage} />

      {collectOpen && (
        <CollectModal
          schoolId={user?.school_id}
          onClose={() => setCollectOpen(false)}
          onDone={() => {
            setCollectOpen(false);
            load();
          }}
        />
      )}
      {receipt && <ReceiptModal data={receipt} onClose={() => setReceipt(null)} />}
    </div>
  );
}

function CollectModal({
  schoolId,
  onClose,
  onDone,
}: {
  schoolId: number | null | undefined;
  onClose: () => void;
  onDone: () => void;
}) {
  const methods = useMasterValues('payment_methods');
  const [students, setStudents] = useState<Student[]>([]);
  const [studentId, setStudentId] = useState('');
  const [amount, setAmount] = useState('');
  const [methodId, setMethodId] = useState('');
  const [due, setDue] = useState<Record<string, unknown> | null>(null);

  useEffect(() => {
    studentsApi.list({ per_page: 300, sort: 'name' }).then((r) => setStudents(r.data));
  }, []);
  useEffect(() => {
    if (studentId) financeApi.dueTracking(Number(studentId)).then(setDue);
    else setDue(null);
  }, [studentId]);

  const submit = async () => {
    await financeApi.recordPayment({
      school_id: schoolId,
      student_id: Number(studentId),
      amount: Number(amount),
      payment_method_id: methodId ? Number(methodId) : null,
    });
    onDone();
  };

  return (
    <AXModal open title="Collect fee" onClose={onClose}>
      <div className="space-y-3">
        <AXSelect
          label="Student"
          value={studentId}
          onChange={(e) => setStudentId(e.target.value)}
          options={[
            { value: '', label: 'Select…' },
            ...students.map((s) => ({
              value: String(s.id),
              label: `${s.admission_number} — ${s.name}`,
            })),
          ]}
        />
        {due && <AXBadge tone="amber">Outstanding: ₹{String(due.outstanding ?? 0)}</AXBadge>}
        <AXInput
          label="Amount"
          type="number"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
        />
        <AXSelect
          label="Payment method"
          value={methodId}
          onChange={(e) => setMethodId(e.target.value)}
          options={[{ value: '', label: '—' }, ...methods]}
        />
        <div className="flex justify-end gap-2">
          <button
            onClick={onClose}
            className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
          >
            Cancel
          </button>
          <button
            onClick={submit}
            disabled={!studentId || !amount}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            Record payment
          </button>
        </div>
      </div>
    </AXModal>
  );
}

function ReceiptModal({ data, onClose }: { data: Record<string, unknown>; onClose: () => void }) {
  const student = (data.student ?? {}) as Record<string, unknown>;
  const identity = data.identity as Record<string, unknown> | null;
  const breakdown = (data.breakdown ?? []) as Array<{ item: string | null; amount: number }>;

  return (
    <AXModal open title={`Receipt ${String(data.receipt_number ?? '')}`} onClose={onClose}>
      <div className="space-y-3">
        <div className="flex items-start justify-between">
          <div>
            <div className="font-semibold text-[var(--navy-primary)]">
              {String(student.name ?? '')}
            </div>
            <div className="text-xs text-gray-500">
              Adm. {String(student.admission_number ?? '')}
            </div>
          </div>
          {identity?.qr_url ? (
            <i className="fas fa-qrcode text-3xl text-[var(--navy-primary)]" />
          ) : null}
        </div>
        <div className="rounded-md border border-gray-200 p-3 text-sm">
          {breakdown.map((b, i) => (
            <div key={i} className="flex justify-between">
              <span className="text-gray-600">{b.item}</span>
              <span>₹{b.amount}</span>
            </div>
          ))}
          <div className="mt-2 flex justify-between border-t pt-2 font-semibold">
            <span>Paid</span>
            <span>₹{String(data.amount ?? 0)}</span>
          </div>
        </div>
        <AXBadge tone="amber">
          Outstanding balance: ₹{String(data.outstanding_balance ?? 0)}
        </AXBadge>
      </div>
    </AXModal>
  );
}
