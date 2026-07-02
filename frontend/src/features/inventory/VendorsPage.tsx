/* Vendors — managed independently; documents via the Media Platform. */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { inventoryApi, type Ref } from './api';

const fields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'contact', label: 'Contact', type: 'text' },
  { name: 'gst_number', label: 'GST number', type: 'text' },
  { name: 'address', label: 'Address', type: 'text' },
];

export function VendorsPage() {
  const { user } = useAuth();
  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Vendor',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'contact', header: 'Contact', render: (r) => String(r.contact ?? '—') },
    { key: 'gst', header: 'GST', render: (r) => String(r.gst_number ?? '—') },
  ];

  return (
    <EntityManager<Ref>
      title="Vendors"
      icon="handshake"
      unitLabel="vendors"
      api={inventoryApi.vendors}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', contact: '', gst_number: '', address: '' }}
      toForm={(r) => ({
        name: String(r.name),
        contact: (r.contact as string) ?? '',
        gst_number: (r.gst_number as string) ?? '',
        address: (r.address as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search vendors…"
      sort="name"
    />
  );
}
