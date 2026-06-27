/* Rooms — room type comes from Master Data (never hardcoded). */
import { useAuth } from '@core/auth/AuthContext';
import type { AXColumn } from '@ui/ax';
import { academicApi, type Room } from './api';
import { EntityManager, statusCell, type Field } from './EntityManager';
import { useMasterValues } from './useReference';

export function RoomsPage() {
  const { user } = useAuth();
  const roomTypes = useMasterValues('room_types');

  const fields: Field[] = [
    { name: 'code', label: 'Code', type: 'text', required: true },
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'room_type_id', label: 'Room type', type: 'select', options: roomTypes },
    { name: 'capacity', label: 'Capacity', type: 'number' },
    { name: 'building', label: 'Building', type: 'text' },
    { name: 'display_order', label: 'Display order', type: 'number' },
  ];

  const columns: AXColumn<Room>[] = [
    {
      key: 'code',
      header: 'Code',
      render: (r) => <code className="text-xs text-gray-500">{r.code}</code>,
    },
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'capacity', header: 'Capacity', render: (r) => r.capacity ?? '—' },
    { key: 'building', header: 'Building', render: (r) => r.building ?? '—' },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Room>
      title="Rooms"
      icon="door-open"
      unitLabel="rooms"
      api={academicApi.rooms}
      columns={columns}
      fields={fields}
      emptyForm={{
        code: '',
        name: '',
        room_type_id: null,
        capacity: null,
        building: '',
        display_order: 0,
      }}
      toForm={(r) => ({
        code: r.code,
        name: r.name,
        room_type_id: r.room_type_id,
        capacity: r.capacity,
        building: r.building ?? '',
        display_order: r.display_order,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search rooms…"
    />
  );
}
