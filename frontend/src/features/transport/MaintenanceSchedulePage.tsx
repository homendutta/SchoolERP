/* Maintenance Schedule — planning only (service due date, odometer, reminder). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { transportApi, type Ref } from './api';

export function MaintenanceSchedulePage() {
  const { user } = useAuth();
  const [vehicles, setVehicles] = useState<FieldOption[]>([]);

  useEffect(() => {
    transportApi.vehicles
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) =>
        setVehicles(r.data.map((v) => ({ value: String(v.id), label: v.vehicle_number })))
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
    { name: 'service_type', label: 'Service type', type: 'text' },
    { name: 'service_due_date', label: 'Service due date', type: 'date' },
    { name: 'odometer_due', label: 'Odometer due', type: 'number' },
    { name: 'last_service_date', label: 'Last service date', type: 'date' },
    { name: 'reminder_days', label: 'Reminder (days before)', type: 'number' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'vehicle',
      header: 'Vehicle',
      render: (r) =>
        String((r.vehicle as { vehicle_number?: string })?.vehicle_number ?? r.vehicle_id ?? '—'),
    },
    { key: 'service', header: 'Service', render: (r) => (r.service_type as string) ?? '—' },
    { key: 'due', header: 'Due date', render: (r) => (r.service_due_date as string) ?? '—' },
    { key: 'odo', header: 'Odometer due', render: (r) => (r.odometer_due as number) ?? '—' },
  ];

  return (
    <EntityManager<Ref>
      title="Maintenance Schedule"
      icon="screwdriver-wrench"
      unitLabel="schedules"
      api={transportApi.maintenance}
      columns={columns}
      fields={fields}
      emptyForm={{
        vehicle_id: '',
        service_type: '',
        service_due_date: '',
        odometer_due: '',
        last_service_date: '',
        reminder_days: 7,
      }}
      toForm={(r) => ({
        vehicle_id: String(r.vehicle_id),
        service_type: (r.service_type as string) ?? '',
        service_due_date: (r.service_due_date as string) ?? '',
        odometer_due: (r.odometer_due as number) ?? '',
        last_service_date: (r.last_service_date as string) ?? '',
        reminder_days: (r.reminder_days as number) ?? 7,
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
