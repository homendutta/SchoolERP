/* Visitors — ID proof via Media; workflow pending → approved → in/out. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { studentsApi } from '@features/students/api';
import { VISITOR_STATUSES, hostelApi, type Ref } from './api';

const TONES: Record<string, 'amber' | 'green' | 'navy' | 'gray' | 'red'> = {
  pending: 'amber',
  approved: 'green',
  checked_in: 'navy',
  checked_out: 'gray',
  rejected: 'red',
};

export function VisitorsPage() {
  const { user } = useAuth();
  const [students, setStudents] = useState<FieldOption[]>([]);

  useEffect(() => {
    studentsApi
      .list({ per_page: 500, sort: 'name' })
      .then((r) =>
        setStudents(
          r.data.map((s) => ({ value: String(s.id), label: `${s.admission_number} — ${s.name}` }))
        )
      );
  }, []);

  const fields: Field[] = [
    { name: 'visitor_name', label: 'Visitor name', type: 'text', required: true },
    {
      name: 'student_id',
      label: 'Student',
      type: 'select',
      options: [{ value: '', label: '—' }, ...students],
    },
    { name: 'identity_proof', label: 'Identity proof', type: 'text' },
    { name: 'id_media_id', label: 'ID media ID', type: 'number' },
    { name: 'visit_date', label: 'Visit date', type: 'date' },
    { name: 'purpose', label: 'Purpose', type: 'text' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: VISITOR_STATUSES.map((s) => ({ value: s, label: s.replace('_', ' ') })),
    },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'visitor',
      header: 'Visitor',
      render: (r) => <span className="font-medium">{String(r.visitor_name)}</span>,
    },
    {
      key: 'student',
      header: 'Student',
      render: (r) => String((r.student as { name?: string })?.name ?? '—'),
    },
    { key: 'date', header: 'Visit date', render: (r) => String(r.visit_date ?? '—') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>
          {String(r.status).replace('_', ' ')}
        </AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<Ref>
      title="Visitors"
      icon="user-check"
      unitLabel="visitors"
      api={hostelApi.visitors}
      columns={columns}
      fields={fields}
      emptyForm={{
        visitor_name: '',
        student_id: '',
        identity_proof: '',
        id_media_id: '',
        visit_date: '',
        purpose: '',
        status: 'pending',
      }}
      toForm={(r) => ({
        visitor_name: String(r.visitor_name),
        student_id: r.student_id ? String(r.student_id) : '',
        identity_proof: (r.identity_proof as string) ?? '',
        id_media_id: (r.id_media_id as number) ?? '',
        visit_date: (r.visit_date as string) ?? '',
        purpose: (r.purpose as string) ?? '',
        status: String(r.status),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="visitor_name"
      searchPlaceholder="Search visitors…"
      sort="id"
    />
  );
}
