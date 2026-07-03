/* Payslips — structured data (no PDF); QR from the Identity Platform; settlement. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXSelect, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { SETTLEMENT_STATUSES, payrollApi, type Ref } from './api';

const TONES: Record<string, 'gray' | 'green' | 'amber' | 'red'> = {
  unpaid: 'gray',
  paid: 'green',
  partially_paid: 'amber',
  failed: 'red',
};

export function PayslipsPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Ref[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const load = useMemo(
    () => () =>
      payrollApi.payslips({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<Ref>[] = [
    {
      key: 'no',
      header: 'Payslip #',
      render: (r) => <span className="font-medium">{String(r.payslip_number ?? r.id)}</span>,
    },
    {
      key: 'employee',
      header: 'Employee',
      render: (r) => String((r.employee as { name?: string })?.name ?? r.staff_id),
    },
    {
      key: 'period',
      header: 'Period',
      render: (r) => `${r.period_year}-${String(r.period_month).padStart(2, '0')}`,
    },
    { key: 'gross', header: 'Gross', render: (r) => String(r.gross_earnings ?? 0) },
    { key: 'ded', header: 'Deductions', render: (r) => String(r.total_deductions ?? 0) },
    {
      key: 'net',
      header: 'Net',
      render: (r) => <span className="font-medium">{String(r.net_pay ?? 0)}</span>,
    },
    {
      key: 'settle',
      header: 'Settlement',
      render: (r) => (
        <div className="flex items-center gap-2">
          <AXBadge tone={TONES[String(r.settlement_status)] ?? 'gray'}>
            {String(r.settlement_status).replace(/_/g, ' ')}
          </AXBadge>
          <div className="w-32">
            <AXSelect
              value=""
              onChange={(e) => {
                if (e.target.value) payrollApi.settlePayslip(r.id, e.target.value).then(load);
              }}
              options={[
                { value: '', label: 'Set…' },
                ...SETTLEMENT_STATUSES.map((s) => ({ value: s, label: s.replace(/_/g, ' ') })),
              ]}
            />
          </div>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-receipt text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Payslips</h2>
      </div>
      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No payslips yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
