/* Timetable Dashboard — widgets + teacher workload / room utilization / subject
 * distribution charts. All figures are derived from the master class timetable. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXSelect } from '@ui/ax';
import { useYears } from '@features/academic/useReference';
import { timetableApi, type TimetableDashboardData } from './api';

function Bars({ data }: { data: Array<{ label: string; count: number }> }) {
  const max = Math.max(1, ...data.map((d) => d.count));
  if (data.length === 0) return <p className="text-sm text-gray-400">No data yet.</p>;
  return (
    <div className="space-y-2">
      {data.map((d) => (
        <div key={d.label} className="flex items-center gap-2 text-xs">
          <span className="w-28 truncate text-gray-500">{d.label}</span>
          <div className="h-4 flex-1 rounded bg-gray-100">
            <div
              className="h-4 rounded bg-[var(--navy-primary)]"
              style={{ width: `${(d.count / max) * 100}%` }}
            />
          </div>
          <span className="w-8 text-right font-medium text-gray-600">{d.count}</span>
        </div>
      ))}
    </div>
  );
}

export function TimetableDashboardPage() {
  const { user } = useAuth();
  const years = useYears();
  const [year, setYear] = useState('');
  const [data, setData] = useState<TimetableDashboardData | null>(null);

  useEffect(() => {
    if (years.length && !year) setYear(years[0].value);
  }, [years, year]);

  useEffect(() => {
    if (!year) return;
    timetableApi
      .dashboard({ school_id: user?.school_id, academic_year_id: year })
      .then(setData)
      .catch(() => undefined);
  }, [year, user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Total Timetables', icon: 'table-cells', value: w?.total_timetables },
    { label: 'Teacher Load', icon: 'chalkboard-user', value: w?.teacher_load },
    { label: 'Room Usage', icon: 'door-open', value: w?.room_usage },
    { label: 'Daily Classes', icon: 'calendar-day', value: w?.daily_classes },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-calendar-alt text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Timetable Dashboard</h2>
        </div>
        <div className="w-44">
          <AXSelect
            value={year}
            onChange={(e) => setYear(e.target.value)}
            options={[{ value: '', label: 'Year…' }, ...years]}
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        {widgets.map((s) => (
          <div key={s.label} className="erp-card flex items-center gap-3">
            <div className="bg-[var(--navy-primary)]/10 flex h-11 w-11 items-center justify-center rounded-lg text-[var(--navy-primary)]">
              <i className={`fas fa-${s.icon}`} />
            </div>
            <div>
              <div className="text-xl font-semibold text-[var(--navy-primary)]">
                {s.value ?? '—'}
              </div>
              <div className="text-xs uppercase tracking-wide text-gray-500">{s.label}</div>
            </div>
          </div>
        ))}
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Teacher Workload (periods/week)
          </h3>
          <Bars
            data={(data?.charts.teacher_workload ?? []).map((t) => ({
              label: `Staff #${t.teacher_id}`,
              count: t.periods_per_week,
            }))}
          />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Room Utilization
          </h3>
          <Bars
            data={(data?.charts.room_utilization ?? []).map((r) => ({
              label: `Room #${r.room_id}`,
              count: r.count,
            }))}
          />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Subject Distribution
          </h3>
          <Bars
            data={(data?.charts.subject_distribution ?? []).map((s) => ({
              label: `Subject #${s.subject_id}`,
              count: s.count,
            }))}
          />
        </div>
      </div>
    </div>
  );
}
