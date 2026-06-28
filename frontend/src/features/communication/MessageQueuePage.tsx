/* Message Queue — compose & publish (the only send path), process the queue,
 * retry/cancel. Every message is tracked; none are lost. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import {
  AXBadge,
  AXInput,
  AXModal,
  AXPagination,
  AXSelect,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { useClasses } from '@features/academic/useReference';
import { AUDIENCE_TYPES, CHANNELS, communicationApi, type Message, type Template } from './api';

const TONES: Record<string, 'gray' | 'amber' | 'green' | 'red' | 'navy'> = {
  pending: 'amber',
  processing: 'amber',
  sent: 'navy',
  delivered: 'green',
  read: 'green',
  failed: 'red',
  cancelled: 'gray',
};

export function MessageQueuePage() {
  const { user } = useAuth();
  const classes = useClasses();
  const [rows, setRows] = useState<Message[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState('');
  const [composeOpen, setComposeOpen] = useState(false);
  const [msg, setMsg] = useState<string | null>(null);

  const load = useMemo(
    () => () => {
      communicationApi
        .messages({ page, filter: { school_id: user?.school_id, status } })
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        });
    },
    [page, status, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const process = async () => {
    if (!user?.school_id) return;
    const res = await communicationApi.processQueue(user.school_id);
    setMsg(`Processed ${res.processed} message(s)`);
    load();
  };

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
    { key: 'attempts', header: 'Attempts', render: (r) => `${r.attempts}/${r.max_attempts}` },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <div className="flex gap-1">
          {(r.status === 'failed' || r.status === 'cancelled') && (
            <button
              onClick={() => communicationApi.retry(r.id).then(load)}
              className="text-xs font-semibold text-[var(--navy-accent)]"
            >
              Retry
            </button>
          )}
          {r.status === 'pending' && (
            <button
              onClick={() => communicationApi.cancel(r.id).then(load)}
              className="text-xs font-semibold text-[var(--danger)]"
            >
              Cancel
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-inbox text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Message Queue</h2>
        </div>
        <div className="flex items-end gap-2">
          <div className="w-40">
            <AXSelect
              value={status}
              onChange={(e) => {
                setStatus(e.target.value);
                setPage(1);
              }}
              options={[
                { value: '', label: 'All statuses' },
                ...['pending', 'processing', 'delivered', 'failed', 'cancelled'].map((s) => ({
                  value: s,
                  label: s,
                })),
              ]}
            />
          </div>
          <button
            onClick={process}
            className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
          >
            <i className="fas fa-play mr-1" /> Process queue
          </button>
          <button
            onClick={() => setComposeOpen(true)}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
          >
            <i className="fas fa-paper-plane mr-1" /> Compose
          </button>
        </div>
      </div>
      {msg && <AXBadge tone="green">{msg}</AXBadge>}

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No messages yet." />
      <AXPagination meta={meta} onPage={setPage} />

      {composeOpen && (
        <ComposeModal
          schoolId={user?.school_id}
          classes={classes}
          onClose={() => setComposeOpen(false)}
          onDone={() => {
            setComposeOpen(false);
            load();
          }}
        />
      )}
    </div>
  );
}

function ComposeModal({
  schoolId,
  classes,
  onClose,
  onDone,
}: {
  schoolId: number | null | undefined;
  classes: Array<{ value: string; label: string }>;
  onClose: () => void;
  onDone: () => void;
}) {
  const [templates, setTemplates] = useState<Template[]>([]);
  const [form, setForm] = useState({
    channel: 'in_app',
    audience_type: 'students',
    class_id: '',
    template_code: '',
    subject: '',
    body: '',
    is_mandatory: false,
    scheduled_at: '',
  });

  useEffect(() => {
    communicationApi.templates
      .list({ filter: { school_id: schoolId }, per_page: 200 })
      .then((r) => setTemplates(r.data));
  }, [schoolId]);

  const submit = async () => {
    await communicationApi.publish({
      school_id: schoolId,
      channel: form.channel,
      audience_type: form.audience_type,
      class_id: form.class_id ? Number(form.class_id) : null,
      template_code: form.template_code || null,
      subject: form.subject || null,
      body: form.body || null,
      is_mandatory: form.is_mandatory,
      scheduled_at: form.scheduled_at || null,
    });
    onDone();
  };

  return (
    <AXModal open title="Compose & publish" onClose={onClose}>
      <div className="space-y-3">
        <div className="grid grid-cols-2 gap-2">
          <AXSelect
            label="Channel"
            value={form.channel}
            onChange={(e) => setForm((f) => ({ ...f, channel: e.target.value }))}
            options={CHANNELS.map((c) => ({ value: c, label: c.replace('_', ' ') }))}
          />
          <AXSelect
            label="Audience"
            value={form.audience_type}
            onChange={(e) => setForm((f) => ({ ...f, audience_type: e.target.value }))}
            options={AUDIENCE_TYPES.map((a) => ({ value: a, label: a }))}
          />
        </div>
        {(form.audience_type === 'class' || form.audience_type === 'students') && (
          <AXSelect
            label="Class (optional)"
            value={form.class_id}
            onChange={(e) => setForm((f) => ({ ...f, class_id: e.target.value }))}
            options={[{ value: '', label: 'All' }, ...classes]}
          />
        )}
        <AXSelect
          label="Template (optional)"
          value={form.template_code}
          onChange={(e) => setForm((f) => ({ ...f, template_code: e.target.value }))}
          options={[
            { value: '', label: 'No template (free text)' },
            ...templates
              .filter((t) => t.channel === form.channel)
              .map((t) => ({ value: t.code, label: t.name })),
          ]}
        />
        {!form.template_code && (
          <>
            <AXInput
              label="Subject"
              value={form.subject}
              onChange={(e) => setForm((f) => ({ ...f, subject: e.target.value }))}
            />
            <AXInput
              label="Body"
              value={form.body}
              onChange={(e) => setForm((f) => ({ ...f, body: e.target.value }))}
            />
          </>
        )}
        <AXInput
          label="Schedule (optional)"
          type="datetime-local"
          value={form.scheduled_at}
          onChange={(e) => setForm((f) => ({ ...f, scheduled_at: e.target.value }))}
        />
        <label className="flex items-center gap-2 text-sm text-gray-600">
          <input
            type="checkbox"
            checked={form.is_mandatory}
            onChange={(e) => setForm((f) => ({ ...f, is_mandatory: e.target.checked }))}
          />{' '}
          Mandatory (override user preferences)
        </label>
        <div className="flex justify-end gap-2">
          <button
            onClick={onClose}
            className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
          >
            Cancel
          </button>
          <button
            onClick={submit}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
          >
            Publish
          </button>
        </div>
      </div>
    </AXModal>
  );
}
