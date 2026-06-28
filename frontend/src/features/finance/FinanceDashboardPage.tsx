/* Finance Dashboard — collection / outstanding widgets + collection charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { financeApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: {
    daily_collection: Bar[];
    monthly_collection: Bar[];
    category_collection: Bar[];
    outstanding_trend: Bar[];
  };
}

function Bars({ data, money = true }: { data: Bar[]; money?: boolean }) {
  const max = Math.max(1, ...data.map((d) => d.count));
  if (data.length === 0) return <p className="text-sm text-gray-400">No data yet.</p>;
  return (
    <div className="space-y-2">
      {data.map((d) => (
        <div key={d.label} className="flex items-center gap-2 text-xs">
          <span className="w-24 truncate text-gray-500">{d.label}</span>
          <div className="h-4 flex-1 rounded bg-gray-100">
            <div
              className="h-4 rounded bg-[var(--navy-primary)]"
              style={{ width: `${(d.count / max) * 100}%` }}
            />
          </div>
          <span className="w-20 text-right font-medium text-gray-600">
            {money ? '₹' : ''}
            {d.count}
          </span>
        </div>
      ))}
    </div>
  );
}

export function FinanceDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    financeApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    {
      label: 'Total Collection',
      icon: 'sack-dollar',
      value: w ? `₹${w.total_collection}` : undefined,
    },
    {
      label: 'Outstanding',
      icon: 'hourglass-half',
      value: w ? `₹${w.outstanding_amount}` : undefined,
    },
    {
      label: "Today's Collection",
      icon: 'calendar-day',
      value: w ? `₹${w.todays_collection}` : undefined,
    },
    {
      label: 'Monthly Collection',
      icon: 'calendar',
      value: w ? `₹${w.monthly_collection}` : undefined,
    },
    { label: 'Refunds', icon: 'rotate-left', value: w ? `₹${w.refunds}` : undefined },
    { label: 'Discounts', icon: 'percent', value: w ? `₹${w.discounts}` : undefined },
    { label: 'Scholarships', icon: 'graduation-cap', value: w ? `₹${w.scholarships}` : undefined },
    { label: 'Defaulters', icon: 'user-clock', value: w?.defaulters },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-wallet text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Finance Dashboard</h2>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        {widgets.map((s) => (
          <div key={s.label} className="erp-card flex items-center gap-3">
            <div className="bg-[var(--navy-primary)]/10 flex h-11 w-11 items-center justify-center rounded-lg text-[var(--navy-primary)]">
              <i className={`fas fa-${s.icon}`} />
            </div>
            <div>
              <div className="text-lg font-semibold text-[var(--navy-primary)]">
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
            Daily Collection
          </h3>
          <Bars data={data?.charts.daily_collection ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Monthly Collection
          </h3>
          <Bars data={data?.charts.monthly_collection ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Category Collection
          </h3>
          <Bars data={data?.charts.category_collection ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Outstanding Trend
          </h3>
          <Bars data={data?.charts.outstanding_trend ?? []} />
        </div>
      </div>
    </div>
  );
}
