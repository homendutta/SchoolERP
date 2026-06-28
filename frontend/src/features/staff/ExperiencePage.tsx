/* Staff Experience — unlimited prior employment history. */
import { useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import type { AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { staffApi, type Experience } from './api';
import { StaffPicker, useStaffList } from './StaffPicker';

const fields: Field[] = [
  { name: 'organization', label: 'Organization', type: 'text', required: true },
  { name: 'designation', label: 'Designation', type: 'text' },
  { name: 'from_date', label: 'From', type: 'date' },
  { name: 'to_date', label: 'To', type: 'date' },
  { name: 'reason_for_leaving', label: 'Reason for leaving', type: 'text' },
];

export function ExperiencePage() {
  const { user } = useAuth();
  const staff = useStaffList();
  const [id, setId] = useState('');

  const columns: AXColumn<Experience>[] = [
    {
      key: 'organization',
      header: 'Organization',
      render: (r) => <span className="font-medium">{r.organization}</span>,
    },
    { key: 'designation', header: 'Designation', render: (r) => r.designation ?? '—' },
    {
      key: 'period',
      header: 'Period',
      render: (r) => (
        <span className="text-xs text-gray-500">
          {r.from_date ?? '—'} → {r.to_date ?? '—'}
        </span>
      ),
    },
    { key: 'reason', header: 'Reason', render: (r) => r.reason_for_leaving ?? '—' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-briefcase text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Experience</h2>
        </div>
        <StaffPicker value={id} onChange={setId} staff={staff} />
      </div>

      {id && (
        <EntityManager<Experience>
          title="Experience"
          icon="briefcase"
          unitLabel="records"
          api={staffApi.experience}
          columns={columns}
          fields={fields}
          emptyForm={{
            organization: '',
            designation: '',
            from_date: '',
            to_date: '',
            reason_for_leaving: '',
          }}
          toForm={(r) => ({
            organization: r.organization,
            designation: r.designation ?? '',
            from_date: r.from_date ?? '',
            to_date: r.to_date ?? '',
            reason_for_leaving: r.reason_for_leaving ?? '',
          })}
          createDefaults={{ school_id: user?.school_id, staff_id: Number(id) }}
          listParams={{ filter: { staff_id: id } }}
          sort="created_at"
        />
      )}
    </div>
  );
}
