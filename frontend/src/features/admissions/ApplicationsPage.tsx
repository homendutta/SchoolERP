/* Admission Applications — independent of Student records (no Student is created
 * here). Create, edit, submit for verification/approval. */
import { useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { useYears, useClasses, useAllSections } from '@features/academic/useReference';
import { admissionsApi, type Application } from './api';

const TONES: Record<string, 'navy' | 'green' | 'amber' | 'red' | 'gray'> = {
  draft: 'gray',
  submitted: 'navy',
  under_review: 'amber',
  verified: 'amber',
  approved: 'green',
  rejected: 'red',
  enrolled: 'green',
  cancelled: 'gray',
};

export function ApplicationsPage() {
  const { user } = useAuth();
  const years = useYears();
  const classes = useClasses();
  const sections = useAllSections();
  const [status, setStatus] = useState('');

  const fields: Field[] = [
    {
      name: 'academic_year_id',
      label: 'Academic year',
      type: 'select',
      options: years,
      required: true,
    },
    { name: 'class_id', label: 'Class', type: 'select', options: classes, required: true },
    { name: 'section_id', label: 'Section', type: 'select', options: sections },
    { name: 'student_name', label: 'Student name', type: 'text', required: true },
    { name: 'gender', label: 'Gender', type: 'text' },
    { name: 'date_of_birth', label: 'Date of birth', type: 'date' },
    { name: 'guardian_name', label: 'Guardian name', type: 'text', required: true },
    { name: 'guardian_relation', label: 'Guardian relation', type: 'text' },
    { name: 'guardian_phone', label: 'Guardian phone', type: 'text' },
    { name: 'guardian_email', label: 'Guardian email', type: 'text' },
    { name: 'address', label: 'Address', type: 'text' },
    { name: 'previous_school', label: 'Previous school', type: 'text' },
    { name: 'previous_class', label: 'Previous class', type: 'text' },
    { name: 'remarks', label: 'Remarks', type: 'text' },
  ];

  const columns: AXColumn<Application>[] = [
    {
      key: 'application_number',
      header: 'No.',
      render: (r) => <code className="text-xs text-gray-500">{r.application_number}</code>,
    },
    {
      key: 'student_name',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student_name}</span>,
    },
    { key: 'guardian_name', header: 'Guardian', render: (r) => r.guardian_name },
    {
      key: 'verification_status',
      header: 'Verification',
      render: (r) => (
        <AXBadge tone={TONES[r.verification_status] ?? 'gray'}>{r.verification_status}</AXBadge>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
  ];

  const statusFilter = [
    { value: 'draft', label: 'Draft' },
    { value: 'submitted', label: 'Submitted' },
    { value: 'under_review', label: 'Under Review' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'enrolled', label: 'Enrolled' },
  ];

  return (
    <EntityManager<Application>
      title="Admission Applications"
      icon="file-signature"
      unitLabel="applications"
      api={admissionsApi.applications}
      columns={columns}
      fields={fields}
      emptyForm={{
        academic_year_id: null,
        class_id: null,
        section_id: null,
        student_name: '',
        gender: '',
        date_of_birth: '',
        guardian_name: '',
        guardian_relation: '',
        guardian_phone: '',
        guardian_email: '',
        address: '',
        previous_school: '',
        previous_class: '',
        remarks: '',
      }}
      toForm={(r) => ({
        academic_year_id: r.academic_year_id,
        class_id: r.class_id,
        section_id: r.section_id,
        student_name: r.student_name,
        gender: r.gender ?? '',
        date_of_birth: r.date_of_birth ?? '',
        guardian_name: r.guardian_name,
        guardian_relation: r.guardian_relation ?? '',
        guardian_phone: r.guardian_phone ?? '',
        guardian_email: r.guardian_email ?? '',
        address: r.address ?? '',
        previous_school: r.previous_school ?? '',
        previous_class: r.previous_class ?? '',
        remarks: r.remarks ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="student_name"
      searchPlaceholder="Search applications…"
      sort="created_at"
      listParams={status ? { filter: { status } } : {}}
      filters={[
        {
          name: 'status',
          label: 'Status',
          options: statusFilter,
          value: status,
          onChange: setStatus,
        },
      ]}
      rowExtras={(r, reload) =>
        r.status === 'draft' ? (
          <button
            onClick={() => admissionsApi.applications.submit(r.id).then(reload)}
            title="Submit"
            className="hover:text-[var(--success)]"
          >
            <i className="fas fa-paper-plane" />
          </button>
        ) : null
      }
    />
  );
}
