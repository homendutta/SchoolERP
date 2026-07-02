/* Warranty — start/end/vendor/coverage; reminders via Communication. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { WARRANTY_STATUSES, inventoryApi, type Ref } from './api';

export function WarrantyPage() {
  const { user } = useAuth();
  const [assets, setAssets] = useState<FieldOption[]>([]);
  const [vendors, setVendors] = useState<FieldOption[]>([]);

  useEffect(() => {
    const f = { filter: { school_id: user?.school_id }, per_page: 500 };
    inventoryApi.assets
      .list(f)
      .then((r) => setAssets(r.data.map((a) => ({ value: String(a.id), label: a.asset_number }))));
    inventoryApi.vendors
      .list(f)
      .then((r) => setVendors(r.data.map((v) => ({ value: String(v.id), label: String(v.name) }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    {
      name: 'asset_id',
      label: 'Asset',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...assets],
      required: true,
    },
    {
      name: 'vendor_id',
      label: 'Vendor',
      type: 'select',
      options: [{ value: '', label: '—' }, ...vendors],
    },
    { name: 'start_date', label: 'Start date', type: 'date' },
    { name: 'end_date', label: 'End date', type: 'date' },
    { name: 'coverage', label: 'Coverage', type: 'text' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: WARRANTY_STATUSES.map((s) => ({ value: s, label: s })),
    },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'asset',
      header: 'Asset',
      render: (r) => (
        <span className="font-medium">
          {String((r.asset as { asset_number?: string })?.asset_number ?? r.asset_id)}
        </span>
      ),
    },
    { key: 'end', header: 'Expires', render: (r) => String(r.end_date ?? '—') },
    { key: 'coverage', header: 'Coverage', render: (r) => String(r.coverage ?? '—') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={r.status === 'active' ? 'green' : 'gray'}>{String(r.status)}</AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<Ref>
      title="Warranty"
      icon="shield-halved"
      unitLabel="warranties"
      api={inventoryApi.warranties}
      columns={columns}
      fields={fields}
      emptyForm={{
        asset_id: '',
        vendor_id: '',
        start_date: '',
        end_date: '',
        coverage: '',
        status: 'active',
      }}
      toForm={(r) => ({
        asset_id: String(r.asset_id),
        vendor_id: r.vendor_id ? String(r.vendor_id) : '',
        start_date: (r.start_date as string) ?? '',
        end_date: (r.end_date as string) ?? '',
        coverage: (r.coverage as string) ?? '',
        status: String(r.status),
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
