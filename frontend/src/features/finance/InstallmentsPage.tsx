/* Installments — unlimited installment schedules per student fee. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { financeApi, type Installment } from './api';

const fields: Field[] = [
  { name: 'student_fee_id', label: 'Student fee ID', type: 'number', required: true },
  { name: 'name', label: 'Installment name', type: 'text', required: true },
  { name: 'due_date', label: 'Due date', type: 'date' },
  { name: 'amount', label: 'Amount', type: 'number', required: true },
  { name: 'sort_order', label: 'Order', type: 'number' },
];

const TONES: Record<string, 'green' | 'amber' | 'gray'> = {
  paid: 'green',
  partial: 'amber',
  pending: 'gray',
};

export function InstallmentsPage() {
  const { user } = useAuth();
  const columns: AXColumn<Installment>[] = [
    {
      key: 'name',
      header: 'Installment',
      render: (r) => <span className="font-medium">{r.name}</span>,
    },
    { key: 'fee', header: 'Fee #', render: (r) => r.student_fee_id },
    { key: 'amount', header: 'Amount', render: (r) => `₹${r.amount}` },
    { key: 'due', header: 'Due', render: (r) => r.due_date ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Installment>
      title="Installments"
      icon="calendar-days"
      unitLabel="installments"
      api={financeApi.installments}
      columns={columns}
      fields={fields}
      emptyForm={{ student_fee_id: '', name: '', due_date: '', amount: 0, sort_order: 0 }}
      toForm={(r) => ({
        student_fee_id: r.student_fee_id,
        name: r.name,
        due_date: r.due_date ?? '',
        amount: r.amount,
        sort_order: r.sort_order,
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="due_date"
    />
  );
}
