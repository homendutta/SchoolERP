/* Staff Profile — full profile for a selected employee. */
import { useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { staffApi, type Staff } from './api';
import { StaffPicker, useStaffList } from './StaffPicker';

function Field({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div>
      <div className="text-xs uppercase tracking-wide text-gray-400">{label}</div>
      <div className="text-sm text-gray-800">{value || '—'}</div>
    </div>
  );
}

export function StaffProfilePage() {
  const staff = useStaffList();
  const [id, setId] = useState('');
  const [person, setPerson] = useState<Staff | null>(null);

  useEffect(() => {
    if (!id) {
      setPerson(null);
      return;
    }
    staffApi.staff.get(Number(id)).then(setPerson);
  }, [id]);

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-id-badge text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Staff Profile</h2>
        </div>
        <StaffPicker value={id} onChange={setId} staff={staff} />
      </div>

      {person && (
        <>
          <div className="erp-card flex flex-wrap items-center gap-4">
            <div className="text-lg font-semibold text-[var(--navy-primary)]">{person.name}</div>
            <span className="text-sm text-gray-500">Emp #{person.employee_number}</span>
            <AXBadge tone={person.is_teaching ? 'navy' : 'gray'}>
              {person.is_teaching ? 'Teaching' : 'Non-Teaching'}
            </AXBadge>
            <AXBadge tone="green">{person.status}</AXBadge>
          </div>

          <div className="erp-card grid grid-cols-2 gap-4 md:grid-cols-3">
            <Field label="Department" value={person.department?.label} />
            <Field label="Designation" value={person.designation?.label} />
            <Field label="Employment Type" value={person.employment_type} />
            <Field label="Joining Date" value={person.joining_date} />
            <Field label="Confirmation Date" value={person.confirmation_date} />
            <Field label="Gender" value={person.gender?.label} />
            <Field label="Phone" value={person.phone} />
            <Field label="Email" value={person.email} />
            <Field label="Blood Group" value={person.blood_group?.label} />
            <Field
              label="Address"
              value={[person.address, person.city, person.state].filter(Boolean).join(', ')}
            />
          </div>

          <div className="grid gap-4 md:grid-cols-2">
            <div className="erp-card">
              <h3 className="mb-2 text-sm font-semibold text-[var(--navy-primary)]">
                Qualifications
              </h3>
              {(person.qualifications ?? []).length === 0 ? (
                <p className="text-sm text-gray-400">None.</p>
              ) : (
                <ul className="space-y-1 text-sm">
                  {(person.qualifications ?? []).map((q) => (
                    <li key={q.id}>
                      {q.qualification} — {q.institution ?? ''} {q.year ?? ''}
                    </li>
                  ))}
                </ul>
              )}
            </div>
            <div className="erp-card">
              <h3 className="mb-2 text-sm font-semibold text-[var(--navy-primary)]">Experience</h3>
              {(person.experiences ?? []).length === 0 ? (
                <p className="text-sm text-gray-400">None.</p>
              ) : (
                <ul className="space-y-1 text-sm">
                  {(person.experiences ?? []).map((e) => (
                    <li key={e.id}>
                      {e.organization} — {e.designation ?? ''} ({e.from_date ?? ''} →{' '}
                      {e.to_date ?? ''})
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </div>
        </>
      )}
    </div>
  );
}
