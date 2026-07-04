/* Reports Dashboard — export/queue widgets + most-used & trend charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { reportsApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: { most_used_reports: Bar[]; export_trend: Bar[]; format_distribution: Bar[] };
}

function Bars({ data }: { data: Bar[] }) {
  const max = Math.max(1, ...data.map((d) => d.count));
  if (data.length === 0) return <p className="text-sm text-gray-400">No data yet.</p>;
  return (
    <div className="space-y-2">
      {data.map((d) => (
        <div key={d.label} className="flex items-center gap-2 text-xs">
          <span className="w-40 truncate text-gray-500">{d.label}</span>
          <div className="h-4 flex-1 rounded bg-gray-100">
            <div
              className="h-4 rounded bg-[var(--navy-primary)]"
              style={{ width: `${(d.count / max) * 100}%` }}
            />
          </div>
          <span className="w-10 text-right font-medium text-gray-600">{d.count}</span>
        </div>
      ))}
    </div>
  );
}

export function ReportsDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    reportsApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Scheduled Reports', icon: 'clock', value: w?.scheduled_reports },
    { label: 'Recent Exports (7d)', icon: 'file-export', value: w?.recent_exports },
    { label: 'Export Queue', icon: 'hourglass-half', value: w?.export_queue },
    { label: 'Failed Reports', icon: 'triangle-exclamation', value: w?.failed_reports },
    { label: 'Total Exports', icon: 'chart-column', value: w?.total_exports },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-chart-line text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Reports Dashboard</h2>
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

      <div className="grid gap-4 md:grid-cols-2">
        <div className="erp-card md:col-span-2">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Most Used Reports
          </h3>
          <Bars data={data?.charts.most_used_reports ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Export Trend</h3>
          <Bars data={data?.charts.export_trend ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Format Distribution
          </h3>
          <Bars data={data?.charts.format_distribution ?? []} />
        </div>
      </div>
    </div>
  );
}
