/* Portal Fees — view dues, pay online (reuses the Finance Payment Engine),
 * payment history + receipts. Parents may pay for several children at once. */
import { useEffect, useMemo, useState } from 'react';
import { AXBadge, AXInput, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { portalApi, type PayItem } from './api';
import { usePortal } from './usePortal';
import { PortalShell } from './PortalShell';

export function PortalFeesPage() {
  const { context, studentId, setStudentId, error } = usePortal();
  const [dues, setDues] = useState<Record<string, unknown> | null>(null);
  const [history, setHistory] = useState<Array<Record<string, unknown>>>([]);
  const [providers, setProviders] = useState<string[]>([]);
  const [gateway, setGateway] = useState('');
  const [amounts, setAmounts] = useState<Record<number, string>>({});
  const [status, setStatus] = useState<string | null>(null);

  const isTeacher = context?.role === 'teacher';

  const load = useMemo(
    () => () => {
      if (!studentId) return;
      portalApi
        .fees(studentId)
        .then(setDues)
        .catch(() => setDues(null));
      portalApi
        .feeHistory(studentId)
        .then(setHistory)
        .catch(() => setHistory([]));
    },
    [studentId]
  );

  useEffect(() => {
    if (isTeacher) return;
    portalApi.gateways().then((g) => {
      setProviders(g.providers);
      setGateway(g.providers[0] ?? 'manual');
    });
  }, [isTeacher]);
  useEffect(() => {
    load();
  }, [load]);

  const pay = async () => {
    setStatus(null);
    const items: PayItem[] = Object.entries(amounts)
      .filter(([, v]) => Number(v) > 0)
      .map(([sid, v]) => ({ student_id: Number(sid), amount: Number(v) }));
    if (items.length === 0) {
      setStatus('Enter an amount to pay.');
      return;
    }
    try {
      const res = await portalApi.pay(items, gateway);
      setStatus(
        `Paid ${String(res.total)} — ${(res.payments as unknown[]).length} receipt(s) generated.`
      );
      setAmounts({});
      load();
    } catch (e) {
      setStatus(e instanceof Error ? e.message : 'Payment failed.');
    }
  };

  const columns: AXColumn<Record<string, unknown>>[] = [
    {
      key: 'receipt',
      header: 'Receipt',
      render: (r) => <span className="font-medium">{String(r.receipt_number)}</span>,
    },
    { key: 'amount', header: 'Amount', render: (r) => String(r.amount) },
    { key: 'paid_on', header: 'Paid on', render: (r) => String(r.paid_on ?? '—') },
    { key: 'gateway', header: 'Gateway', render: (r) => String(r.gateway ?? '—') },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          className="text-xs font-semibold text-[var(--navy-accent)]"
          onClick={() =>
            portalApi
              .receipt(Number(r.id))
              .then((rec) =>
                window.alert(
                  `Receipt ${String((rec as Record<string, unknown>).receipt_number ?? r.receipt_number)}`
                )
              )
          }
        >
          Receipt
        </button>
      ),
    },
  ];

  if (isTeacher) {
    return (
      <PortalShell
        title="Fees"
        icon="wallet"
        context={context}
        studentId={studentId}
        requiresStudent={false}
        error={error}
      >
        <p className="text-sm text-gray-500">Teachers do not have fee access.</p>
      </PortalShell>
    );
  }

  return (
    <PortalShell
      title="Fees & Online Payment"
      icon="wallet"
      context={context}
      studentId={studentId}
      onStudent={setStudentId}
      error={error}
    >
      <div className="grid gap-4 md:grid-cols-3">
        <div className="erp-card">
          <div className="text-2xl font-semibold text-[var(--navy-primary)]">
            {String(dues?.net_amount ?? dues?.outstanding ?? 0)}
          </div>
          <div className="text-xs uppercase tracking-wide text-gray-500">Net Payable</div>
        </div>
      </div>

      <div className="erp-card space-y-3">
        <h3 className="text-sm font-semibold text-[var(--navy-primary)]">Pay online</h3>
        <p className="text-xs text-gray-500">
          Enter an amount for one or more children — parents can pay everyone in a single
          transaction.
        </p>
        <div className="space-y-2">
          {(context?.students ?? []).map((s) => (
            <div key={s.id} className="flex items-center gap-3">
              <span className="w-48 text-sm">{s.name ?? `#${s.id}`}</span>
              <div className="w-40">
                <AXInput
                  type="number"
                  label=""
                  value={amounts[s.id] ?? ''}
                  onChange={(e) => setAmounts((a) => ({ ...a, [s.id]: e.target.value }))}
                />
              </div>
            </div>
          ))}
        </div>
        <div className="flex flex-wrap items-end gap-3">
          <div className="w-44">
            <AXSelect
              label="Gateway"
              value={gateway}
              onChange={(e) => setGateway(e.target.value)}
              options={providers.map((p) => ({ value: p, label: p }))}
            />
          </div>
          <button
            onClick={pay}
            className="rounded-md bg-[var(--navy-primary)] px-5 py-2 text-sm font-semibold text-white"
          >
            Pay now
          </button>
          {status && <AXBadge tone="navy">{status}</AXBadge>}
        </div>
      </div>

      <div>
        <h3 className="mb-2 text-sm font-semibold text-[var(--navy-primary)]">Payment history</h3>
        <AXTable
          columns={columns}
          rows={history}
          rowKey={(r) => Number(r.id)}
          empty="No payments yet."
        />
      </div>
    </PortalShell>
  );
}
