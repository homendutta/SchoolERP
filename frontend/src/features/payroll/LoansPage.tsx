/* Loans & Advances — payroll deducts installments; Finance owns the cash. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { LOAN_TYPES, payrollApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'green' | 'navy'> = {
  pending: 'gray',
  active: 'green',
  closed: 'navy',
};

export function LoansPage() {
  const { user } = useAuth();
  const [employees, setEmployees] = useState<FieldOption[]>([]);

  useEffect(() => {
    staffApi.staff
      .list({ per_page: 500, sort: 'name' })
      .then((r) =>
        setEmployees(
          r.data.map((s) => ({ value: String(s.id), label: `${s.employee_number} — ${s.name}` }))
        )
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'staff_id', label: 'Employee', type: 'select', options: employees, required: true },
    {
      name: 'loan_type',
      label: 'Type',
      type: 'select',
      options: LOAN_TYPES.map((t) => ({ value: t, label: t })),
    },
    { name: 'reference', label: 'Reference', type: 'text' },
    { name: 'principal', label: 'Principal', type: 'number', required: true },
    { name: 'installment_amount', label: 'Installment', type: 'number' },
    { name: 'disbursed_on', label: 'Disbursed on', type: 'date' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'employee',
      header: 'Employee',
      render: (r) => (
        <span className="font-medium">
          {String((r.employee as { name?: string })?.name ?? r.staff_id)}
        </span>
      ),
    },
    { key: 'type', header: 'Type', render: (r) => String(r.loan_type) },
    { key: 'principal', header: 'Principal', render: (r) => String(r.principal ?? 0) },
    { key: 'balance', header: 'Balance', render: (r) => String(r.balance ?? 0) },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Ref>
      title="Loans & Advances"
      icon="hand-holding-dollar"
      unitLabel="loans"
      api={payrollApi.loans}
      columns={columns}
      fields={fields}
      emptyForm={{
        staff_id: '',
        loan_type: 'loan',
        reference: '',
        principal: 0,
        installment_amount: 0,
        disbursed_on: '',
      }}
      toForm={(r) => ({
        staff_id: String(r.staff_id),
        loan_type: String(r.loan_type ?? 'loan'),
        reference: r.reference ?? '',
        principal: (r.principal as number) ?? 0,
        installment_amount: (r.installment_amount as number) ?? 0,
        disbursed_on: (r.disbursed_on as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
      rowExtras={(r, reload) =>
        r.status === 'pending' ? (
          <button
            onClick={() => payrollApi.approveLoan(r.id).then(reload)}
            title="Approve loan"
            className="hover:text-[var(--success)]"
          >
            <i className="fas fa-check" />
          </button>
        ) : null
      }
    />
  );
}
