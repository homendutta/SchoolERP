/* Event Logs — the immutable event bus + integration request logs. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { integrationsApi, type Ref } from './api';

const TONES: Record<string, 'green' | 'red'> = { success: 'green', failure: 'red' };

export function LogsPage() {
  const { user } = useAuth();
  const [tab, setTab] = useState<'logs' | 'events'>('logs');
  const [rows, setRows] = useState<Ref[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const load = useMemo(
    () => () => {
      const call = tab === 'logs' ? integrationsApi.logs : integrationsApi.events;
      call({ page, school_id: user?.school_id }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      });
    },
    [tab, page, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const logCols: AXColumn<Ref>[] = [
    { key: 'provider', header: 'Provider', render: (r) => String(r.provider_code ?? '—') },
    { key: 'method', header: 'Method', render: (r) => String(r.method ?? '') },
    {
      key: 'url',
      header: 'URL',
      render: (r) => <code className="text-xs text-gray-500">{String(r.url ?? '')}</code>,
    },
    { key: 'code', header: 'Code', render: (r) => String(r.response_code ?? '') },
    { key: 'ms', header: 'ms', render: (r) => String(r.duration_ms ?? '') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'red'}>{String(r.status)}</AXBadge>,
    },
  ];

  const eventCols: AXColumn<Ref>[] = [
    {
      key: 'event',
      header: 'Event',
      render: (r) => <span className="font-medium">{String(r.event)}</span>,
    },
    { key: 'source', header: 'Source', render: (r) => String(r.source ?? '—') },
    {
      key: 'when',
      header: 'Dispatched',
      render: (r) => String(r.dispatched_at ?? '—').slice(0, 19),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-list-ul text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Integration Logs</h2>
      </div>

      <div className="flex gap-2">
        {(['logs', 'events'] as const).map((t) => (
          <button
            key={t}
            onClick={() => {
              setTab(t);
              setPage(1);
            }}
            className={`rounded-md px-3 py-1.5 text-sm font-semibold ${tab === t ? 'bg-[var(--navy-primary)] text-white' : 'bg-gray-100 text-gray-600'}`}
          >
            {t === 'logs' ? 'Request Logs' : 'Event Bus'}
          </button>
        ))}
      </div>

      <AXTable
        columns={tab === 'logs' ? logCols : eventCols}
        rows={rows}
        rowKey={(r) => r.id}
        empty="Nothing logged yet."
      />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
