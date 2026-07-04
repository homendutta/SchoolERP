/* Providers — register + configure (encrypted) + health/test. Modules resolve a
 * provider by category through the platform; they never call vendors directly. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { PROVIDER_STATUSES, integrationsApi, type Ref } from './api';

const HEALTH_TONES: Record<string, 'gray' | 'green' | 'amber' | 'red'> = {
  unknown: 'gray',
  healthy: 'green',
  degraded: 'amber',
  down: 'red',
};

/** Parse the config JSON textarea into an object payload. */
function toPayload(d: Record<string, unknown>): Record<string, unknown> {
  const { config_json, ...rest } = d;
  let config: Record<string, unknown> | undefined;
  try {
    config = config_json ? JSON.parse(String(config_json)) : undefined;
  } catch {
    config = undefined;
  }
  return { ...rest, config };
}

export function ProvidersPage() {
  const { user } = useAuth();
  const [categories, setCategories] = useState<FieldOption[]>([]);
  const [adapters, setAdapters] = useState<FieldOption[]>([]);

  useEffect(() => {
    integrationsApi.categories
      .list({ filter: { school_id: user?.school_id }, per_page: 200 })
      .then((r) =>
        setCategories(r.data.map((c) => ({ value: String(c.id), label: String(c.name) })))
      );
    integrationsApi
      .adapters()
      .then((a) =>
        setAdapters(a.map((x) => ({ value: String(x.code), label: `${x.code} (${x.category})` })))
      )
      .catch(() => undefined);
  }, [user?.school_id]);

  const api = useMemo(
    () => ({
      ...integrationsApi.providers,
      create: (d: Record<string, unknown>) => integrationsApi.providers.create(toPayload(d)),
      update: (id: number, d: Record<string, unknown>) =>
        integrationsApi.providers.update(id, toPayload(d)),
    }),
    []
  );

  const fields: Field[] = [
    { name: 'name', label: 'Provider name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text', required: true },
    {
      name: 'category_id',
      label: 'Category',
      type: 'select',
      options: [{ value: '', label: '—' }, ...categories],
    },
    {
      name: 'adapter',
      label: 'Adapter',
      type: 'select',
      options: [{ value: '', label: '—' }, ...adapters],
    },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: PROVIDER_STATUSES.map((s) => ({ value: s, label: s })),
    },
    { name: 'priority', label: 'Priority', type: 'number' },
    { name: 'is_default', label: 'Default for category', type: 'checkbox' },
    { name: 'config_json', label: 'Config JSON (encrypted at rest)', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Provider',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'code', header: 'Code', render: (r) => String(r.code) },
    { key: 'status', header: 'Status', render: (r) => String(r.status) },
    {
      key: 'health',
      header: 'Health',
      render: (r) => (
        <AXBadge tone={HEALTH_TONES[String(r.health)] ?? 'gray'}>{String(r.health)}</AXBadge>
      ),
    },
    { key: 'default', header: 'Default', render: (r) => (r.is_default ? '★' : '') },
  ];

  return (
    <EntityManager<Ref>
      title="Providers"
      icon="plug"
      unitLabel="providers"
      api={api}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        code: '',
        category_id: '',
        adapter: '',
        status: 'disabled',
        priority: 100,
        is_default: false,
        config_json: '',
      }}
      toForm={(r) => ({
        name: r.name,
        code: String(r.code ?? ''),
        category_id: r.category_id ? String(r.category_id) : '',
        adapter: String(r.adapter ?? ''),
        status: String(r.status ?? 'disabled'),
        priority: (r.priority as number) ?? 100,
        is_default: Boolean(r.is_default),
        config_json: '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search providers…"
      sort="priority"
      rowExtras={(r, reload) => (
        <div className="flex gap-2">
          <button
            onClick={() => integrationsApi.health(r.id).then(reload)}
            title="Health check"
            className="hover:text-[var(--success)]"
          >
            <i className="fas fa-heart-pulse" />
          </button>
          <button
            onClick={() => integrationsApi.test(r.id).then(reload)}
            title="Test provider"
            className="hover:text-[var(--navy-accent)]"
          >
            <i className="fas fa-vial" />
          </button>
        </div>
      )}
    />
  );
}
