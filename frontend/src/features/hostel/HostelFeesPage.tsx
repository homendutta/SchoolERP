/* Hostel Fees — definitions only; collection is handled by Finance. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { FEE_TYPES, hostelApi, type Ref } from './api';

export function HostelFeesPage() {
  const { user } = useAuth();
  const [hostels, setHostels] = useState<FieldOption[]>([]);

  useEffect(() => {
    hostelApi.hostels
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setHostels(r.data.map((h) => ({ value: String(h.id), label: String(h.name) }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    {
      name: 'fee_type',
      label: 'Type',
      type: 'select',
      options: FEE_TYPES.map((f) => ({ value: f, label: f.replace('_', ' ') })),
    },
    {
      name: 'hostel_id',
      label: 'Hostel',
      type: 'select',
      options: [{ value: '', label: 'All hostels' }, ...hostels],
    },
    { name: 'amount', label: 'Amount', type: 'number', required: true },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Fee',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    {
      key: 'type',
      header: 'Type',
      render: (r) => <AXBadge tone="navy">{String(r.fee_type).replace('_', ' ')}</AXBadge>,
    },
    {
      key: 'hostel',
      header: 'Hostel',
      render: (r) => String((r.hostel as { name?: string })?.name ?? 'All'),
    },
    { key: 'amount', header: 'Amount', render: (r) => String(r.amount ?? 0) },
  ];

  return (
    <EntityManager<Ref>
      title="Hostel Fees"
      icon="rupee-sign"
      unitLabel="fees"
      api={hostelApi.fees}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', fee_type: 'hostel', hostel_id: '', amount: 0 }}
      toForm={(r) => ({
        name: String(r.name),
        fee_type: String(r.fee_type),
        hostel_id: r.hostel_id ? String(r.hostel_id) : '',
        amount: (r.amount as number) ?? 0,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search fees…"
      sort="name"
    />
  );
}
