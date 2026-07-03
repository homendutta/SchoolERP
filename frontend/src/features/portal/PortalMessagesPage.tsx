/* Portal Messages — announcements + circulars from the Communication module. */
import { useEffect, useState } from 'react';
import { portalApi } from './api';
import { usePortal } from './usePortal';
import { PortalShell } from './PortalShell';

export function PortalMessagesPage() {
  const { context, error } = usePortal();
  const [data, setData] = useState<Record<string, unknown> | null>(null);

  useEffect(() => {
    portalApi
      .messages()
      .then(setData)
      .catch(() => setData(null));
  }, []);

  const announcements = (data?.announcements as Array<Record<string, unknown>>) ?? [];
  const circulars = (data?.circulars as Array<Record<string, unknown>>) ?? [];

  const list = (title: string, items: Array<Record<string, unknown>>) => (
    <div className="erp-card">
      <h3 className="mb-2 text-sm font-semibold text-[var(--navy-primary)]">{title}</h3>
      {items.length === 0 ? (
        <p className="text-sm text-gray-400">Nothing yet.</p>
      ) : (
        <ul className="space-y-2">
          {items.map((m) => (
            <li key={String(m.id)} className="border-b pb-2 last:border-0">
              <div className="flex items-center justify-between gap-2">
                <span className="font-medium">{String(m.title)}</span>
                {typeof m.attachment === 'string' && (
                  <a
                    href={m.attachment}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-xs text-[var(--navy-accent)]"
                  >
                    Attachment
                  </a>
                )}
              </div>
              <p className="text-sm text-gray-600">{String(m.body ?? '')}</p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );

  return (
    <PortalShell
      title="Messages & Circulars"
      icon="envelope"
      context={context}
      studentId={null}
      requiresStudent={false}
      error={error}
    >
      <div className="grid gap-4 md:grid-cols-2">
        {list('Announcements', announcements)}
        {list('Circulars', circulars)}
      </div>
    </PortalShell>
  );
}
