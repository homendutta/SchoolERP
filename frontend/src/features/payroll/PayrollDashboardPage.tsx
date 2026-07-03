/* Payroll Dashboard — cost/net/deduction widgets + trend & distribution charts. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { payrollApi } from './api';

interface Bar {
  label: string;
  count: number;
}
interface DashboardData {
  widgets: Record<string, number>;
  charts: {
    payroll_trend: Bar[];
    department_cost: Bar[];
    salary_distribution: Bar[];
    deduction_breakdown: Bar[];
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
          <span className="w-16 text-right font-medium text-gray-600">{d.count}</span>
        </div>
      ))}
    </div>
  );
}

export function PayrollDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<DashboardData | null>(null);

  useEffect(() => {
    payrollApi
      .dashboard(user?.school_id ?? undefined)
      .then((d) => setData(d as unknown as DashboardData))
      .catch(() => undefined);
  }, [user?.school_id]);

  const w = data?.widgets;
  const widgets = [
    { label: 'Employees Processed', icon: 'users', value: w?.employees_processed },
    { label: 'Pending Payroll', icon: 'hourglass-half', value: w?.pending_payroll },
    { label: 'Payroll Cost', icon: 'sack-dollar', value: w?.payroll_cost },
    { label: 'Net Salary', icon: 'money-bill-wave', value: w?.net_salary },
    { label: 'Deductions', icon: 'scissors', value: w?.deductions },
    { label: 'Employer Contributions', icon: 'building-columns', value: w?.employer_contributions },
    { label: 'Pending Loans', icon: 'hand-holding-dollar', value: w?.pending_loans },
    { label: 'Payroll Runs', icon: 'list-check', value: w?.payroll_runs },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-money-check-dollar text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Payroll Dashboard</h2>
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
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Payroll Trend</h3>
          <Bars data={data?.charts.payroll_trend ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Department Cost</h3>
          <Bars data={data?.charts.department_cost ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Salary Distribution
          </h3>
          <Bars data={data?.charts.salary_distribution ?? []} />
        </div>
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Deduction Breakdown
          </h3>
          <Bars data={data?.charts.deduction_breakdown ?? []} />
        </div>
      </div>
    </div>
  );
}
