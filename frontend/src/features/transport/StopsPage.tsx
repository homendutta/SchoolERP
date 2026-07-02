/* Stops — ordered stops on a route. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { transportApi, type Stop } from './api';

export function StopsPage() {
  const { user } = useAuth();
  const [routes, setRoutes] = useState<FieldOption[]>([]);

  useEffect(() => {
    transportApi.routes
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) =>
        setRoutes(
          r.data.map((x) => ({ value: String(x.id), label: `${x.route_code} — ${x.name}` }))
        )
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    {
      name: 'route_id',
      label: 'Route',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...routes],
      required: true,
    },
    { name: 'name', label: 'Stop name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    { name: 'sequence', label: 'Sequence', type: 'number' },
    { name: 'pickup_time', label: 'Pickup time (HH:MM)', type: 'text' },
    { name: 'drop_time', label: 'Drop time (HH:MM)', type: 'text' },
    { name: 'capacity', label: 'Capacity (optional)', type: 'number' },
  ];

  const columns: AXColumn<Stop>[] = [
    { key: 'seq', header: '#', render: (r) => r.sequence },
    { key: 'name', header: 'Stop', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'pickup', header: 'Pickup', render: (r) => r.pickup_time ?? '—' },
    { key: 'drop', header: 'Drop', render: (r) => r.drop_time ?? '—' },
    { key: 'capacity', header: 'Capacity', render: (r) => r.capacity ?? '—' },
  ];

  return (
    <EntityManager<Stop>
      title="Stops"
      icon="location-dot"
      unitLabel="stops"
      api={transportApi.stops}
      columns={columns}
      fields={fields}
      emptyForm={{
        route_id: '',
        name: '',
        code: '',
        sequence: 1,
        pickup_time: '',
        drop_time: '',
        capacity: '',
      }}
      toForm={(r) => ({
        route_id: String(r.route_id),
        name: r.name,
        code: r.code ?? '',
        sequence: r.sequence,
        pickup_time: r.pickup_time ?? '',
        drop_time: r.drop_time ?? '',
        capacity: r.capacity ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search stops…"
      sort="sequence"
    />
  );
}
