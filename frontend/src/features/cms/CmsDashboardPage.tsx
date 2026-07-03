/* CMS Dashboard — content counts + publication/enquiry trends & distribution. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { cmsApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: {
    publication_trend: Bar[];
    enquiry_trend: Bar[];
    content_distribution: Bar[];
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

export function CmsDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    cmsApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Pages', icon: 'file-lines', value: w?.pages },
    { label: 'News', icon: 'newspaper', value: w?.news },
    { label: 'Events', icon: 'calendar-days', value: w?.events },
    { label: 'Notices', icon: 'bullhorn', value: w?.notices },
    { label: 'Gallery', icon: 'images', value: w?.gallery },
    { label: 'Downloads', icon: 'file-arrow-down', value: w?.downloads },
    { label: 'Enquiries', icon: 'inbox', value: w?.enquiries },
    { label: 'Draft Pages', icon: 'pen-ruler', value: w?.draft_pages },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-globe text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">
          Website / CMS Dashboard
        </h2>
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
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Publication Trend
          </h3>
          <Bars data={data?.charts.publication_trend ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Enquiry Trend</h3>
          <Bars data={data?.charts.enquiry_trend ?? []} />
        </div>
        <div className="erp-card md:col-span-2">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Content Distribution
          </h3>
          <Bars data={data?.charts.content_distribution ?? []} />
        </div>
      </div>
    </div>
  );
}
