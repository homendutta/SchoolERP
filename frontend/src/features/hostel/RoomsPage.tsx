/* Rooms — room type is Master Data; capacity enforced when adding beds. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { useMasterValues } from '@features/academic/useReference';
import { hostelApi, type Ref } from './api';

export function RoomsPage() {
  const { user } = useAuth();
  const [hostels, setHostels] = useState<FieldOption[]>([]);
  const [buildings, setBuildings] = useState<FieldOption[]>([]);
  const [floors, setFloors] = useState<FieldOption[]>([]);
  const roomTypes = useMasterValues('hostel_room_types');

  useEffect(() => {
    const f = { filter: { school_id: user?.school_id }, per_page: 500 };
    hostelApi.hostels
      .list(f)
      .then((r) => setHostels(r.data.map((h) => ({ value: String(h.id), label: String(h.name) }))));
    hostelApi.buildings
      .list(f)
      .then((r) =>
        setBuildings(r.data.map((b) => ({ value: String(b.id), label: String(b.name) })))
      );
    hostelApi.floors
      .list(f)
      .then((r) =>
        setFloors(
          r.data.map((fl) => ({
            value: String(fl.id),
            label: `${fl.name ?? 'Floor'} (${fl.floor_number})`,
          }))
        )
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    {
      name: 'hostel_id',
      label: 'Hostel',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...hostels],
      required: true,
    },
    {
      name: 'building_id',
      label: 'Building',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...buildings],
      required: true,
    },
    {
      name: 'floor_id',
      label: 'Floor',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...floors],
      required: true,
    },
    { name: 'room_number', label: 'Room number', type: 'text', required: true },
    {
      name: 'room_type_id',
      label: 'Room type',
      type: 'select',
      options: [{ value: '', label: '—' }, ...roomTypes],
    },
    { name: 'capacity', label: 'Capacity', type: 'number', required: true },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'room',
      header: 'Room',
      render: (r) => <span className="font-medium">{String(r.room_number)}</span>,
    },
    {
      key: 'hostel',
      header: 'Hostel',
      render: (r) => String((r.hostel as { name?: string })?.name ?? '—'),
    },
    { key: 'capacity', header: 'Capacity', render: (r) => String(r.capacity ?? 0) },
    {
      key: 'beds',
      header: 'Beds',
      render: (r) => <AXBadge tone="navy">{String(r.beds_count ?? 0)}</AXBadge>,
    },
    { key: 'status', header: 'Status', render: (r) => String(r.status).replace('_', ' ') },
  ];

  return (
    <EntityManager<Ref>
      title="Rooms"
      icon="door-closed"
      unitLabel="rooms"
      api={hostelApi.rooms}
      columns={columns}
      fields={fields}
      emptyForm={{
        hostel_id: '',
        building_id: '',
        floor_id: '',
        room_number: '',
        room_type_id: '',
        capacity: 2,
        status: 'available',
      }}
      toForm={(r) => ({
        hostel_id: String(r.hostel_id),
        building_id: String(r.building_id),
        floor_id: String(r.floor_id),
        room_number: String(r.room_number),
        room_type_id: r.room_type_id ? String(r.room_type_id) : '',
        capacity: (r.capacity as number) ?? 2,
        status: String(r.status),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="room_number"
      searchPlaceholder="Search rooms…"
      sort="room_number"
    />
  );
}
