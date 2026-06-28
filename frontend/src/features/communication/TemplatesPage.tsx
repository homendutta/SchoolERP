/* Templates — reusable, variable-driven message templates per channel. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { CHANNELS, communicationApi, type Template } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'code', label: 'Code', type: 'text', required: true },
  {
    name: 'channel',
    label: 'Channel',
    type: 'select',
    options: CHANNELS.map((c) => ({ value: c, label: c.replace('_', ' ') })),
  },
  { name: 'subject', label: 'Subject (use {{var}})', type: 'text' },
  { name: 'body', label: 'Body (use {{var}})', type: 'text', required: true },
  { name: 'language', label: 'Language', type: 'text' },
];

export function TemplatesPage() {
  const { user } = useAuth();
  const columns: AXColumn<Template>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    {
      key: 'code',
      header: 'Code',
      render: (r) => <code className="text-xs text-gray-500">{r.code}</code>,
    },
    {
      key: 'channel',
      header: 'Channel',
      render: (r) => <AXBadge tone="navy">{r.channel.replace('_', ' ')}</AXBadge>,
    },
    { key: 'subject', header: 'Subject', render: (r) => r.subject ?? '—' },
    { key: 'lang', header: 'Lang', render: (r) => r.language },
  ];

  return (
    <EntityManager<Template>
      title="Templates"
      icon="file-lines"
      unitLabel="templates"
      api={communicationApi.templates}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '', channel: 'email', subject: '', body: '', language: 'en' }}
      toForm={(r) => ({
        name: r.name,
        code: r.code,
        channel: r.channel,
        subject: r.subject ?? '',
        body: r.body,
        language: r.language,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search templates…"
      sort="name"
    />
  );
}
