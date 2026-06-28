/* Shared student selector used by the operational student pages. */
import { useEffect, useState } from 'react';
import { AXSelect } from '@ui/ax';
import { studentsApi, type Student } from './api';

export function useStudentList() {
  const [students, setStudents] = useState<Student[]>([]);
  useEffect(() => {
    studentsApi.list({ per_page: 200, sort: 'name' }).then((r) => setStudents(r.data));
  }, []);
  return students;
}

export function StudentPicker({
  value,
  onChange,
  students,
}: {
  value: string;
  onChange: (id: string) => void;
  students: Student[];
}) {
  return (
    <div className="w-96">
      <AXSelect
        value={value}
        onChange={(e) => onChange(e.target.value)}
        options={[
          { value: '', label: 'Select a student…' },
          ...students.map((s) => ({
            value: String(s.id),
            label: `${s.admission_number} — ${s.name}`,
          })),
        ]}
      />
    </div>
  );
}
