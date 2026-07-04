/* Integration Dashboard — provider status, success rate, response time, queues. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { integrationsApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: {
    provider_status: Bar[];
    requests_by_provider: Bar[];
    request_trend: Bar[];
  };
}

function Bars({ data }: { data: Bar[] }) {
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
          <span className="w-10 text-right font-medium text-gray-600">{d.count}</span>
        </div>
      ))}
    </div>
  );
}

export function IntegrationDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    integrationsApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Providers', icon: 'plug', value: w?.providers },
    { label: 'Enabled', icon: 'toggle-on', value: w?.enabled_providers },
    { label: 'Failed Requests', icon: 'triangle-exclamation', value: w?.failed_requests },
    { label: 'Retry Queue', icon: 'rotate', value: w?.retry_queue },
    { label: 'Success Rate %', icon: 'gauge-high', value: w?.success_rate },
    { label: 'Avg Response ms', icon: 'stopwatch', value: w?.avg_response_ms },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-diagram-project text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Integrations Dashboard</h2>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
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
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Provider Status</h3>
          <Bars data={data?.charts.provider_status ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Requests by Provider
          </h3>
          <Bars data={data?.charts.requests_by_provider ?? []} />
        </div>
        <div className="erp-card md:col-span-2">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Request Trend</h3>
          <Bars data={data?.charts.request_trend ?? []} />
        </div>
      </div>
    </div>
  );
}
