/* Student Profile — full profile for a selected student, plus the ID-card / QR
 * preparation data (the designer is a future sprint). */
import { useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { studentsApi, type IdCard, type Student } from './api';
import { StudentPicker, useStudentList } from './StudentPicker';

function Field({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div>
      <div className="text-xs uppercase tracking-wide text-gray-400">{label}</div>
      <div className="text-sm text-gray-800">{value || '—'}</div>
    </div>
  );
}

export function StudentProfilePage() {
  const students = useStudentList();
  const [id, setId] = useState('');
  const [student, setStudent] = useState<Student | null>(null);
  const [card, setCard] = useState<IdCard | null>(null);

  useEffect(() => {
    if (!id) {
      setStudent(null);
      setCard(null);
      return;
    }
    studentsApi.get(Number(id)).then(setStudent);
    studentsApi
      .idCard(Number(id))
      .then(setCard)
      .catch(() => undefined);
  }, [id]);

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-id-badge text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Student Profile</h2>
        </div>
        <StudentPicker value={id} onChange={setId} students={students} />
      </div>

      {student && (
        <>
          <div className="erp-card flex flex-wrap items-center gap-4">
            <div className="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400">
              {card?.photo_url ? (
                <img src={card.photo_url} alt="" className="h-16 w-16 rounded-full object-cover" />
              ) : (
                <i className="fas fa-user text-2xl" />
              )}
            </div>
            <div>
              <div className="text-lg font-semibold text-[var(--navy-primary)]">{student.name}</div>
              <div className="text-sm text-gray-500">Admission #{student.admission_number}</div>
            </div>
            <AXBadge tone="green">{student.status}</AXBadge>
            <div className="ml-auto text-right">
              <div className="text-xs uppercase tracking-wide text-gray-400">QR data</div>
              <code className="text-xs text-gray-600">{card?.qr_data}</code>
            </div>
          </div>

          <div className="erp-card grid grid-cols-2 gap-4 md:grid-cols-3">
            <Field label="Class" value={student.current_record?.class?.name} />
            <Field label="Section" value={student.current_record?.section?.name} />
            <Field label="Academic Year" value={student.current_record?.academic_year?.name} />
            <Field label="Gender" value={student.gender} />
            <Field label="Date of Birth" value={student.date_of_birth} />
            <Field label="Blood Group" value={student.blood_group?.label ?? '—'} />
            <Field label="Phone" value={student.phone} />
            <Field label="Email" value={student.email} />
            <Field label="Category" value={student.category} />
            <Field
              label="Address"
              value={[student.address, student.city, student.state].filter(Boolean).join(', ')}
            />
          </div>

          <div className="erp-card">
            <h3 className="mb-2 text-sm font-semibold text-[var(--navy-primary)]">Guardians</h3>
            <div className="flex flex-wrap gap-3">
              {(student.guardians ?? []).map((g) => (
                <div key={g.id} className="rounded-md border border-gray-200 px-3 py-2 text-sm">
                  <div className="font-medium">
                    {g.name} {g.is_primary && <AXBadge tone="navy">Primary</AXBadge>}
                  </div>
                  <div className="text-xs text-gray-500">{g.phone ?? ''}</div>
                  <div className="mt-1 flex flex-wrap gap-1">
                    {g.emergency_contact && <AXBadge tone="amber">Emergency</AXBadge>}
                    {g.pickup_authorized && <AXBadge tone="green">Pickup</AXBadge>}
                    {g.financial_responsible && <AXBadge tone="navy">Financial</AXBadge>}
                  </div>
                </div>
              ))}
              {(student.guardians ?? []).length === 0 && (
                <span className="text-sm text-gray-400">No guardians linked.</span>
              )}
            </div>
          </div>
        </>
      )}
    </div>
  );
}
