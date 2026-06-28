/* Fine Rules — configurable fines with grace period & maximum. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { FINE_MODES, financeApi, type FineRule } from './api';

export function FineRulesPage() {
  const { user } = useAuth();
  const [categories, setCategories] = useState<FieldOption[]>([]);

  useEffect(() => {
    financeApi.categories
      .list({ per_page: 200 })
      .then((r) => setCategories(r.data.map((c) => ({ value: String(c.id), label: c.name }))));
  }, []);

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    {
      name: 'fee_category_id',
      label: 'Category (optional)',
      type: 'select',
      options: [{ value: '', label: 'All categories' }, ...categories],
    },
    {
      name: 'mode',
      label: 'Mode',
      type: 'select',
      options: FINE_MODES.map((m) => ({ value: m, label: m })),
    },
    { name: 'amount', label: 'Amount', type: 'number', required: true },
    { name: 'grace_period_days', label: 'Grace period (days)', type: 'number' },
    { name: 'max_fine', label: 'Maximum fine', type: 'number' },
  ];

  const columns: AXColumn<FineRule>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'mode', header: 'Mode', render: (r) => <AXBadge tone="navy">{r.mode}</AXBadge> },
    { key: 'amount', header: 'Amount', render: (r) => `₹${r.amount}` },
    { key: 'grace', header: 'Grace', render: (r) => `${r.grace_period_days}d` },
    { key: 'max', header: 'Max', render: (r) => (r.max_fine != null ? `₹${r.max_fine}` : '—') },
  ];

  return (
    <EntityManager<FineRule>
      title="Fine Rules"
      icon="triangle-exclamation"
      unitLabel="fine rules"
      api={financeApi.fines}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        fee_category_id: '',
        mode: 'flat',
        amount: 0,
        grace_period_days: 0,
        max_fine: '',
      }}
      toForm={(r) => ({
        name: r.name,
        fee_category_id: r.fee_category_id ? String(r.fee_category_id) : '',
        mode: r.mode,
        amount: r.amount,
        grace_period_days: r.grace_period_days,
        max_fine: r.max_fine ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search fine rules…"
      sort="name"
    />
  );
}
