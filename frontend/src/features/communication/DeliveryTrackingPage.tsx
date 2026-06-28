/* Delivery Tracking — full message history with per-message delivery log. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import {
  AXBadge,
  AXModal,
  AXPagination,
  AXSelect,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { MESSAGE_STATUSES, communicationApi, type Message } from './api';

const TONES: Record<string, 'gray' | 'amber' | 'green' | 'red' | 'navy'> = {
  pending: 'amber',
  processing: 'amber',
  sent: 'navy',
  delivered: 'green',
  read: 'green',
  failed: 'red',
  cancelled: 'gray',
};

export function DeliveryTrackingPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Message[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState('');
  const [channel, setChannel] = useState('');
  const [detail, setDetail] = useState<Message | null>(null);

  const load = useMemo(
    () => () => {
      communicationApi
        .messages({ page, filter: { school_id: user?.school_id, status, channel } })
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        });
    },
    [page, status, channel, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<Message>[] = [
    {
      key: 'recipient',
      header: 'Recipient',
      render: (r) => <span className="font-medium">{r.recipient_name ?? '—'}</span>,
    },
    {
      key: 'channel',
      header: 'Channel',
      render: (r) => <AXBadge tone="navy">{r.channel.replace('_', ' ')}</AXBadge>,
    },
    { key: 'subject', header: 'Subject', render: (r) => r.subject ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'sent',
      header: 'Sent',
      render: (r) => r.sent_at?.slice(0, 16).replace('T', ' ') ?? '—',
    },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          onClick={() => communicationApi.message(r.id).then(setDetail)}
          className="text-xs font-semibold text-[var(--navy-accent)]"
        >
          History
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-route text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Delivery Tracking</h2>
        </div>
        <div className="flex items-end gap-2">
          <div className="w-40">
            <AXSelect
              value={channel}
              onChange={(e) => {
                setChannel(e.target.value);
                setPage(1);
              }}
              options={[
                { value: '', label: 'All channels' },
                ...['email', 'sms', 'push', 'in_app'].map((c) => ({ value: c, label: c })),
              ]}
            />
          </div>
          <div className="w-40">
            <AXSelect
              value={status}
              onChange={(e) => {
                setStatus(e.target.value);
                setPage(1);
              }}
              options={[
                { value: '', label: 'All statuses' },
                ...MESSAGE_STATUSES.map((s) => ({ value: s, label: s })),
              ]}
            />
          </div>
        </div>
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No messages yet." />
      <AXPagination meta={meta} onPage={setPage} />

      {detail && (
        <AXModal
          open
          title={`Delivery history — ${detail.recipient_name ?? ''}`}
          onClose={() => setDetail(null)}
        >
          <div className="space-y-2">
            {detail.error && <AXBadge tone="red">{detail.error}</AXBadge>}
            {(detail.logs ?? []).map((l, i) => (
              <div
                key={i}
                className="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2 text-sm"
              >
                <span className="font-medium capitalize text-[var(--navy-primary)]">{l.event}</span>
                <span className="text-xs text-gray-500">
                  {l.detail ?? ''} · {l.at?.slice(0, 19).replace('T', ' ')}
                </span>
              </div>
            ))}
          </div>
        </AXModal>
      )}
    </div>
  );
}
