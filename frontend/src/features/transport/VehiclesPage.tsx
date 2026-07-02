/* Vehicles — number from the Number Generator (leave blank to auto-issue). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { FUEL_TYPES, VEHICLE_STATUSES, VEHICLE_TYPES, transportApi, type Vehicle } from './api';

const TONES: Record<string, 'green' | 'gray' | 'amber' | 'red'> = {
  active: 'green',
  inactive: 'gray',
  under_maintenance: 'amber',
  retired: 'red',
};

const fields: Field[] = [
  { name: 'vehicle_number', label: 'Vehicle number (blank = auto)', type: 'text' },
  { name: 'registration_number', label: 'Registration number', type: 'text' },
  {
    name: 'vehicle_type',
    label: 'Type',
    type: 'select',
    options: VEHICLE_TYPES.map((t) => ({ value: t, label: t.replace('_', ' ') })),
  },
  { name: 'manufacturer', label: 'Manufacturer', type: 'text' },
  { name: 'model', label: 'Model', type: 'text' },
  { name: 'year', label: 'Year', type: 'number' },
  { name: 'seating_capacity', label: 'Seating capacity', type: 'number', required: true },
  { name: 'reserved_seats', label: 'Reserved seats', type: 'number' },
  {
    name: 'fuel_type',
    label: 'Fuel',
    type: 'select',
    options: FUEL_TYPES.map((f) => ({ value: f, label: f })),
  },
  { name: 'odometer', label: 'Odometer', type: 'number' },
  {
    name: 'status',
    label: 'Status',
    type: 'select',
    options: VEHICLE_STATUSES.map((s) => ({ value: s, label: s.replace('_', ' ') })),
  },
];

export function VehiclesPage() {
  const { user } = useAuth();
  const columns: AXColumn<Vehicle>[] = [
    {
      key: 'vehicle_number',
      header: 'Vehicle #',
      render: (r) => <span className="font-medium">{r.vehicle_number}</span>,
    },
    {
      key: 'registration_number',
      header: 'Registration',
      render: (r) => <code className="text-xs text-gray-500">{r.registration_number ?? '—'}</code>,
    },
    { key: 'vehicle_type', header: 'Type', render: (r) => r.vehicle_type.replace('_', ' ') },
    {
      key: 'seating',
      header: 'Seats',
      render: (r) => `${r.seating_capacity} (−${r.reserved_seats})`,
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status.replace('_', ' ')}</AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<Vehicle>
      title="Vehicles"
      icon="bus"
      unitLabel="vehicles"
      api={transportApi.vehicles}
      columns={columns}
      fields={fields}
      emptyForm={{
        vehicle_number: '',
        registration_number: '',
        vehicle_type: 'bus',
        manufacturer: '',
        model: '',
        year: '',
        seating_capacity: 40,
        reserved_seats: 0,
        fuel_type: 'diesel',
        odometer: '',
        status: 'active',
      }}
      toForm={(r) => ({
        vehicle_number: r.vehicle_number,
        registration_number: r.registration_number ?? '',
        vehicle_type: r.vehicle_type,
        manufacturer: r.manufacturer ?? '',
        model: r.model ?? '',
        year: r.year ?? '',
        seating_capacity: r.seating_capacity,
        reserved_seats: r.reserved_seats,
        fuel_type: r.fuel_type,
        odometer: r.odometer ?? '',
        status: r.status,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="vehicle_number"
      searchPlaceholder="Search vehicles…"
      sort="vehicle_number"
    />
  );
}
