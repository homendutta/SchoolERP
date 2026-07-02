/* Routes — route code from the Number Generator (blank = auto-issue). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { transportApi, type Route } from './api';

const fields: Field[] = [
  { name: 'route_code', label: 'Route code (blank = auto)', type: 'text' },
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'start_location', label: 'Start location', type: 'text' },
  { name: 'end_location', label: 'End location', type: 'text' },
  { name: 'distance_km', label: 'Distance (km)', type: 'number' },
  { name: 'estimated_minutes', label: 'Estimated minutes', type: 'number' },
];

export function RoutesPage() {
  const { user } = useAuth();
  const columns: AXColumn<Route>[] = [
    {
      key: 'route_code',
      header: 'Code',
      render: (r) => <code className="text-xs text-gray-500">{r.route_code}</code>,
    },
    { key: 'name', header: 'Route', render: (r) => <span className="font-medium">{r.name}</span> },
    {
      key: 'from',
      header: 'From → To',
      render: (r) => `${r.start_location ?? '—'} → ${r.end_location ?? '—'}`,
    },
    {
      key: 'stops',
      header: 'Stops',
      render: (r) => <AXBadge tone="navy">{r.stops_count ?? 0}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Route>
      title="Routes"
      icon="route"
      unitLabel="routes"
      api={transportApi.routes}
      columns={columns}
      fields={fields}
      emptyForm={{
        route_code: '',
        name: '',
        start_location: '',
        end_location: '',
        distance_km: '',
        estimated_minutes: '',
      }}
      toForm={(r) => ({
        route_code: r.route_code,
        name: r.name,
        start_location: r.start_location ?? '',
        end_location: r.end_location ?? '',
        distance_km: r.distance_km ?? '',
        estimated_minutes: r.estimated_minutes ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search routes…"
      sort="name"
    />
  );
}
