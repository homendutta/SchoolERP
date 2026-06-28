/* Scheduled Messages — future-dated messages waiting in the queue. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { communicationApi, type Message } from './api';

export function ScheduledMessagesPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Message[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });

  const load = useMemo(
    () => () => {
      if (!user?.school_id) return;
      communicationApi.scheduled(user.school_id).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      });
    },
    [user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<Message>[] = [
    {
      key: 'when',
      header: 'Scheduled for',
      render: (r) => (
        <span className="font-medium">{r.scheduled_at?.slice(0, 16).replace('T', ' ')}</span>
      ),
    },
    { key: 'recipient', header: 'Recipient', render: (r) => r.recipient_name ?? '—' },
    {
      key: 'channel',
      header: 'Channel',
      render: (r) => <AXBadge tone="navy">{r.channel.replace('_', ' ')}</AXBadge>,
    },
    { key: 'subject', header: 'Subject', render: (r) => r.subject ?? '—' },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          onClick={() => communicationApi.cancel(r.id).then(load)}
          className="text-xs font-semibold text-[var(--danger)]"
        >
          Cancel
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-clock text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Scheduled Messages</h2>
      </div>
      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No scheduled messages." />
      <AXPagination meta={meta} onPage={() => undefined} />
    </div>
  );
}
