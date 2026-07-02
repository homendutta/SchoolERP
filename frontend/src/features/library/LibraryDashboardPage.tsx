/* Library Dashboard — copy/circulation widgets + trend/popularity charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: {
    borrowing_trend: Bar[];
    popular_books: Array<{ book_id: number; count: number }>;
    category_distribution: Array<{ category_id: number | null; count: number }>;
    overdue_trend: Bar[];
  };
}

import { libraryApi } from './api';

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

export function LibraryDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    libraryApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Total Titles', icon: 'book', value: w?.total_titles },
    { label: 'Total Copies', icon: 'clone', value: w?.total_copies },
    { label: 'Borrowed', icon: 'book-open-reader', value: w?.borrowed },
    { label: 'Available', icon: 'circle-check', value: w?.available },
    { label: 'Reserved', icon: 'bookmark', value: w?.reserved },
    { label: 'Overdue', icon: 'triangle-exclamation', value: w?.overdue },
    { label: 'Lost', icon: 'ban', value: w?.lost },
    { label: 'Damaged', icon: 'bandage', value: w?.damaged },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-book-bookmark text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Library Dashboard</h2>
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
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Borrowing Trend</h3>
          <Bars data={data?.charts.borrowing_trend ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Popular Books</h3>
          <Bars
            data={(data?.charts.popular_books ?? []).map((b) => ({
              label: `Book #${b.book_id}`,
              count: b.count,
            }))}
          />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Category Distribution
          </h3>
          <Bars
            data={(data?.charts.category_distribution ?? []).map((c) => ({
              label: c.category_id ? `Cat #${c.category_id}` : 'Uncategorized',
              count: c.count,
            }))}
          />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Overdue Trend</h3>
          <Bars data={data?.charts.overdue_trend ?? []} />
        </div>
      </div>
    </div>
  );
}
