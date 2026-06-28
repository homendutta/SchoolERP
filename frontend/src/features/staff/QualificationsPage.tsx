/* Staff Qualifications — unlimited per employee. */
import { useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import type { AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { staffApi, type Qualification } from './api';
import { StaffPicker, useStaffList } from './StaffPicker';

const fields: Field[] = [
  { name: 'qualification', label: 'Qualification', type: 'text', required: true },
  { name: 'institution', label: 'Institution', type: 'text' },
  { name: 'board_university', label: 'Board / University', type: 'text' },
  { name: 'year', label: 'Year', type: 'text' },
  { name: 'grade', label: 'Percentage / Grade', type: 'text' },
];

export function QualificationsPage() {
  const { user } = useAuth();
  const staff = useStaffList();
  const [id, setId] = useState('');

  const columns: AXColumn<Qualification>[] = [
    {
      key: 'qualification',
      header: 'Qualification',
      render: (r) => <span className="font-medium">{r.qualification}</span>,
    },
    { key: 'institution', header: 'Institution', render: (r) => r.institution ?? '—' },
    {
      key: 'board_university',
      header: 'Board/University',
      render: (r) => r.board_university ?? '—',
    },
    { key: 'year', header: 'Year', render: (r) => r.year ?? '—' },
    { key: 'grade', header: 'Grade', render: (r) => r.grade ?? '—' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-graduation-cap text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Qualifications</h2>
        </div>
        <StaffPicker value={id} onChange={setId} staff={staff} />
      </div>

      {id && (
        <EntityManager<Qualification>
          title="Qualification"
          icon="graduation-cap"
          unitLabel="qualifications"
          api={staffApi.qualifications}
          columns={columns}
          fields={fields}
          emptyForm={{
            qualification: '',
            institution: '',
            board_university: '',
            year: '',
            grade: '',
          }}
          toForm={(r) => ({
            qualification: r.qualification,
            institution: r.institution ?? '',
            board_university: r.board_university ?? '',
            year: r.year ?? '',
            grade: r.grade ?? '',
          })}
          createDefaults={{ school_id: user?.school_id, staff_id: Number(id) }}
          listParams={{ filter: { staff_id: id } }}
          sort="created_at"
        />
      )}
    </div>
  );
}
