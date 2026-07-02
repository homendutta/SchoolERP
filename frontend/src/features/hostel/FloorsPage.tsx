/* Floors — buildings contain floors. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { hostelApi, type Ref } from './api';

export function FloorsPage() {
  const { user } = useAuth();
  const [buildings, setBuildings] = useState<FieldOption[]>([]);

  useEffect(() => {
    hostelApi.buildings
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) =>
        setBuildings(r.data.map((b) => ({ value: String(b.id), label: String(b.name) })))
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    {
      name: 'building_id',
      label: 'Building',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...buildings],
      required: true,
    },
    { name: 'floor_number', label: 'Floor number', type: 'number' },
    { name: 'name', label: 'Floor name', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    { key: 'number', header: '#', render: (r) => String(r.floor_number ?? 0) },
    {
      key: 'name',
      header: 'Floor',
      render: (r) => <span className="font-medium">{String(r.name ?? '—')}</span>,
    },
    {
      key: 'building',
      header: 'Building',
      render: (r) => String((r.building as { name?: string })?.name ?? r.building_id),
    },
  ];

  return (
    <EntityManager<Ref>
      title="Floors"
      icon="layer-group"
      unitLabel="floors"
      api={hostelApi.floors}
      columns={columns}
      fields={fields}
      emptyForm={{ building_id: '', floor_number: 0, name: '' }}
      toForm={(r) => ({
        building_id: String(r.building_id),
        floor_number: (r.floor_number as number) ?? 0,
        name: (r.name as string) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
