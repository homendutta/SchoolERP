/* Hostels — code from the Number Generator (blank = auto). Multiple per school. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { HOSTEL_GENDERS, hostelApi, type Ref } from './api';

const fields: Field[] = [
  { name: 'code', label: 'Code (blank = auto)', type: 'text' },
  { name: 'name', label: 'Name', type: 'text', required: true },
  {
    name: 'gender',
    label: 'Gender',
    type: 'select',
    options: HOSTEL_GENDERS.map((g) => ({ value: g, label: g.replace('_', '-') })),
  },
  { name: 'address', label: 'Address', type: 'text' },
  { name: 'description', label: 'Description', type: 'text' },
];

export function HostelsPage() {
  const { user } = useAuth();
  const columns: AXColumn<Ref>[] = [
    {
      key: 'code',
      header: 'Code',
      render: (r) => <code className="text-xs text-gray-500">{String(r.code ?? '—')}</code>,
    },
    {
      key: 'name',
      header: 'Hostel',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    {
      key: 'gender',
      header: 'Gender',
      render: (r) => <AXBadge tone="navy">{String(r.gender).replace('_', '-')}</AXBadge>,
    },
    { key: 'address', header: 'Address', render: (r) => String(r.address ?? '—') },
  ];

  return (
    <EntityManager<Ref>
      title="Hostels"
      icon="building"
      unitLabel="hostels"
      api={hostelApi.hostels}
      columns={columns}
      fields={fields}
      emptyForm={{ code: '', name: '', gender: 'boys', address: '', description: '' }}
      toForm={(r) => ({
        code: (r.code as string) ?? '',
        name: String(r.name),
        gender: String(r.gender),
        address: (r.address as string) ?? '',
        description: (r.description as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search hostels…"
      sort="name"
    />
  );
}
