/* Exam Types — schools configure their own (Unit Test, Annual, Practical…). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { examApi, type ExamType } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'code', label: 'Code', type: 'text' },
  { name: 'weightage', label: 'Weightage (%)', type: 'number' },
  { name: 'description', label: 'Description', type: 'text' },
];

export function ExamTypesPage() {
  const { user } = useAuth();

  const columns: AXColumn<ExamType>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'code', header: 'Code', render: (r) => r.code ?? '—' },
    {
      key: 'weightage',
      header: 'Weightage',
      render: (r) => (r.weightage != null ? `${r.weightage}%` : '—'),
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={r.is_active ? 'green' : 'gray'}>
          {r.is_active ? 'Active' : 'Inactive'}
        </AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<ExamType>
      title="Exam Types"
      icon="file-lines"
      unitLabel="exam types"
      api={examApi.types}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '', weightage: '', description: '' }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        weightage: r.weightage ?? '',
        description: r.description ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search exam types…"
      sort="sort_order"
    />
  );
}
