/* HR Designations — hierarchical; grade; code auto-issued by the Number Generator. */
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

export function DesignationsPage() {
  const { user } = useAuth();
  const [departments, setDepartments] = useState<FieldOption[]>([]);
  const [parents, setParents] = useState<FieldOption[]>([]);

  useEffect(() => {
    const f = { filter: { school_id: user?.school_id }, per_page: 500 };
    hrApi.departments
      .list(f)
      .then((r) =>
        setDepartments(r.data.map((d) => ({ value: String(d.id), label: String(d.name) })))
      );
    hrApi.designations
      .list(f)
      .then((r) => setParents(r.data.map((d) => ({ value: String(d.id), label: String(d.name) }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'name', label: 'Designation name', type: 'text', required: true },
    { name: 'department_id', label: 'Department', type: 'select', options: departments },
    { name: 'grade', label: 'Grade', type: 'text' },
    { name: 'code', label: 'Code (blank = auto)', type: 'text' },
    { name: 'parent_id', label: 'Reports to', type: 'select', options: parents },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Name',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    {
      key: 'department',
      header: 'Department',
      render: (r) => String((r.department as { name?: string })?.name ?? '—'),
    },
    { key: 'grade', header: 'Grade', render: (r) => String(r.grade ?? '—') },
    { key: 'code', header: 'Code', render: (r) => String(r.code ?? '—') },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Designations"
      icon="user-tie"
      unitLabel="designations"
      api={hrApi.designations}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', department_id: '', grade: '', code: '', parent_id: '' }}
      toForm={(r) => ({
        name: r.name,
        department_id: r.department_id ? String(r.department_id) : '',
        grade: r.grade ?? '',
        code: r.code ?? '',
        parent_id: r.parent_id ? String(r.parent_id) : '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search designations…"
      sort="name"
    />
  );
}
