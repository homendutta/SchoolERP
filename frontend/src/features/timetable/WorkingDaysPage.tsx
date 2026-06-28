/* Working Days — each school chooses its own working days (never hardcoded). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge } from '@ui/ax';
import { timetableApi, WEEKDAYS } from './api';

export function WorkingDaysPage() {
  const { user } = useAuth();
  const [working, setWorking] = useState<Record<string, boolean>>(
    Object.fromEntries(WEEKDAYS.map((d) => [d, d !== 'sunday']))
  );
  const [saved, setSaved] = useState(false);
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (!user?.school_id) return;
    timetableApi.workingDays
      .list({ filter: { school_id: user.school_id }, per_page: 7 })
      .then((r) => {
        if (r.data.length) {
          setWorking(
            Object.fromEntries(
              WEEKDAYS.map((d) => [d, r.data.find((w) => w.weekday === d)?.is_working ?? false])
            )
          );
        }
      });
  }, [user?.school_id]);

  const toggle = (day: string) => {
    setSaved(false);
    setWorking((w) => ({ ...w, [day]: !w[day] }));
  };

  const save = async () => {
    if (!user?.school_id) return;
    setBusy(true);
    setSaved(false);
    try {
      await timetableApi.workingDays.sync(
        user.school_id,
        WEEKDAYS.map((d) => ({ weekday: d, is_working: working[d] }))
      );
      setSaved(true);
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-calendar-week text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Working Days</h2>
      </div>

      <div className="erp-card space-y-3">
        <p className="text-sm text-gray-500">
          Toggle which weekdays are working days for your school.
        </p>
        <div className="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
          {WEEKDAYS.map((d) => (
            <button
              key={d}
              onClick={() => toggle(d)}
              className={`rounded-md border px-3 py-3 text-sm font-semibold capitalize transition ${
                working[d]
                  ? 'border-[var(--navy-primary)] bg-[var(--navy-primary)] text-white'
                  : 'border-gray-300 bg-white text-gray-500'
              }`}
            >
              {d}
            </button>
          ))}
        </div>
        <div className="flex items-center gap-3">
          <button
            onClick={save}
            disabled={busy}
            className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            <i className="fas fa-save mr-1" /> Save
          </button>
          {saved && <AXBadge tone="green">Saved</AXBadge>}
        </div>
      </div>
    </div>
  );
}
