/* Inventory Dashboard — asset/consumable widgets + distribution & trend charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { inventoryApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: {
    category_distribution: Bar[];
    asset_allocation: Bar[];
    maintenance_trend: Bar[];
    stock_consumption: Bar[];
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

export function InventoryDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    inventoryApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Total Assets', icon: 'boxes-stacked', value: w?.total_assets },
    { label: 'Active Assets', icon: 'circle-check', value: w?.active_assets },
    { label: 'Assigned Assets', icon: 'user-tag', value: w?.assigned_assets },
    { label: 'Consumables', icon: 'box', value: w?.consumables },
    { label: 'Low Stock', icon: 'triangle-exclamation', value: w?.low_stock },
    { label: 'Warranty Expiring', icon: 'shield-halved', value: w?.warranty_expiring },
    { label: 'Maintenance Due', icon: 'screwdriver-wrench', value: w?.maintenance_due },
    { label: 'Verification Pending', icon: 'clipboard-check', value: w?.verification_pending },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-warehouse text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Inventory Dashboard</h2>
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
            Category Distribution
          </h3>
          <Bars data={data?.charts.category_distribution ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Asset Allocation
          </h3>
          <Bars data={data?.charts.asset_allocation ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Maintenance Trend
          </h3>
          <Bars data={data?.charts.maintenance_trend ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Stock Consumption
          </h3>
          <Bars data={data?.charts.stock_consumption ?? []} />
        </div>
      </div>
    </div>
  );
}
