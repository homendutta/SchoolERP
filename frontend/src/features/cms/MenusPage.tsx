/* CMS Menus — header / footer / quick links, ordered + nestable. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { MENU_LOCATIONS, cmsApi, type Ref } from './api';

const TONES: Record<string, 'navy' | 'amber' | 'gray'> = {
  header: 'navy',
  footer: 'amber',
  quick_links: 'gray',
};

export function MenusPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'label', label: 'Label', type: 'text', required: true },
    { name: 'url', label: 'URL', type: 'text' },
    {
      name: 'location',
      label: 'Location',
      type: 'select',
      options: MENU_LOCATIONS.map((l) => ({ value: l, label: l.replace(/_/g, ' ') })),
      required: true,
    },
    { name: 'sequence', label: 'Order', type: 'number' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'label',
      header: 'Label',
      render: (r) => <span className="font-medium">{String(r.label)}</span>,
    },
    {
      key: 'url',
      header: 'URL',
      render: (r) => <code className="text-xs text-gray-500">{String(r.url ?? '—')}</code>,
    },
    {
      key: 'location',
      header: 'Location',
      render: (r) => (
        <AXBadge tone={TONES[String(r.location)] ?? 'gray'}>
          {String(r.location).replace(/_/g, ' ')}
        </AXBadge>
      ),
    },
    { key: 'sequence', header: 'Order', render: (r) => String(r.sequence ?? 0) },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Menus"
      icon="bars"
      unitLabel="menu items"
      api={cmsApi.menus}
      columns={columns}
      fields={fields}
      emptyForm={{ label: '', url: '', location: 'header', sequence: 0 }}
      toForm={(r) => ({
        label: r.label,
        url: r.url ?? '',
        location: String(r.location ?? 'header'),
        sequence: (r.sequence as number) ?? 0,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="label"
      searchPlaceholder="Search menu items…"
      sort="sequence"
    />
  );
}
