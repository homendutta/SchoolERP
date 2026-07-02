/* Assets — physical items; asset number auto-issued; each has its own Identity
 * (barcode/QR read from that Identity). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { ASSET_CONDITIONS, ASSET_STATUSES, inventoryApi, type Asset } from './api';

const TONES: Record<string, 'green' | 'navy' | 'amber' | 'red' | 'gray'> = {
  draft: 'gray',
  ordered: 'gray',
  received: 'navy',
  available: 'green',
  assigned: 'navy',
  reserved: 'amber',
  in_maintenance: 'amber',
  lost: 'red',
  stolen: 'red',
  disposed: 'gray',
};

export function AssetsPage() {
  const { user } = useAuth();
  const [models, setModels] = useState<FieldOption[]>([]);
  const [categories, setCategories] = useState<FieldOption[]>([]);
  const [vendors, setVendors] = useState<FieldOption[]>([]);

  useEffect(() => {
    const f = { filter: { school_id: user?.school_id }, per_page: 500 };
    inventoryApi.models
      .list(f)
      .then((r) => setModels(r.data.map((m) => ({ value: String(m.id), label: String(m.name) }))));
    inventoryApi.categories
      .list(f)
      .then((r) =>
        setCategories(r.data.map((c) => ({ value: String(c.id), label: String(c.name) })))
      );
    inventoryApi.vendors
      .list(f)
      .then((r) => setVendors(r.data.map((v) => ({ value: String(v.id), label: String(v.name) }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'asset_number', label: 'Asset number (blank = auto)', type: 'text' },
    { name: 'serial_number', label: 'Serial number', type: 'text' },
    {
      name: 'asset_model_id',
      label: 'Model',
      type: 'select',
      options: [{ value: '', label: '—' }, ...models],
    },
    {
      name: 'category_id',
      label: 'Category',
      type: 'select',
      options: [{ value: '', label: '—' }, ...categories],
    },
    {
      name: 'vendor_id',
      label: 'Vendor',
      type: 'select',
      options: [{ value: '', label: '—' }, ...vendors],
    },
    { name: 'purchase_date', label: 'Purchase date', type: 'date' },
    { name: 'purchase_value', label: 'Purchase value', type: 'number' },
    { name: 'current_value', label: 'Current value', type: 'number' },
    {
      name: 'condition',
      label: 'Condition',
      type: 'select',
      options: ASSET_CONDITIONS.map((c) => ({ value: c, label: c })),
    },
    // Lifecycle state is NOT edited here — it changes only through the lifecycle
    // endpoint (see the Status column) so every transition is audited + timelined.
  ];

  const columns: AXColumn<Asset>[] = [
    {
      key: 'asset_number',
      header: 'Asset #',
      render: (r) => <span className="font-medium">{r.asset_number}</span>,
    },
    {
      key: 'barcode',
      header: 'Barcode',
      render: (r) => (
        <code className="text-xs text-gray-500">{r.assetIdentity?.identity_number ?? '—'}</code>
      ),
    },
    { key: 'model', header: 'Model', render: (r) => r.assetModel?.name ?? '—' },
    { key: 'serial', header: 'Serial', render: (r) => r.serial_number ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status.replace('_', ' ')}</AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<Asset>
      title="Assets"
      icon="laptop"
      unitLabel="assets"
      api={inventoryApi.assets}
      columns={columns}
      fields={fields}
      emptyForm={{
        asset_number: '',
        serial_number: '',
        asset_model_id: '',
        category_id: '',
        vendor_id: '',
        purchase_date: '',
        purchase_value: '',
        current_value: '',
        condition: 'good',
        status: 'available',
      }}
      toForm={(r) => ({
        asset_number: r.asset_number,
        serial_number: r.serial_number ?? '',
        asset_model_id: r.asset_model_id ? String(r.asset_model_id) : '',
        category_id: r.category_id ? String(r.category_id) : '',
        vendor_id: r.vendor_id ? String(r.vendor_id) : '',
        purchase_date: (r.purchase_date as string) ?? '',
        purchase_value: (r.purchase_value as number) ?? '',
        current_value: (r.current_value as number) ?? '',
        condition: r.condition,
        status: r.status,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="asset_number"
      searchPlaceholder="Search assets…"
      sort="asset_number"
      rowExtras={(r, reload) => (
        <select
          aria-label="Change lifecycle state"
          value={r.status}
          onChange={(e) =>
            inventoryApi.transition(r.id, { to_status: e.target.value }).then(reload)
          }
          className="rounded border border-gray-200 bg-white px-1.5 py-1 text-xs text-gray-600"
          title="Move to a new lifecycle state (audited)"
        >
          {ASSET_STATUSES.map((s) => (
            <option key={s} value={s}>
              {s.replace('_', ' ')}
            </option>
          ))}
        </select>
      )}
    />
  );
}
