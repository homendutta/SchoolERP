/* Webhooks — incoming (signature-verified) + outgoing (queued, retried). */
import { useMemo } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { WEBHOOK_DIRECTIONS, integrationsApi, type Ref } from './api';

const TONES: Record<string, 'navy' | 'amber'> = { outgoing: 'navy', incoming: 'amber' };

/** Turn the comma-separated events field into an array payload. */
function toPayload(d: Record<string, unknown>): Record<string, unknown> {
  const { events_csv, ...rest } = d;
  const events = String(events_csv ?? '')
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean);
  return { ...rest, events };
}

export function WebhooksPage() {
  const { user } = useAuth();

  const api = useMemo(
    () => ({
      ...integrationsApi.webhooks,
      create: (d: Record<string, unknown>) => integrationsApi.webhooks.create(toPayload(d)),
      update: (id: number, d: Record<string, unknown>) =>
        integrationsApi.webhooks.update(id, toPayload(d)),
    }),
    []
  );

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    {
      name: 'direction',
      label: 'Direction',
      type: 'select',
      options: WEBHOOK_DIRECTIONS.map((d) => ({ value: d, label: d })),
      required: true,
    },
    { name: 'url', label: 'URL (outgoing)', type: 'text' },
    { name: 'secret', label: 'Signing secret', type: 'text' },
    { name: 'events_csv', label: 'Events (comma-separated, * = all)', type: 'text' },
    { name: 'max_retries', label: 'Max retries', type: 'number' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Name',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    {
      key: 'dir',
      header: 'Direction',
      render: (r) => (
        <AXBadge tone={TONES[String(r.direction)] ?? 'navy'}>{String(r.direction)}</AXBadge>
      ),
    },
    {
      key: 'events',
      header: 'Events',
      render: (r) => (Array.isArray(r.events) ? (r.events as string[]).join(', ') : '—'),
    },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Webhooks"
      icon="bolt"
      unitLabel="webhooks"
      api={api}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        direction: 'outgoing',
        url: '',
        secret: '',
        events_csv: '',
        max_retries: 3,
      }}
      toForm={(r) => ({
        name: r.name,
        direction: String(r.direction ?? 'outgoing'),
        url: r.url ?? '',
        secret: '',
        events_csv: Array.isArray(r.events) ? (r.events as string[]).join(', ') : '',
        max_retries: (r.max_retries as number) ?? 3,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search webhooks…"
      sort="id"
    />
  );
}
