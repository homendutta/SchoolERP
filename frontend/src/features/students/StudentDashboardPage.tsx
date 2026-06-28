/* Student Dashboard — lifecycle widgets + charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { studentsApi, type StudentDashboardData } from './api';

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

export function StudentDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<StudentDashboardData | null>(null);

  useEffect(() => {
    studentsApi
      .dashboard(user?.school_id ?? undefined)
      .then(setData)
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Total Students', icon: 'users', value: w?.total_students },
    { label: 'Active', icon: 'user-check', value: w?.active },
    { label: 'Promoted', icon: 'arrow-up-right-dots', value: w?.promoted },
    { label: 'Transfers', icon: 'right-left', value: w?.transfers },
    { label: 'Withdrawn', icon: 'user-xmark', value: w?.withdrawn },
    { label: 'Graduated', icon: 'user-graduate', value: w?.graduated },
    { label: 'New Admissions', icon: 'user-plus', value: w?.new_admissions },
  ];
  const months = (s: Array<{ month: string; count: number }> = []) =>
    s.map((m) => ({ label: m.month, count: m.count }));

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-chart-line text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Student Dashboard</h2>
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
            Monthly Admissions
          </h3>
          <Bars data={months(data?.charts.monthly_admissions)} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Promotions</h3>
          <Bars data={months(data?.charts.promotions)} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Withdrawals</h3>
          <Bars data={months(data?.charts.withdrawals)} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Gender Distribution
          </h3>
          <Bars data={data?.charts.gender_distribution ?? []} />
        </div>
        <div className="erp-card md:col-span-2">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Class Distribution
          </h3>
          <Bars data={data?.charts.class_distribution ?? []} />
        </div>
      </div>
    </div>
  );
}
