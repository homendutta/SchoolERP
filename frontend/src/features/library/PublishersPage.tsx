/* Library Publishers — managed independently; books reference them. */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { libraryApi, type Ref } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'code', label: 'Code', type: 'text' },
  { name: 'address', label: 'Address', type: 'text' },
  { name: 'website', label: 'Website', type: 'text' },
];

export function PublishersPage() {
  const { user } = useAuth();
  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Publisher',
      render: (r) => <span className="font-medium">{r.name}</span>,
    },
    { key: 'website', header: 'Website', render: (r) => (r.website as string) ?? '—' },
  ];

  return (
    <EntityManager<Ref>
      title="Publishers"
      icon="building"
      unitLabel="publishers"
      api={libraryApi.publishers}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '', address: '', website: '' }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        address: (r.address as string) ?? '',
        website: (r.website as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search publishers…"
      sort="name"
    />
  );
}
