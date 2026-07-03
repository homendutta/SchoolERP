/* Portal Transport & Hostel — read-only from the Transport + Hostel modules. */
import { useEffect, useState } from 'react';
import { portalApi } from './api';
import { usePortal } from './usePortal';
import { PortalShell } from './PortalShell';

function Info({
  title,
  icon,
  data,
}: {
  title: string;
  icon: string;
  data: Record<string, unknown> | null;
}) {
  return (
    <div className="erp-card">
      <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold text-[var(--navy-primary)]">
        <i className={`fas fa-${icon}`} />
        {title}
      </h3>
      {data ? (
        <dl className="space-y-1 text-sm">
          {Object.entries(data).map(([k, v]) => (
            <div key={k} className="flex justify-between gap-4">
              <dt className="text-gray-500">{k.replace(/_/g, ' ')}</dt>
              <dd className="font-medium">{String(v ?? '—')}</dd>
            </div>
          ))}
        </dl>
      ) : (
        <p className="text-sm text-gray-400">Not assigned.</p>
      )}
    </div>
  );
}

export function PortalInfoPage() {
  const { context, studentId, setStudentId, error } = usePortal();
  const [transport, setTransport] = useState<Record<string, unknown> | null>(null);
  const [hostel, setHostel] = useState<Record<string, unknown> | null>(null);

  useEffect(() => {
    if (!studentId) return;
    portalApi
      .transport(studentId)
      .then(setTransport)
      .catch(() => setTransport(null));
    portalApi
      .hostel(studentId)
      .then(setHostel)
      .catch(() => setHostel(null));
  }, [studentId]);

  return (
    <PortalShell
      title="Transport & Hostel"
      icon="bus"
      context={context}
      studentId={studentId}
      onStudent={setStudentId}
      error={error}
    >
      <div className="grid gap-4 md:grid-cols-2">
        <Info title="Transport" icon="bus" data={transport} />
        <Info title="Hostel" icon="bed" data={hostel} />
      </div>
    </PortalShell>
  );
}
