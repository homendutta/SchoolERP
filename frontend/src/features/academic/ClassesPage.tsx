/* Classes — unlimited, searchable, sortable, archive/restore. */
import { useAuth } from '@core/auth/AuthContext';
import type { AXColumn } from '@ui/ax';
import { academicApi, type SchoolClass } from './api';
import { EntityManager, statusCell, type Field } from './EntityManager';

const fields: Field[] = [
  { name: 'code', label: 'Code', type: 'text', required: true },
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'short_name', label: 'Short name', type: 'text' },
  { name: 'display_order', label: 'Display order', type: 'number' },
];

export function ClassesPage() {
  const { user } = useAuth();
  const columns: AXColumn<SchoolClass>[] = [
    {
      key: 'code',
      header: 'Code',
      render: (r) => <code className="text-xs text-gray-500">{r.code}</code>,
    },
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'display_order', header: 'Order' },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<SchoolClass>
      title="Classes"
      icon="school"
      unitLabel="classes"
      api={academicApi.classes}
      columns={columns}
      fields={fields}
      emptyForm={{ code: '', name: '', short_name: '', display_order: 0 }}
      toForm={(r) => ({
        code: r.code,
        name: r.name,
        short_name: r.short_name ?? '',
        display_order: r.display_order,
        version: r.version,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search classes…"
      sort="display_order"
    />
  );
}
