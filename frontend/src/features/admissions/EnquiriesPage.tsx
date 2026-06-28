/* Admission Enquiries — capture prospective-student interest before any
 * application exists. Built on the shared AX EntityManager. */
import { useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import {
  EntityManager,
  statusCell,
  type Field,
  type FieldOption,
} from '@features/academic/EntityManager';
import { useYears, useMasterValues } from '@features/academic/useReference';
import { admissionsApi, type Enquiry } from './api';

const STATUS: FieldOption[] = [
  { value: 'new', label: 'New' },
  { value: 'contacted', label: 'Contacted' },
  { value: 'interested', label: 'Interested' },
  { value: 'converted', label: 'Converted' },
  { value: 'closed', label: 'Closed' },
];
const TONES: Record<string, 'navy' | 'green' | 'amber' | 'gray'> = {
  new: 'navy',
  contacted: 'amber',
  interested: 'amber',
  converted: 'green',
  closed: 'gray',
};

export function EnquiriesPage() {
  const { user } = useAuth();
  const years = useYears();
  const sources = useMasterValues('admission_sources');
  const [status, setStatus] = useState('');

  const fields: Field[] = [
    { name: 'student_name', label: 'Student name', type: 'text', required: true },
    { name: 'guardian_name', label: 'Guardian name', type: 'text' },
    { name: 'phone', label: 'Phone', type: 'text' },
    { name: 'email', label: 'Email', type: 'text' },
    { name: 'class_interested', label: 'Class interested', type: 'text' },
    { name: 'academic_year_id', label: 'Academic year', type: 'select', options: years },
    { name: 'source_id', label: 'Source', type: 'select', options: sources },
    { name: 'status', label: 'Status', type: 'select', options: STATUS },
    { name: 'follow_up_date', label: 'Follow-up date', type: 'date' },
    { name: 'remarks', label: 'Remarks', type: 'text' },
  ];

  const columns: AXColumn<Enquiry>[] = [
    {
      key: 'enquiry_number',
      header: 'No.',
      render: (r) => <code className="text-xs text-gray-500">{r.enquiry_number}</code>,
    },
    {
      key: 'student_name',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student_name}</span>,
    },
    { key: 'phone', header: 'Phone', render: (r) => r.phone ?? '—' },
    { key: 'class_interested', header: 'Class', render: (r) => r.class_interested ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    { key: 'archived', header: '', render: statusCell },
  ];

  return (
    <EntityManager<Enquiry>
      title="Admission Enquiries"
      icon="comments"
      unitLabel="enquiries"
      api={admissionsApi.enquiries}
      columns={columns}
      fields={fields}
      emptyForm={{
        student_name: '',
        guardian_name: '',
        phone: '',
        email: '',
        class_interested: '',
        academic_year_id: null,
        source_id: null,
        status: 'new',
        follow_up_date: '',
        remarks: '',
      }}
      toForm={(r) => ({
        student_name: r.student_name,
        guardian_name: r.guardian_name ?? '',
        phone: r.phone ?? '',
        email: r.email ?? '',
        class_interested: r.class_interested ?? '',
        academic_year_id: r.academic_year_id,
        source_id: r.source_id,
        status: r.status,
        follow_up_date: r.follow_up_date ?? '',
        remarks: r.remarks ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="student_name"
      searchPlaceholder="Search enquiries…"
      sort="created_at"
      listParams={status ? { filter: { status } } : {}}
      filters={[
        { name: 'status', label: 'Status', options: STATUS, value: status, onChange: setStatus },
      ]}
    />
  );
}
