/* CMS Forms — dynamic public form definitions (contact / enquiry). */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { FORM_TYPES, cmsApi, type Ref } from './api';

export function FormsPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Form name', type: 'text', required: true },
    {
      name: 'type',
      label: 'Type',
      type: 'select',
      options: FORM_TYPES.map((t) => ({ value: t, label: t.replace(/_/g, ' ') })),
      required: true,
    },
    { name: 'notify_email', label: 'Notify email', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Form',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'type', header: 'Type', render: (r) => String(r.type).replace(/_/g, ' ') },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Forms"
      icon="wpforms"
      unitLabel="forms"
      api={cmsApi.forms}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', type: 'contact', notify_email: '' }}
      toForm={(r) => ({
        name: r.name,
        type: String(r.type ?? 'contact'),
        notify_email: r.notify_email ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search forms…"
      sort="name"
    />
  );
}
