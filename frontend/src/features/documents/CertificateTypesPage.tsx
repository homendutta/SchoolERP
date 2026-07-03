/* Certificate Types — configurable (Transfer, Bonafide, Salary, ...). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import {
  EntityManager,
  statusCell,
  type Field,
  type FieldOption,
} from '@features/academic/EntityManager';
import { SUBJECT_KINDS, documentsApi, type Ref } from './api';

export function CertificateTypesPage() {
  const { user } = useAuth();
  const [categories, setCategories] = useState<FieldOption[]>([]);

  useEffect(() => {
    documentsApi.categories
      .list({ filter: { school_id: user?.school_id }, per_page: 200 })
      .then((r) =>
        setCategories(r.data.map((c) => ({ value: String(c.id), label: String(c.name) })))
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'name', label: 'Certificate name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    {
      name: 'category_id',
      label: 'Category',
      type: 'select',
      options: [{ value: '', label: '—' }, ...categories],
    },
    {
      name: 'subject_kind',
      label: 'Subject',
      type: 'select',
      options: SUBJECT_KINDS.map((s) => ({ value: s, label: s })),
    },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Certificate',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'subject', header: 'Subject', render: (r) => String(r.subject_kind) },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Certificate Types"
      icon="award"
      unitLabel="types"
      api={documentsApi.certificateTypes}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '', category_id: '', subject_kind: 'student' }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        category_id: r.category_id ? String(r.category_id) : '',
        subject_kind: String(r.subject_kind ?? 'student'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search certificate types…"
      sort="name"
    />
  );
}
