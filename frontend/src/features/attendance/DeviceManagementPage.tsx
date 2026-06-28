/* Device Management — biometric devices (multiple per school). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { attendanceApi, type Device } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Device name', type: 'text', required: true },
  { name: 'device_identifier', label: 'Device identifier (serial)', type: 'text', required: true },
  {
    name: 'device_type',
    label: 'Device type',
    type: 'select',
    options: [{ value: 'essl_mb20', label: 'eSSL MB20' }],
  },
  { name: 'location', label: 'Location', type: 'text' },
  {
    name: 'status',
    label: 'Status',
    type: 'select',
    options: [
      { value: 'active', label: 'Active' },
      { value: 'inactive', label: 'Inactive' },
    ],
  },
];

export function DeviceManagementPage() {
  const { user } = useAuth();

  const columns: AXColumn<Device>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    {
      key: 'device_identifier',
      header: 'Identifier',
      render: (r) => <code className="text-xs text-gray-500">{r.device_identifier}</code>,
    },
    {
      key: 'device_type',
      header: 'Type',
      render: (r) => <AXBadge tone="navy">{r.device_type}</AXBadge>,
    },
    { key: 'location', header: 'Location', render: (r) => r.location ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={r.status === 'active' ? 'green' : 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'last_sync',
      header: 'Last Sync',
      render: (r) => r.last_sync_at?.slice(0, 19).replace('T', ' ') ?? '—',
    },
  ];

  return (
    <EntityManager<Device>
      title="Biometric Devices"
      icon="fingerprint"
      unitLabel="devices"
      api={attendanceApi.devices}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        device_identifier: '',
        device_type: 'essl_mb20',
        location: '',
        status: 'active',
      }}
      toForm={(r) => ({
        name: r.name,
        device_identifier: r.device_identifier,
        device_type: r.device_type,
        location: r.location ?? '',
        status: r.status,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search devices…"
      sort="name"
    />
  );
}
