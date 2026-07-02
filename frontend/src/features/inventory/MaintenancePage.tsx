/* Maintenance — preventive/corrective/emergency (no workflow engine). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { MAINT_PRIORITIES, MAINT_STATUSES, MAINT_TYPES, inventoryApi, type Ref } from './api';

const PT: Record<string, 'gray' | 'navy' | 'amber' | 'red'> = {
  low: 'gray',
  medium: 'navy',
  high: 'amber',
  urgent: 'red',
};

export function MaintenancePage() {
  const { user } = useAuth();
  const [assets, setAssets] = useState<FieldOption[]>([]);
  const [staff, setStaff] = useState<FieldOption[]>([]);

  useEffect(() => {
    inventoryApi.assets
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setAssets(r.data.map((a) => ({ value: String(a.id), label: a.asset_number }))));
    staffApi.staff
      .list({ per_page: 500, sort: 'name' })
      .then((r) =>
        setStaff(
          r.data.map((s) => ({ value: String(s.id), label: `${s.employee_number} — ${s.name}` }))
        )
      );
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
      name: 'type',
      label: 'Type',
      type: 'select',
      options: MAINT_TYPES.map((t) => ({ value: t, label: t })),
    },
    {
      name: 'priority',
      label: 'Priority',
      type: 'select',
      options: MAINT_PRIORITIES.map((p) => ({ value: p, label: p })),
    },
    {
      name: 'assigned_staff_id',
      label: 'Assigned staff',
      type: 'select',
      options: [{ value: '', label: '—' }, ...staff],
    },
    { name: 'scheduled_date', label: 'Scheduled date', type: 'date' },
    { name: 'completed_date', label: 'Completed date', type: 'date' },
    { name: 'cost', label: 'Cost', type: 'number' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: MAINT_STATUSES.map((s) => ({ value: s, label: s.replace('_', ' ') })),
    },
    { name: 'notes', label: 'Notes', type: 'text' },
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
    { key: 'type', header: 'Type', render: (r) => String(r.type) },
    {
      key: 'priority',
      header: 'Priority',
      render: (r) => (
        <AXBadge tone={PT[String(r.priority)] ?? 'gray'}>{String(r.priority)}</AXBadge>
      ),
    },
    { key: 'status', header: 'Status', render: (r) => String(r.status).replace('_', ' ') },
  ];

  return (
    <EntityManager<Ref>
      title="Asset Maintenance"
      icon="screwdriver-wrench"
      unitLabel="records"
      api={inventoryApi.maintenance}
      columns={columns}
      fields={fields}
      emptyForm={{
        asset_id: '',
        type: 'preventive',
        priority: 'medium',
        assigned_staff_id: '',
        scheduled_date: '',
        completed_date: '',
        cost: '',
        status: 'scheduled',
        notes: '',
      }}
      toForm={(r) => ({
        asset_id: String(r.asset_id),
        type: String(r.type),
        priority: String(r.priority),
        assigned_staff_id: r.assigned_staff_id ? String(r.assigned_staff_id) : '',
        scheduled_date: (r.scheduled_date as string) ?? '',
        completed_date: (r.completed_date as string) ?? '',
        cost: (r.cost as number) ?? '',
        status: String(r.status),
        notes: (r.notes as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
