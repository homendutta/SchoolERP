/* Buildings — each hostel has one or more buildings. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { hostelApi, type Ref } from './api';

export function BuildingsPage() {
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
      options: [{ value: '', label: 'Select…' }, ...hostels],
      required: true,
    },
    { name: 'name', label: 'Building name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    { name: 'floors_count', label: 'Number of floors', type: 'number' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Building',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    {
      key: 'hostel',
      header: 'Hostel',
      render: (r) => String((r.hostel as { name?: string })?.name ?? r.hostel_id),
    },
    { key: 'floors', header: 'Floors', render: (r) => String(r.floors_count ?? 0) },
  ];

  return (
    <EntityManager<Ref>
      title="Buildings"
      icon="building-flag"
      unitLabel="buildings"
      api={hostelApi.buildings}
      columns={columns}
      fields={fields}
      emptyForm={{ hostel_id: '', name: '', code: '', floors_count: 0 }}
      toForm={(r) => ({
        hostel_id: String(r.hostel_id),
        name: String(r.name),
        code: (r.code as string) ?? '',
        floors_count: (r.floors_count as number) ?? 0,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search buildings…"
      sort="name"
    />
  );
}
