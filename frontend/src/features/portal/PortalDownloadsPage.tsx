/* Portal Downloads — school documents from the Website CMS module. */
import { useEffect, useState } from 'react';
import { portalApi } from './api';
import { usePortal } from './usePortal';
import { PortalShell } from './PortalShell';

export function PortalDownloadsPage() {
  const { context, error } = usePortal();
  const [rows, setRows] = useState<Array<Record<string, unknown>>>([]);

  useEffect(() => {
    portalApi
      .downloads()
      .then(setRows)
      .catch(() => setRows([]));
  }, []);

  return (
    <PortalShell
      title="Downloads"
      icon="file-arrow-down"
      context={context}
      studentId={null}
      requiresStudent={false}
      error={error}
    >
      <div className="erp-card">
        {rows.length === 0 ? (
          <p className="text-sm text-gray-400">No documents available.</p>
        ) : (
          <ul className="divide-y">
            {rows.map((d) => (
              <li
                key={String(d.id)}
                className="flex flex-wrap items-center justify-between gap-2 py-2"
              >
                <span className="font-medium">{String(d.title)}</span>
                {typeof d.file === 'string' && (
                  <a
                    href={d.file}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="rounded-md border border-[var(--navy-primary)] px-3 py-1 text-xs font-semibold text-[var(--navy-primary)]"
                  >
                    Download
                  </a>
                )}
              </li>
            ))}
          </ul>
        )}
      </div>
    </PortalShell>
  );
}
