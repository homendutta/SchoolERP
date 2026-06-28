/* Fee Masters — reusable definitions of what is owed. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { useClasses, useYears } from '@features/academic/useReference';
import { FEE_FREQUENCIES, financeApi, type FeeMaster } from './api';

export function FeeMastersPage() {
  const { user } = useAuth();
  const years = useYears();
  const classes = useClasses();
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
      label: 'Category',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...categories],
    },
    {
      name: 'academic_year_id',
      label: 'Academic year',
      type: 'select',
      options: [{ value: '', label: '—' }, ...years],
    },
    {
      name: 'class_id',
      label: 'Class',
      type: 'select',
      options: [{ value: '', label: 'All' }, ...classes],
    },
    { name: 'amount', label: 'Amount', type: 'number', required: true },
    { name: 'due_date', label: 'Due date', type: 'date' },
    {
      name: 'frequency',
      label: 'Frequency',
      type: 'select',
      options: FEE_FREQUENCIES.map((f) => ({ value: f, label: f.replace('_', ' ') })),
    },
  ];

  const columns: AXColumn<FeeMaster>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'category', header: 'Category', render: (r) => r.category ?? '—' },
    { key: 'amount', header: 'Amount', render: (r) => `₹${r.amount}` },
    { key: 'frequency', header: 'Frequency', render: (r) => r.frequency.replace('_', ' ') },
    { key: 'due', header: 'Due', render: (r) => r.due_date ?? '—' },
  ];

  return (
    <EntityManager<FeeMaster>
      title="Fee Masters"
      icon="rupee-sign"
      unitLabel="fee masters"
      api={financeApi.masters}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        fee_category_id: '',
        academic_year_id: '',
        class_id: '',
        amount: 0,
        due_date: '',
        frequency: 'one_time',
      }}
      toForm={(r) => ({
        name: r.name,
        fee_category_id: String(r.fee_category_id),
        academic_year_id: r.academic_year_id ? String(r.academic_year_id) : '',
        class_id: r.class_id ? String(r.class_id) : '',
        amount: r.amount,
        due_date: r.due_date ?? '',
        frequency: r.frequency,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search fee masters…"
      sort="name"
    />
  );
}
