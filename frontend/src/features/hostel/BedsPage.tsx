/* Beds — bed code from the Number Generator; students occupy beds. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { BED_STATUSES, hostelApi, type Bed } from './api';

const TONES: Record<string, 'green' | 'navy' | 'amber' | 'red'> = {
  available: 'green',
  occupied: 'navy',
  reserved: 'amber',
  under_maintenance: 'red',
};

export function BedsPage() {
  const { user } = useAuth();
  const [rooms, setRooms] = useState<FieldOption[]>([]);

  useEffect(() => {
    hostelApi.rooms
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) =>
        setRooms(r.data.map((x) => ({ value: String(x.id), label: String(x.room_number) })))
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    {
      name: 'room_id',
      label: 'Room',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...rooms],
      required: true,
    },
    { name: 'bed_number', label: 'Bed number', type: 'text', required: true },
    { name: 'bed_code', label: 'Bed code (blank = auto)', type: 'text' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: BED_STATUSES.map((s) => ({ value: s, label: s.replace('_', ' ') })),
    },
  ];

  const columns: AXColumn<Bed>[] = [
    {
      key: 'bed_number',
      header: 'Bed',
      render: (r) => <span className="font-medium">{r.bed_number}</span>,
    },
    {
      key: 'bed_code',
      header: 'Code',
      render: (r) => <code className="text-xs text-gray-500">{r.bed_code ?? '—'}</code>,
    },
    {
      key: 'room',
      header: 'Room',
      render: (r) => String((r.room as { room_number?: string })?.room_number ?? r.room_id),
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status.replace('_', ' ')}</AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<Bed>
      title="Beds"
      icon="bed"
      unitLabel="beds"
      api={hostelApi.beds}
      columns={columns}
      fields={fields}
      emptyForm={{ room_id: '', bed_number: '', bed_code: '', status: 'available' }}
      toForm={(r) => ({
        room_id: String(r.room_id),
        bed_number: r.bed_number,
        bed_code: r.bed_code ?? '',
        status: r.status,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="bed_number"
      searchPlaceholder="Search beds…"
      sort="bed_number"
    />
  );
}
