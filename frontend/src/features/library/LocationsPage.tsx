/* Library Locations — configurable room / rack / shelf layout. */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { libraryApi, type Ref } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'room', label: 'Room', type: 'text' },
  { name: 'rack', label: 'Rack', type: 'text' },
  { name: 'shelf', label: 'Shelf', type: 'text' },
  { name: 'position', label: 'Position', type: 'text' },
];

export function LocationsPage() {
  const { user } = useAuth();
  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Location',
      render: (r) => <span className="font-medium">{r.name}</span>,
    },
    { key: 'room', header: 'Room', render: (r) => (r.room as string) ?? '—' },
    { key: 'rack', header: 'Rack', render: (r) => (r.rack as string) ?? '—' },
    { key: 'shelf', header: 'Shelf', render: (r) => (r.shelf as string) ?? '—' },
  ];

  return (
    <EntityManager<Ref>
      title="Locations"
      icon="map-location-dot"
      unitLabel="locations"
      api={libraryApi.locations}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', room: '', rack: '', shelf: '', position: '' }}
      toForm={(r) => ({
        name: r.name,
        room: (r.room as string) ?? '',
        rack: (r.rack as string) ?? '',
        shelf: (r.shelf as string) ?? '',
        position: (r.position as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search locations…"
      sort="name"
    />
  );
}
