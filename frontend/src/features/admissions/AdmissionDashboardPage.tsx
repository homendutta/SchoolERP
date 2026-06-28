/* Admission Dashboard — headline widgets + charts (monthly admissions, enquiry
 * sources, status distribution). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { admissionsApi, type DashboardData } from './api';

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

export function AdmissionDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    admissionsApi
      .dashboard(user?.school_id ?? undefined)
      .then(setData)
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: "Today's Enquiries", icon: 'comments', value: w?.today_enquiries },
    { label: 'Pending Applications', icon: 'hourglass-half', value: w?.pending_applications },
    { label: 'Approved', icon: 'circle-check', value: w?.approved },
    { label: 'Rejected', icon: 'circle-xmark', value: w?.rejected },
    { label: 'This Month Admissions', icon: 'user-graduate', value: w?.month_admissions },
    { label: 'Conversion Rate', icon: 'percent', value: w ? `${w.conversion_rate}%` : undefined },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-chart-pie text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Admission Dashboard</h2>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-3">
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
          <Bars
            data={(data?.charts.monthly_admissions ?? []).map((m) => ({
              label: m.month,
              count: m.count,
            }))}
          />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Enquiry Sources</h3>
          <Bars data={data?.charts.enquiry_sources ?? []} />
        </div>
        <div className="erp-card md:col-span-2">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Status Distribution
          </h3>
          <Bars data={data?.charts.status_distribution ?? []} />
        </div>
      </div>
    </div>
  );
}
