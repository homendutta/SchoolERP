/* Read-only weekday × period grid for derived (teacher / room) timetables. */
import { WEEKDAYS, type TimetableSlot } from './api';

export function DerivedGrid({
  slots,
  show,
}: {
  slots: TimetableSlot[];
  show: 'teacher' | 'room' | 'class';
}) {
  // Distinct periods present in the data, kept in first-seen order.
  const periods: Array<{ id: number; name: string }> = [];
  for (const s of slots) {
    if (!periods.find((p) => p.id === s.period_id))
      periods.push({ id: s.period_id, name: s.period ?? `Period ${s.period_id}` });
  }

  const at = (weekday: string, periodId: number) =>
    slots.find((s) => s.weekday === weekday && s.period_id === periodId);

  if (slots.length === 0)
    return <div className="erp-card text-sm text-gray-500">No scheduled periods.</div>;

  const secondary = (s: TimetableSlot) =>
    show === 'teacher'
      ? `${s.class ?? ''}${s.section ? ' · ' + s.section : ''}`
      : show === 'room'
        ? (s.teacher ?? '—')
        : (s.teacher ?? '—');

  return (
    <div className="erp-card overflow-x-auto">
      <table className="w-full border-collapse text-sm">
        <thead>
          <tr>
            <th className="border bg-gray-50 p-2 text-left text-gray-600">Period</th>
            {WEEKDAYS.map((d) => (
              <th key={d} className="border bg-gray-50 p-2 text-center capitalize text-gray-600">
                {d.slice(0, 3)}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {periods.map((p) => (
            <tr key={p.id}>
              <td className="border p-2 font-medium text-gray-700">{p.name}</td>
              {WEEKDAYS.map((d) => {
                const s = at(d, p.id);
                return (
                  <td key={d} className="border p-2 text-center text-xs">
                    {s ? (
                      <>
                        <div className="font-semibold text-[var(--navy-primary)]">{s.subject}</div>
                        <div className="text-gray-500">{secondary(s)}</div>
                      </>
                    ) : (
                      <span className="text-gray-300">—</span>
                    )}
                  </td>
                );
              })}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
