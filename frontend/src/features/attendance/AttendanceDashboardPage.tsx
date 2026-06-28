/* Attendance Dashboard — student/staff toggle, widgets + daily/weekly/monthly. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXSelect } from '@ui/ax';
import { attendanceApi, type AttendanceDashboardData } from './api';

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

export function AttendanceDashboardPage() {
  const { user } = useAuth();
  const [type, setType] = useState<'student' | 'staff'>('student');
  const [data, setData] = useState<AttendanceDashboardData | null>(null);

  useEffect(() => {
    attendanceApi
      .dashboard(type, user?.school_id ?? undefined)
      .then(setData)
      .catch(() => undefined);
  }, [type, user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Present', icon: 'circle-check', value: w?.present },
    { label: 'Absent', icon: 'circle-xmark', value: w?.absent },
    { label: 'Late', icon: 'clock', value: w?.late },
    { label: 'Leave', icon: 'plane-departure', value: w?.leave },
    {
      label: 'Attendance %',
      icon: 'percent',
      value: w ? `${w.attendance_percentage}%` : undefined,
    },
  ];
  const series = (s: Array<{ period: string; count: number }> = []) =>
    s.map((x) => ({ label: x.period, count: x.count }));

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-clipboard-check text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Attendance Dashboard</h2>
        </div>
        <div className="w-44">
          <AXSelect
            value={type}
            onChange={(e) => setType(e.target.value as 'student' | 'staff')}
            options={[
              { value: 'student', label: 'Students' },
              { value: 'staff', label: 'Staff' },
            ]}
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-5">
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
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Daily (present)</h3>
          <Bars data={series(data?.charts.daily)} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Weekly</h3>
          <Bars data={series(data?.charts.weekly)} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Monthly</h3>
          <Bars data={series(data?.charts.monthly)} />
        </div>
      </div>
    </div>
  );
}
