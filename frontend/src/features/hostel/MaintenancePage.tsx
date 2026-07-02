/* Maintenance — report/track hostel maintenance (no workflow engine). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { MAINT_CATEGORIES, MAINT_PRIORITIES, MAINT_STATUSES, hostelApi, type Ref } from './api';

const PTONES: Record<string, 'gray' | 'navy' | 'amber' | 'red'> = {
  low: 'gray',
  medium: 'navy',
  high: 'amber',
  urgent: 'red',
};

export function MaintenancePage() {
  const { user } = useAuth();
  const [hostels, setHostels] = useState<FieldOption[]>([]);

  useEffect(() => {
    hostelApi.hostels
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setHostels(r.data.map((h) => ({ value: String(h.id), label: String(h.name) }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    {
      name: 'hostel_id',
      label: 'Hostel',
      type: 'select',
      options: [{ value: '', label: '—' }, ...hostels],
    },
    {
      name: 'category',
      label: 'Category',
      type: 'select',
      options: MAINT_CATEGORIES.map((c) => ({ value: c, label: c })),
    },
    {
      name: 'priority',
      label: 'Priority',
      type: 'select',
      options: MAINT_PRIORITIES.map((p) => ({ value: p, label: p })),
    },
    { name: 'description', label: 'Description', type: 'text' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: MAINT_STATUSES.map((s) => ({ value: s, label: s.replace('_', ' ') })),
    },
    { name: 'resolution_date', label: 'Resolution date', type: 'date' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'category',
      header: 'Category',
      render: (r) => <span className="font-medium capitalize">{String(r.category)}</span>,
    },
    {
      key: 'hostel',
      header: 'Hostel',
      render: (r) => String((r.hostel as { name?: string })?.name ?? '—'),
    },
    {
      key: 'priority',
      header: 'Priority',
      render: (r) => (
        <AXBadge tone={PTONES[String(r.priority)] ?? 'gray'}>{String(r.priority)}</AXBadge>
      ),
    },
    { key: 'status', header: 'Status', render: (r) => String(r.status).replace('_', ' ') },
  ];

  return (
    <EntityManager<Ref>
      title="Maintenance"
      icon="screwdriver-wrench"
      unitLabel="requests"
      api={hostelApi.maintenance}
      columns={columns}
      fields={fields}
      emptyForm={{
        hostel_id: '',
        category: 'other',
        priority: 'medium',
        description: '',
        status: 'open',
        resolution_date: '',
      }}
      toForm={(r) => ({
        hostel_id: r.hostel_id ? String(r.hostel_id) : '',
        category: String(r.category),
        priority: String(r.priority),
        description: (r.description as string) ?? '',
        status: String(r.status),
        resolution_date: (r.resolution_date as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="description"
      searchPlaceholder="Search maintenance…"
      sort="id"
    />
  );
}
