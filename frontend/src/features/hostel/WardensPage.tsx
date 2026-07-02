/* Wardens — Staff assigned to a hostel (chief / assistant). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { WARDEN_ROLES, hostelApi, type Ref } from './api';

export function WardensPage() {
  const { user } = useAuth();
  const [hostels, setHostels] = useState<FieldOption[]>([]);
  const [staff, setStaff] = useState<FieldOption[]>([]);

  useEffect(() => {
    hostelApi.hostels
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setHostels(r.data.map((h) => ({ value: String(h.id), label: String(h.name) }))));
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
      name: 'hostel_id',
      label: 'Hostel',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...hostels],
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
      options: WARDEN_ROLES.map((r) => ({ value: r, label: `${r} warden` })),
    },
    { name: 'assigned_on', label: 'Assigned on', type: 'date' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'staff',
      header: 'Warden',
      render: (r) => (
        <span className="font-medium">{String((r.staff as { name?: string })?.name ?? '—')}</span>
      ),
    },
    {
      key: 'hostel',
      header: 'Hostel',
      render: (r) => String((r.hostel as { name?: string })?.name ?? '—'),
    },
    { key: 'role', header: 'Role', render: (r) => <AXBadge tone="navy">{String(r.role)}</AXBadge> },
  ];

  return (
    <EntityManager<Ref>
      title="Wardens"
      icon="user-shield"
      unitLabel="wardens"
      api={hostelApi.wardens}
      columns={columns}
      fields={fields}
      emptyForm={{ hostel_id: '', staff_id: '', role: 'chief', assigned_on: '' }}
      toForm={(r) => ({
        hostel_id: String(r.hostel_id),
        staff_id: String(r.staff_id),
        role: String(r.role),
        assigned_on: (r.assigned_on as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
