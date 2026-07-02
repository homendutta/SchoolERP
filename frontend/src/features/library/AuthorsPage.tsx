/* Library Authors — many-to-many with books. */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { libraryApi, type Ref } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'bio', label: 'Bio', type: 'text' },
];

export function AuthorsPage() {
  const { user } = useAuth();
  const columns: AXColumn<Ref>[] = [
    { key: 'name', header: 'Author', render: (r) => <span className="font-medium">{r.name}</span> },
  ];

  return (
    <EntityManager<Ref>
      title="Authors"
      icon="feather"
      unitLabel="authors"
      api={libraryApi.authors}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', bio: '' }}
      toForm={(r) => ({ name: r.name, bio: (r.bio as string) ?? '' })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search authors…"
      sort="name"
    />
  );
}
