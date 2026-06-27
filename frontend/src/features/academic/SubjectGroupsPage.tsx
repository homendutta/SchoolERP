/* Subject Groups — many-to-many with subjects (no default records). */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { academicApi, type SubjectGroup } from './api';
import { EntityManager, statusCell, type Field } from './EntityManager';
import { useSubjects } from './useReference';

export function SubjectGroupsPage() {
  const { user } = useAuth();
  const subjects = useSubjects();

  const fields: Field[] = [
    { name: 'code', label: 'Code', type: 'text', required: true },
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'display_order', label: 'Display order', type: 'number' },
    { name: 'subject_ids', label: 'Subjects', type: 'multiselect', options: subjects },
  ];

  const columns: AXColumn<SubjectGroup>[] = [
    {
      key: 'code',
      header: 'Code',
      render: (r) => <code className="text-xs text-gray-500">{r.code}</code>,
    },
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    {
      key: 'subjects',
      header: 'Subjects',
      render: (r) => (
        <AXBadge tone="navy">{r.subjects?.length ?? r.subject_ids?.length ?? 0}</AXBadge>
      ),
    },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<SubjectGroup>
      title="Subject Groups"
      icon="object-group"
      unitLabel="groups"
      api={academicApi.subjectGroups}
      columns={columns}
      fields={fields}
      emptyForm={{ code: '', name: '', display_order: 0, subject_ids: [] }}
      toForm={(r) => ({
        code: r.code,
        name: r.name,
        display_order: r.display_order,
        subject_ids: r.subject_ids ?? r.subjects?.map((s) => s.id) ?? [],
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search groups…"
      sort="display_order"
    />
  );
}
