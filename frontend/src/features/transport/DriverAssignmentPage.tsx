/* Driver Assignment — assign Staff to a vehicle as driver / attendant / helper. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { STAFF_ROLES, transportApi, type Ref } from './api';

export function DriverAssignmentPage() {
  const { user } = useAuth();
  const [vehicles, setVehicles] = useState<FieldOption[]>([]);
  const [staff, setStaff] = useState<FieldOption[]>([]);

  useEffect(() => {
    transportApi.vehicles
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) =>
        setVehicles(r.data.map((v) => ({ value: String(v.id), label: v.vehicle_number })))
      );
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
      name: 'staff_id',
      label: 'Staff member',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...staff],
      required: true,
    },
    {
      name: 'role',
      label: 'Role',
      type: 'select',
      options: STAFF_ROLES.map((r) => ({ value: r, label: r.replace('_', ' ') })),
      required: true,
    },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'vehicle',
      header: 'Vehicle',
      render: (r) => (r.vehicle as { vehicle_number?: string })?.vehicle_number ?? '—',
    },
    { key: 'staff', header: 'Staff', render: (r) => (r.staff as { name?: string })?.name ?? '—' },
    {
      key: 'role',
      header: 'Role',
      render: (r) => <AXBadge tone="navy">{String(r.role).replace('_', ' ')}</AXBadge>,
    },
  ];

  return (
    <EntityManager<Ref>
      title="Driver & Attendant Assignment"
      icon="id-card-clip"
      unitLabel="assignments"
      api={transportApi.drivers}
      columns={columns}
      fields={fields}
      emptyForm={{ vehicle_id: '', staff_id: '', role: 'primary_driver' }}
      toForm={(r) => ({
        vehicle_id: String(r.vehicle_id),
        staff_id: String(r.staff_id),
        role: String(r.role),
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
