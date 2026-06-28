/* Class Timetable — the master grid (periods × weekdays). Writes run clash
 * detection server-side; teacher/room timetables are derived from this. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXModal, AXSelect } from '@ui/ax';
import {
  useClasses,
  useRooms,
  useSections,
  useSubjects,
  useYears,
} from '@features/academic/useReference';
import { timetableApi, useStaffTeachers, WEEKDAYS, type Period, type TimetableSlot } from './api';

export function ClassTimetablePage() {
  const { user } = useAuth();
  const years = useYears();
  const classes = useClasses();
  const subjects = useSubjects();
  const rooms = useRooms();
  const teachers = useStaffTeachers();

  const [year, setYear] = useState('');
  const [classId, setClassId] = useState('');
  const [section, setSection] = useState('');
  const sections = useSections(classId);

  const [periods, setPeriods] = useState<Period[]>([]);
  const [slots, setSlots] = useState<TimetableSlot[]>([]);
  const [cell, setCell] = useState<{ weekday: string; period: Period } | null>(null);
  const [form, setForm] = useState({ subject_id: '', teacher_id: '', room_id: '' });
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!user?.school_id) return;
    timetableApi.periods
      .list({ filter: { school_id: user.school_id }, per_page: 100, sort: 'sort_order' })
      .then((r) => setPeriods(r.data));
  }, [user?.school_id]);

  const ready = year && classId;

  const loadGrid = useMemo(
    () => () => {
      if (!ready) {
        setSlots([]);
        return;
      }
      timetableApi.classTimetable
        .grid({ academic_year_id: year, class_id: classId, section_id: section || undefined })
        .then((rows) => setSlots(Array.isArray(rows) ? rows : []));
    },
    [ready, year, classId, section]
  );

  useEffect(() => {
    loadGrid();
  }, [loadGrid]);

  const slotAt = (weekday: string, periodId: number) =>
    slots.find((s) => s.weekday === weekday && s.period_id === periodId);

  const openCell = (weekday: string, period: Period) => {
    const existing = slotAt(weekday, period.id);
    setError(null);
    setForm({
      subject_id: existing ? String(existing.subject_id) : '',
      teacher_id: existing?.teacher_id ? String(existing.teacher_id) : '',
      room_id: existing?.room_id ? String(existing.room_id) : '',
    });
    setCell({ weekday, period });
  };

  const save = async () => {
    if (!cell) return;
    setError(null);
    try {
      await timetableApi.classTimetable.create({
        school_id: user?.school_id,
        academic_year_id: Number(year),
        class_id: Number(classId),
        section_id: section ? Number(section) : null,
        weekday: cell.weekday,
        period_id: cell.period.id,
        subject_id: Number(form.subject_id),
        teacher_id: form.teacher_id ? Number(form.teacher_id) : null,
        room_id: form.room_id ? Number(form.room_id) : null,
      });
      setCell(null);
      loadGrid();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not save (clash detected).');
    }
  };

  const remove = async () => {
    if (!cell) return;
    const existing = slotAt(cell.weekday, cell.period.id);
    if (existing) await timetableApi.classTimetable.archive(existing.id);
    setCell(null);
    loadGrid();
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-table-cells text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Class Timetable</h2>
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
        <div className="w-44">
          <AXSelect
            label="Class"
            value={classId}
            onChange={(e) => {
              setClassId(e.target.value);
              setSection('');
            }}
            options={[{ value: '', label: 'Select…' }, ...classes]}
          />
        </div>
        <div className="w-40">
          <AXSelect
            label="Section"
            value={section}
            onChange={(e) => setSection(e.target.value)}
            options={[{ value: '', label: 'All / none' }, ...sections]}
          />
        </div>
      </div>

      {!ready && (
        <div className="erp-card text-sm text-gray-500">
          Pick an academic year and class to build the timetable.
        </div>
      )}

      {ready && periods.length === 0 && (
        <div className="erp-card text-sm text-gray-500">
          No periods configured. Add periods in Period Management first.
        </div>
      )}

      {ready && periods.length > 0 && (
        <div className="erp-card overflow-x-auto">
          <table className="w-full border-collapse text-sm">
            <thead>
              <tr>
                <th className="border bg-gray-50 p-2 text-left text-gray-600">Period</th>
                {WEEKDAYS.map((d) => (
                  <th
                    key={d}
                    className="border bg-gray-50 p-2 text-center capitalize text-gray-600"
                  >
                    {d.slice(0, 3)}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {periods.map((p) => (
                <tr key={p.id}>
                  <td className="border p-2 font-medium text-gray-700">
                    {p.name}
                    {p.is_break && <AXBadge tone="amber">break</AXBadge>}
                  </td>
                  {WEEKDAYS.map((d) => {
                    const s = slotAt(d, p.id);
                    return (
                      <td key={d} className="border p-1 text-center align-top">
                        <button
                          onClick={() => openCell(d, p)}
                          className={`h-full w-full rounded p-2 text-xs transition ${s ? 'bg-[var(--navy-primary)]/10 hover:bg-[var(--navy-primary)]/20' : 'text-gray-300 hover:bg-gray-50'}`}
                        >
                          {s ? (
                            <>
                              <div className="font-semibold text-[var(--navy-primary)]">
                                {s.subject}
                              </div>
                              <div className="text-gray-500">{s.teacher ?? '—'}</div>
                              {s.room && <div className="text-gray-400">{s.room}</div>}
                            </>
                          ) : (
                            <i className="fas fa-plus" />
                          )}
                        </button>
                      </td>
                    );
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <AXModal
        open={cell !== null}
        title={cell ? `${cell.weekday} · ${cell.period.name}` : ''}
        onClose={() => setCell(null)}
      >
        <div className="space-y-3">
          <AXSelect
            label="Subject"
            value={form.subject_id}
            onChange={(e) => setForm((f) => ({ ...f, subject_id: e.target.value }))}
            options={[{ value: '', label: 'Select…' }, ...subjects]}
          />
          <AXSelect
            label="Teacher"
            value={form.teacher_id}
            onChange={(e) => setForm((f) => ({ ...f, teacher_id: e.target.value }))}
            options={[{ value: '', label: '—' }, ...teachers]}
          />
          <AXSelect
            label="Room"
            value={form.room_id}
            onChange={(e) => setForm((f) => ({ ...f, room_id: e.target.value }))}
            options={[{ value: '', label: '—' }, ...rooms]}
          />
          {error && <AXBadge tone="red">{error}</AXBadge>}
          <div className="flex justify-between">
            <button
              onClick={remove}
              className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-[var(--danger)]"
            >
              <i className="fas fa-trash mr-1" /> Clear
            </button>
            <div className="flex gap-2">
              <button
                onClick={() => setCell(null)}
                className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
              >
                Cancel
              </button>
              <button
                onClick={save}
                disabled={!form.subject_id}
                className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
              >
                Save
              </button>
            </div>
          </div>
        </div>
      </AXModal>
    </div>
  );
}
