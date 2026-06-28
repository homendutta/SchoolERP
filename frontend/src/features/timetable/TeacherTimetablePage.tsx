/* Teacher Timetable — derived from the master class timetable, with calculated
 * workload (periods/week, per-day, subject & class load). */
import { useEffect, useState } from 'react';
import { AXBadge, AXSelect } from '@ui/ax';
import { useYears } from '@features/academic/useReference';
import { timetableApi, useStaffTeachers, type TimetableSlot, type Workload } from './api';
import { DerivedGrid } from './DerivedGrid';

export function TeacherTimetablePage() {
  const years = useYears();
  const teachers = useStaffTeachers();
  const [year, setYear] = useState('');
  const [teacher, setTeacher] = useState('');
  const [slots, setSlots] = useState<TimetableSlot[]>([]);
  const [workload, setWorkload] = useState<Workload | null>(null);

  useEffect(() => {
    if (!year || !teacher) {
      setSlots([]);
      setWorkload(null);
      return;
    }
    timetableApi.teacherTimetable(Number(teacher), { academic_year_id: year }).then((r) => {
      setSlots(r.slots ?? []);
      setWorkload(r.workload ?? null);
    });
  }, [year, teacher]);

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-chalkboard-user text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Teacher Timetable</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-44">
          <AXSelect
            label="Academic year"
            value={year}
            onChange={(e) => setYear(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...years]}
          />
        </div>
        <div className="w-64">
          <AXSelect
            label="Teacher"
            value={teacher}
            onChange={(e) => setTeacher(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...teachers]}
          />
        </div>
      </div>

      {workload && (
        <div className="flex flex-wrap gap-3 text-sm">
          <AXBadge tone="navy">Periods / week: {workload.periods_per_week}</AXBadge>
          <AXBadge tone="navy">Subjects: {workload.subject_load.length}</AXBadge>
          <AXBadge tone="navy">Classes: {workload.class_load.length}</AXBadge>
          {workload.periods_per_day.map((d) => (
            <AXBadge key={d.weekday} tone="gray">
              {d.weekday.slice(0, 3)}: {d.count}
            </AXBadge>
          ))}
        </div>
      )}

      {year && teacher && <DerivedGrid slots={slots} show="teacher" />}
    </div>
  );
}
