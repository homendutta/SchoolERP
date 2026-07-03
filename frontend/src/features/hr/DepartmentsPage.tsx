/* HR Departments — hierarchical; code auto-issued by the Number Generator. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import {
  EntityManager,
  statusCell,
  type Field,
  type FieldOption,
} from '@features/academic/EntityManager';
import { hrApi, type Ref } from './api';

export function DepartmentsPage() {
  const { user } = useAuth();
  const [parents, setParents] = useState<FieldOption[]>([]);

  useEffect(() => {
    hrApi.departments
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setParents(r.data.map((d) => ({ value: String(d.id), label: String(d.name) }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'name', label: 'Department name', type: 'text', required: true },
    { name: 'code', label: 'Code (blank = auto)', type: 'text' },
    { name: 'parent_id', label: 'Parent department', type: 'select', options: parents },
    { name: 'description', label: 'Description', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Name',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'code', header: 'Code', render: (r) => String(r.code ?? '—') },
    {
      key: 'parent',
      header: 'Parent',
      render: (r) => String((r.parent as { name?: string })?.name ?? '—'),
    },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Departments"
      icon="sitemap"
      unitLabel="departments"
      api={hrApi.departments}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '', parent_id: '', description: '' }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        parent_id: r.parent_id ? String(r.parent_id) : '',
        description: r.description ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search departments…"
      sort="name"
    />
  );
}
