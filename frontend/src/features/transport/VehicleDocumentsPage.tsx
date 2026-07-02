/* Vehicle Documents — Media references (Insurance/Registration/Pollution…). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { DOCUMENT_TYPES, transportApi, type Ref } from './api';

export function VehicleDocumentsPage() {
  const { user } = useAuth();
  const [vehicles, setVehicles] = useState<FieldOption[]>([]);

  useEffect(() => {
    transportApi.vehicles
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) =>
        setVehicles(r.data.map((v) => ({ value: String(v.id), label: v.vehicle_number })))
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    {
      name: 'vehicle_id',
      label: 'Vehicle',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...vehicles],
      required: true,
    },
    {
      name: 'document_type',
      label: 'Document',
      type: 'select',
      options: DOCUMENT_TYPES.map((d) => ({ value: d, label: d })),
      required: true,
    },
    { name: 'number', label: 'Document number', type: 'text' },
    { name: 'media_id', label: 'Media ID (attachment)', type: 'number' },
    { name: 'issue_date', label: 'Issue date', type: 'date' },
    { name: 'expiry_date', label: 'Expiry date', type: 'date' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'vehicle',
      header: 'Vehicle',
      render: (r) =>
        String((r.vehicle as { vehicle_number?: string })?.vehicle_number ?? r.vehicle_id ?? '—'),
    },
    {
      key: 'type',
      header: 'Document',
      render: (r) => <AXBadge tone="navy">{String(r.document_type)}</AXBadge>,
    },
    { key: 'number', header: 'Number', render: (r) => (r.number as string) ?? '—' },
    { key: 'expiry', header: 'Expiry', render: (r) => (r.expiry_date as string) ?? '—' },
  ];

  return (
    <EntityManager<Ref>
      title="Vehicle Documents"
      icon="file-shield"
      unitLabel="documents"
      api={transportApi.documents}
      columns={columns}
      fields={fields}
      emptyForm={{
        vehicle_id: '',
        document_type: 'insurance',
        number: '',
        media_id: '',
        issue_date: '',
        expiry_date: '',
      }}
      toForm={(r) => ({
        vehicle_id: String(r.vehicle_id),
        document_type: String(r.document_type),
        number: (r.number as string) ?? '',
        media_id: (r.media_id as number) ?? '',
        issue_date: (r.issue_date as string) ?? '',
        expiry_date: (r.expiry_date as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
