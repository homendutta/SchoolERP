/* Hostel Dashboard — structure/occupancy widgets + occupancy & trend charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { hostelApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: {
    occupancy: Bar[];
    hostel_distribution: Bar[];
    maintenance_trend: Bar[];
    student_allocation: Bar[];
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

export function HostelDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    hostelApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Hostels', icon: 'building', value: w?.hostels },
    { label: 'Buildings', icon: 'building-flag', value: w?.buildings },
    { label: 'Rooms', icon: 'door-closed', value: w?.rooms },
    { label: 'Beds', icon: 'bed', value: w?.beds },
    { label: 'Occupied', icon: 'user-check', value: w?.occupied },
    { label: 'Available', icon: 'circle-check', value: w?.available },
    { label: 'Visitors Today', icon: 'user-clock', value: w?.visitors_today },
    { label: 'Pending Maintenance', icon: 'screwdriver-wrench', value: w?.pending_maintenance },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-hotel text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Hostel Dashboard</h2>
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
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Occupancy</h3>
          <Bars data={data?.charts.occupancy ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Hostel Distribution
          </h3>
          <Bars data={data?.charts.hostel_distribution ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Maintenance Trend
          </h3>
          <Bars data={data?.charts.maintenance_trend ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Student Allocation
          </h3>
          <Bars data={data?.charts.student_allocation ?? []} />
        </div>
      </div>
    </div>
  );
}
