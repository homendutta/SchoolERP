/* Subjects — subject type comes from Master Data (never hardcoded). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { academicApi, type Subject } from './api';
import { EntityManager, statusCell, type Field } from './EntityManager';
import { useMasterValues } from './useReference';

export function SubjectsPage() {
  const { user } = useAuth();
  const subjectTypes = useMasterValues('subject_types');

  const fields: Field[] = [
    { name: 'code', label: 'Code', type: 'text', required: true },
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'short_name', label: 'Short name', type: 'text' },
    { name: 'subject_type_id', label: 'Subject type', type: 'select', options: subjectTypes },
    { name: 'credits', label: 'Credits', type: 'number' },
    { name: 'display_order', label: 'Display order', type: 'number' },
    { name: 'theory', label: 'Theory', type: 'checkbox' },
    { name: 'practical', label: 'Practical', type: 'checkbox' },
  ];

  const columns: AXColumn<Subject>[] = [
    {
      key: 'code',
      header: 'Code',
      render: (r) => <code className="text-xs text-gray-500">{r.code}</code>,
    },
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    {
      key: 'kind',
      header: 'Type',
      render: (r) => (
        <span className="flex gap-1">
          {r.theory && <AXBadge tone="navy">Theory</AXBadge>}
          {r.practical && <AXBadge tone="amber">Practical</AXBadge>}
        </span>
      ),
    },
    { key: 'credits', header: 'Credits' },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Subject>
      title="Subjects"
      icon="book"
      unitLabel="subjects"
      api={academicApi.subjects}
      columns={columns}
      fields={fields}
      emptyForm={{
        code: '',
        name: '',
        short_name: '',
        subject_type_id: null,
        credits: 0,
        display_order: 0,
        theory: true,
        practical: false,
      }}
      toForm={(r) => ({
        code: r.code,
        name: r.name,
        short_name: r.short_name ?? '',
        subject_type_id: r.subject_type_id,
        credits: r.credits,
        display_order: r.display_order,
        theory: r.theory,
        practical: r.practical,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search subjects…"
      sort="display_order"
    />
  );
}
