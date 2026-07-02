/* Trips — a route run by a vehicle + driver for a shift (daily operations). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { TRIP_SHIFTS, TRIP_STATUSES, transportApi, type Trip } from './api';

export function TripsPage() {
  const { user } = useAuth();
  const [vehicles, setVehicles] = useState<FieldOption[]>([]);
  const [routes, setRoutes] = useState<FieldOption[]>([]);
  const [staff, setStaff] = useState<FieldOption[]>([]);

  useEffect(() => {
    const f = { filter: { school_id: user?.school_id }, per_page: 500 };
    transportApi.vehicles
      .list(f)
      .then((r) =>
        setVehicles(r.data.map((v) => ({ value: String(v.id), label: v.vehicle_number })))
      );
    transportApi.routes
      .list(f)
      .then((r) => setRoutes(r.data.map((x) => ({ value: String(x.id), label: x.name }))));
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
      name: 'vehicle_id',
      label: 'Vehicle',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...vehicles],
      required: true,
    },
    {
      name: 'route_id',
      label: 'Route',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...routes],
      required: true,
    },
    {
      name: 'driver_id',
      label: 'Driver',
      type: 'select',
      options: [{ value: '', label: '—' }, ...staff],
    },
    {
      name: 'attendant_id',
      label: 'Attendant',
      type: 'select',
      options: [{ value: '', label: '—' }, ...staff],
    },
    {
      name: 'shift',
      label: 'Shift',
      type: 'select',
      options: TRIP_SHIFTS.map((s) => ({ value: s, label: s })),
    },
    { name: 'departure_time', label: 'Departure (HH:MM)', type: 'text' },
    { name: 'arrival_time', label: 'Arrival (HH:MM)', type: 'text' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: TRIP_STATUSES.map((s) => ({ value: s, label: s })),
    },
  ];

  const columns: AXColumn<Trip>[] = [
    {
      key: 'route',
      header: 'Route',
      render: (r) => <span className="font-medium">{r.route?.name ?? r.route_id}</span>,
    },
    { key: 'vehicle', header: 'Vehicle', render: (r) => r.vehicle?.vehicle_number ?? r.vehicle_id },
    { key: 'driver', header: 'Driver', render: (r) => r.driver?.name ?? '—' },
    { key: 'shift', header: 'Shift', render: (r) => <AXBadge tone="navy">{r.shift}</AXBadge> },
    { key: 'status', header: 'Status', render: (r) => r.status },
  ];

  return (
    <EntityManager<Trip>
      title="Trips"
      icon="clock"
      unitLabel="trips"
      api={transportApi.trips}
      columns={columns}
      fields={fields}
      emptyForm={{
        vehicle_id: '',
        route_id: '',
        driver_id: '',
        attendant_id: '',
        shift: 'morning',
        departure_time: '',
        arrival_time: '',
        status: 'scheduled',
      }}
      toForm={(r) => ({
        vehicle_id: String(r.vehicle_id),
        route_id: String(r.route_id),
        driver_id: r.driver_id ? String(r.driver_id) : '',
        attendant_id: '',
        shift: r.shift,
        departure_time: '',
        arrival_time: '',
        status: r.status,
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
