/* Staff Dashboard — headcount widgets + distribution/joining charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { staffApi, type StaffDashboardData } from './api';

function Bars({ data }: { data: Array<{ label: string; count: number }> }) {
  const max = Math.max(1, ...data.map((d) => d.count));
  if (data.length === 0) return <p className="text-sm text-gray-400">No data yet.</p>;
  return (
    <div className="space-y-2">
      {data.map((d) => (
        <div key={d.label} className="flex items-center gap-2 text-xs">
          <span className="w-32 truncate text-gray-500">{d.label}</span>
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

export function StaffDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<StaffDashboardData | null>(null);

  useEffect(() => {
    staffApi
      .dashboard(user?.school_id ?? undefined)
      .then(setData)
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Total Staff', icon: 'users', value: w?.total_staff },
    { label: 'Teaching', icon: 'chalkboard-user', value: w?.teaching_staff },
    { label: 'Non-Teaching', icon: 'user-gear', value: w?.non_teaching_staff },
    { label: 'Active', icon: 'user-check', value: w?.active },
    { label: 'On Leave', icon: 'umbrella-beach', value: w?.on_leave },
    { label: 'New Joinees', icon: 'user-plus', value: w?.new_joinees },
    { label: 'Resigned', icon: 'user-xmark', value: w?.resigned },
  ];
  const months = (s: Array<{ month: string; count: number }> = []) =>
    s.map((m) => ({ label: m.month, count: m.count }));

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-id-card-clip text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Staff Dashboard</h2>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        {widgets.map((s) => (
          <div key={s.label} className="erp-card flex items-center gap-4">
            <div className="bg-[var(--navy-primary)]/10 flex h-12 w-12 items-center justify-center rounded-lg text-[var(--navy-primary)]">
              <i className={`fas fa-${s.icon} text-lg`} />
            </div>
            <div>
              <div className="text-2xl font-semibold text-[var(--navy-primary)]">
                {s.value ?? '—'}
              </div>
              <div className="text-xs uppercase tracking-wide text-gray-500">{s.label}</div>
            </div>
          </div>
        ))}
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Department Distribution
          </h3>
          <Bars data={data?.charts.department_distribution ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Designation Distribution
          </h3>
          <Bars data={data?.charts.designation_distribution ?? []} />
        </div>
        <div className="erp-card md:col-span-2">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Monthly Joining Trend
          </h3>
          <Bars data={months(data?.charts.monthly_joining)} />
        </div>
      </div>
    </div>
  );
}
