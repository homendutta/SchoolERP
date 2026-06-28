/* Examination Dashboard — widgets + pass% / grade / subject / class charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { examApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: {
    pass_percentage: Bar[];
    grade_distribution: Bar[];
    subject_performance: Bar[];
    class_performance: Bar[];
  };
}

function Bars({ data, suffix = '' }: { data: Bar[]; suffix?: string }) {
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
          <span className="w-12 text-right font-medium text-gray-600">
            {d.count}
            {suffix}
          </span>
        </div>
      ))}
    </div>
  );
}

export function ExamDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    examApi
      .dashboard({ school_id: user?.school_id })
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Active Exams', icon: 'play', value: w?.active_exams },
    { label: 'Scheduled', icon: 'calendar-check', value: w?.scheduled_exams },
    { label: 'Completed', icon: 'circle-check', value: w?.completed_exams },
    { label: 'Pending Marks', icon: 'pen', value: w?.pending_marks_entry },
    { label: 'Published', icon: 'bullhorn', value: w?.published_results },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-graduation-cap text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Examination Dashboard</h2>
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
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Pass / Fail</h3>
          <Bars data={data?.charts.pass_percentage ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Grade Distribution
          </h3>
          <Bars data={data?.charts.grade_distribution ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Subject Performance (avg %)
          </h3>
          <Bars data={data?.charts.subject_performance ?? []} suffix="%" />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Class Performance (avg %)
          </h3>
          <Bars data={data?.charts.class_performance ?? []} suffix="%" />
        </div>
      </div>
    </div>
  );
}
