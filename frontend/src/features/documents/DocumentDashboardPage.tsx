/* Documents Dashboard — generation/verification widgets + distribution charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { documentsApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface Data {
  widgets: Record<string, number>;
  charts: {
    documents_by_category: Bar[];
    monthly_generation: Bar[];
    verification_trend: Bar[];
    certificate_distribution: Bar[];
  };
}

function Bars({ data }: { data: Bar[] }) {
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
          <span className="w-10 text-right font-medium text-gray-600">{d.count}</span>
        </div>
      ))}
    </div>
  );
}

export function DocumentDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<Data | null>(null);

  useEffect(() => {
    documentsApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as Data))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Documents Generated', icon: 'file-lines', value: w?.documents_generated },
    { label: 'Certificates Issued', icon: 'certificate', value: w?.certificates_issued },
    { label: 'Revoked', icon: 'ban', value: w?.revoked },
    { label: 'Verified', icon: 'circle-check', value: w?.verified_documents },
    { label: 'Rejected', icon: 'circle-xmark', value: w?.rejected_requests },
    { label: 'Templates', icon: 'file-code', value: w?.templates },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-stamp text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Document Dashboard</h2>
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
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Documents by Category
          </h3>
          <Bars data={data?.charts.documents_by_category ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Monthly Generation
          </h3>
          <Bars data={data?.charts.monthly_generation ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Verification Trend
          </h3>
          <Bars data={data?.charts.verification_trend ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Certificate Distribution
          </h3>
          <Bars data={data?.charts.certificate_distribution ?? []} />
        </div>
      </div>
    </div>
  );
}
