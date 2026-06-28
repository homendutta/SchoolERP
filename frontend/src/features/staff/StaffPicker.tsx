/* Shared staff selector used by the operational staff pages. */
import { useEffect, useState } from 'react';
import { AXSelect } from '@ui/ax';
import { staffApi, type Staff } from './api';

export function useStaffList() {
  const [staff, setStaff] = useState<Staff[]>([]);
  useEffect(() => {
    staffApi.staff.list({ per_page: 200, sort: 'name' }).then((r) => setStaff(r.data));
  }, []);
  return staff;
}

export function StaffPicker({
  value,
  onChange,
  staff,
}: {
  value: string;
  onChange: (id: string) => void;
  staff: Staff[];
}) {
  return (
    <div className="w-96">
      <AXSelect
        value={value}
        onChange={(e) => onChange(e.target.value)}
        options={[
          { value: '', label: 'Select a staff member…' },
          ...staff.map((s) => ({ value: String(s.id), label: `${s.employee_number} — ${s.name}` })),
        ]}
      />
    </div>
  );
}
