/* Transport Dashboard — fleet/route/trip widgets + usage & capacity charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { transportApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: {
    route_usage: Bar[];
    vehicle_utilization: Bar[];
    student_distribution: Bar[];
    capacity_usage: Array<{ label: string; assigned: number; capacity: number }>;
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
          <span className="w-8 text-right font-medium text-gray-600">{d.count}</span>
        </div>
      ))}
    </div>
  );
}

export function TransportDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    transportApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Vehicles', icon: 'bus', value: w?.vehicles },
    { label: 'Routes', icon: 'route', value: w?.routes },
    { label: 'Trips', icon: 'clock', value: w?.trips },
    { label: 'Assigned Students', icon: 'user-graduate', value: w?.assigned_students },
    { label: 'Drivers', icon: 'id-card-clip', value: w?.drivers },
    { label: 'Over Capacity', icon: 'triangle-exclamation', value: w?.over_capacity },
    { label: 'Maintenance Due', icon: 'screwdriver-wrench', value: w?.maintenance_due },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-van-shuttle text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Transport Dashboard</h2>
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

      <div className="grid gap-4 md:grid-cols-2">
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Route Usage</h3>
          <Bars data={data?.charts.route_usage ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Vehicle Utilization
          </h3>
          <Bars data={data?.charts.vehicle_utilization ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Student Distribution
          </h3>
          <Bars data={data?.charts.student_distribution ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Capacity Usage</h3>
          <div className="space-y-2">
            {(data?.charts.capacity_usage ?? []).map((c) => (
              <div key={c.label} className="flex items-center gap-2 text-xs">
                <span className="w-28 truncate text-gray-500">{c.label}</span>
                <div className="h-4 flex-1 rounded bg-gray-100">
                  <div
                    className={`h-4 rounded ${c.capacity > 0 && c.assigned > c.capacity ? 'bg-[var(--danger)]' : 'bg-[var(--navy-primary)]'}`}
                    style={{
                      width: `${c.capacity > 0 ? Math.min(100, (c.assigned / c.capacity) * 100) : 0}%`,
                    }}
                  />
                </div>
                <span className="w-12 text-right font-medium text-gray-600">
                  {c.assigned}/{c.capacity}
                </span>
              </div>
            ))}
            {(data?.charts.capacity_usage ?? []).length === 0 && (
              <p className="text-sm text-gray-400">No data yet.</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
