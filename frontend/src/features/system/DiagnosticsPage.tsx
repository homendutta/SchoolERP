/* System Diagnostics — versions, drivers, disk usage and PHP extensions. */
import { useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { systemApi } from './api';

interface Diag {
  php_version: string;
  laravel_version: string;
  app_env: string;
  app_debug: boolean;
  database: { driver: string; version: string };
  cache_driver: string;
  queue_driver: string;
  storage_driver: string;
  mail_driver: string;
  disk: { free: number; total: number; used_percent: number };
  php_extensions: Record<string, boolean>;
}

const gb = (n: number) => (n > 0 ? `${(n / 1024 ** 3).toFixed(1)} GB` : '—');

export function DiagnosticsPage() {
  const [d, setD] = useState<Diag | null>(null);

  useEffect(() => {
    systemApi
      .diagnostics()
      .then((x) => setD(x as unknown as Diag))
      .catch(() => undefined);
  }, []);

  const rows: Array<[string, string]> = d
    ? [
        ['PHP', d.php_version],
        ['Laravel', d.laravel_version],
        ['Environment', d.app_env],
        ['Debug', d.app_debug ? 'on' : 'off'],
        ['Database', `${d.database.driver} ${d.database.version}`],
        ['Cache driver', d.cache_driver],
        ['Queue driver', d.queue_driver],
        ['Storage driver', d.storage_driver],
        ['Mail driver', d.mail_driver],
        [
          'Disk',
          `${gb(d.disk.total - d.disk.free)} / ${gb(d.disk.total)} (${d.disk.used_percent}%)`,
        ],
      ]
    : [];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-microchip text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">System Diagnostics</h2>
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">Environment</h3>
          <table className="w-full text-sm">
            <tbody>
              {rows.map(([k, v]) => (
                <tr key={k} className="border-b border-gray-100">
                  <td className="py-1.5 text-gray-500">{k}</td>
                  <td className="py-1.5 text-right font-medium">{v}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">PHP Extensions</h3>
          <div className="flex flex-wrap gap-2">
            {Object.entries(d?.php_extensions ?? {}).map(([ext, on]) => (
              <AXBadge key={ext} tone={on ? 'green' : 'red'}>
                {ext}
              </AXBadge>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
