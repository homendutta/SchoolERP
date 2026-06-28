/* Room Timetable — derived from the master class timetable. */
import { useEffect, useState } from 'react';
import { AXSelect } from '@ui/ax';
import { useRooms, useYears } from '@features/academic/useReference';
import { timetableApi, type TimetableSlot } from './api';
import { DerivedGrid } from './DerivedGrid';

export function RoomTimetablePage() {
  const years = useYears();
  const rooms = useRooms();
  const [year, setYear] = useState('');
  const [room, setRoom] = useState('');
  const [slots, setSlots] = useState<TimetableSlot[]>([]);

  useEffect(() => {
    if (!year || !room) {
      setSlots([]);
      return;
    }
    timetableApi
      .roomTimetable(Number(room), { academic_year_id: year })
      .then((rows) => setSlots(Array.isArray(rows) ? rows : []));
  }, [year, room]);

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-door-open text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Room Timetable</h2>
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
        <div className="w-56">
          <AXSelect
            label="Room"
            value={room}
            onChange={(e) => setRoom(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...rooms]}
          />
        </div>
      </div>

      {year && room && <DerivedGrid slots={slots} show="room" />}
    </div>
  );
}
