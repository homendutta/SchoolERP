/* Production Dashboard — overall health, component status, ops widgets, config
 * readiness, and backup manifests. The single operator view of the running ERP. */
import { useCallback, useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { systemApi, type ConfigCheck, type HealthComponent } from './api';

const STATUS_TONE: Record<string, 'green' | 'amber' | 'red' | 'gray'> = {
  ok: 'green',
  warn: 'amber',
  down: 'red',
};

interface Dashboard {
  health: { score: number; status: string; components: HealthComponent[] };
  widgets: Record<string, number>;
  cache: { driver: string };
  queue: { driver: string };
}

export function ProductionDashboardPage() {
  const [data, setData] = useState<Dashboard | null>(null);
  const [config, setConfig] = useState<{ ready: boolean; checks: ConfigCheck[] } | null>(null);
  const [backups, setBackups] = useState<Record<string, unknown>[]>([]);
  const [busy, setBusy] = useState(false);

  const load = useCallback(() => {
    systemApi
      .dashboard()
      .then((d) => setData(d as unknown as Dashboard))
      .catch(() => undefined);
    systemApi
      .config()
      .then(setConfig)
      .catch(() => undefined);
    systemApi
      .backups()
      .then((b) => setBackups(Array.isArray(b) ? b : []))
      .catch(() => undefined);
  }, []);
  useEffect(() => load(), [load]);

  const runBackup = async (type: string) => {
    setBusy(true);
    try {
      await systemApi.createBackup({ type });
      load();
    } finally {
      setBusy(false);
    }
  };

  const w = data?.widgets;
  const widgets = [
    { label: 'Overall Health', icon: 'heart-pulse', value: w?.overall_health },
    { label: 'Queue Pending', icon: 'list-check', value: w?.queue_pending },
    { label: 'Failed Jobs', icon: 'triangle-exclamation', value: w?.failed_jobs },
    { label: 'Scheduled Jobs', icon: 'clock', value: w?.scheduled_jobs },
    { label: 'Storage Used %', icon: 'hard-drive', value: w?.storage_used_percent },
    { label: 'Active Sessions', icon: 'user-check', value: w?.active_sessions },
    { label: 'Integrations', icon: 'plug', value: w?.integration_providers },
    { label: 'API Avg ms', icon: 'stopwatch', value: w?.api_avg_ms },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <i className="fas fa-server text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Production Dashboard</h2>
        </div>
        {data && (
          <AXBadge tone={STATUS_TONE[data.health.status] ?? 'gray'}>
            Health {data.health.score}/100 · {data.health.status}
          </AXBadge>
        )}
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-8">
        {widgets.map((s) => (
          <div key={s.label} className="erp-card flex flex-col gap-1">
            <i className={`fas fa-${s.icon} text-[var(--navy-primary)]`} />
            <div className="text-xl font-semibold text-[var(--navy-primary)]">{s.value ?? '—'}</div>
            <div className="text-xs uppercase tracking-wide text-gray-500">{s.label}</div>
          </div>
        ))}
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">
            Component Health
          </h3>
          <ul className="space-y-2">
            {(data?.health.components ?? []).map((c) => (
              <li key={c.name} className="flex items-center justify-between gap-2 text-sm">
                <span className="font-medium capitalize">{c.name}</span>
                <span className="flex items-center gap-2">
                  <span className="text-xs text-gray-500">{c.detail}</span>
                  <AXBadge tone={STATUS_TONE[c.status] ?? 'gray'}>{c.status}</AXBadge>
                </span>
              </li>
            ))}
          </ul>
        </div>

        <div className="erp-card">
          <div className="mb-3 flex items-center justify-between">
            <h3 className="text-sm font-semibold text-[var(--navy-primary)]">
              Production Readiness
            </h3>
            {config && (
              <AXBadge tone={config.ready ? 'green' : 'red'}>
                {config.ready ? 'Ready' : 'Not ready'}
              </AXBadge>
            )}
          </div>
          <ul className="space-y-1.5">
            {(config?.checks ?? []).map((c) => (
              <li key={c.check} className="flex items-center justify-between gap-2 text-sm">
                <span className="font-medium">{c.check.replace(/_/g, ' ')}</span>
                <span className="flex items-center gap-2">
                  <span className="text-xs text-gray-500">{c.detail}</span>
                  <AXBadge tone={c.ok ? 'green' : c.severity === 'critical' ? 'red' : 'amber'}>
                    {c.ok ? 'ok' : c.severity}
                  </AXBadge>
                </span>
              </li>
            ))}
          </ul>
        </div>
      </div>

      <div className="erp-card">
        <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
          <h3 className="text-sm font-semibold text-[var(--navy-primary)]">Backups</h3>
          <div className="flex gap-2">
            {['database', 'media', 'config', 'full'].map((t) => (
              <button
                key={t}
                onClick={() => runBackup(t)}
                disabled={busy}
                className="rounded-md border border-[var(--navy-primary)] px-3 py-1.5 text-xs font-semibold text-[var(--navy-primary)] disabled:opacity-60"
              >
                Backup {t}
              </button>
            ))}
          </div>
        </div>
        <ul className="space-y-1.5">
          {backups.slice(0, 8).map((b) => (
            <li key={String(b.id)} className="flex items-center justify-between gap-2 text-sm">
              <span className="font-medium">
                {String(b.type)} · {String(b.path ?? '')}
              </span>
              <span className="flex items-center gap-2">
                <AXBadge tone={String(b.status) === 'verified' ? 'green' : 'navy'}>
                  {String(b.status)}
                </AXBadge>
                <button
                  onClick={() => systemApi.verifyBackup(Number(b.id)).then(load)}
                  className="text-xs font-semibold text-[var(--navy-accent)]"
                >
                  Verify
                </button>
              </span>
            </li>
          ))}
          {backups.length === 0 && (
            <li className="text-xs text-gray-400">No backups recorded yet.</li>
          )}
        </ul>
      </div>
    </div>
  );
}
