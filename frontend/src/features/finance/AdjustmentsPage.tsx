/* Adjustments — independent records (credit/debit note, waiver, manual). */
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
import { studentsApi, type Student } from '@features/students/api';
import { ADJUSTMENT_TYPES, financeApi, type Adjustment } from './api';

export function AdjustmentsPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Adjustment[]>([]);
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
      financeApi.adjustments({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      });
    },
    [page, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<Adjustment>[] = [
    {
      key: 'txn',
      header: 'Transaction',
      render: (r) => <code className="text-xs text-gray-500">{r.transaction_number}</code>,
    },
    { key: 'student', header: 'Student', render: (r) => r.student ?? '—' },
    {
      key: 'type',
      header: 'Type',
      render: (r) => <AXBadge tone="navy">{r.type.replace('_', ' ')}</AXBadge>,
    },
    { key: 'amount', header: 'Amount', render: (r) => `₹${r.amount}` },
    { key: 'reason', header: 'Reason', render: (r) => r.reason ?? '—' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <i className="fas fa-scale-balanced text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Adjustments</h2>
        </div>
        <button
          onClick={() => setOpen(true)}
          className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
        >
          <i className="fas fa-plus mr-1" /> New adjustment
        </button>
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No adjustments yet." />
      <AXPagination meta={meta} onPage={setPage} />

      {open && (
        <AdjustModal
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

function AdjustModal({
  schoolId,
  onClose,
  onDone,
}: {
  schoolId: number | null | undefined;
  onClose: () => void;
  onDone: () => void;
}) {
  const [students, setStudents] = useState<Student[]>([]);
  const [studentId, setStudentId] = useState('');
  const [type, setType] = useState('waiver');
  const [amount, setAmount] = useState('');
  const [reason, setReason] = useState('');

  useEffect(() => {
    studentsApi.list({ per_page: 300, sort: 'name' }).then((r) => setStudents(r.data));
  }, []);

  const submit = async () => {
    await financeApi.adjust({
      school_id: schoolId,
      student_id: Number(studentId),
      type,
      amount: Number(amount),
      reason,
    });
    onDone();
  };

  return (
    <AXModal open title="New adjustment" onClose={onClose}>
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
        <AXSelect
          label="Type"
          value={type}
          onChange={(e) => setType(e.target.value)}
          options={ADJUSTMENT_TYPES.map((t) => ({ value: t, label: t.replace('_', ' ') }))}
        />
        <AXInput
          label="Amount"
          type="number"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
        />
        <AXInput label="Reason" value={reason} onChange={(e) => setReason(e.target.value)} />
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
            Create
          </button>
        </div>
      </div>
    </AXModal>
  );
}
