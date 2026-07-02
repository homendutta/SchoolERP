/* Copies — physical, borrowable items. Each has its own Identity (barcode/QR).
 * Status transitions here cover lost / damaged / under-repair / withdrawn. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { COPY_CONDITIONS, COPY_STATUSES, libraryApi, type Copy } from './api';

const TONES: Record<string, 'green' | 'navy' | 'amber' | 'red' | 'gray'> = {
  available: 'green',
  borrowed: 'navy',
  reserved: 'amber',
  lost: 'red',
  damaged: 'red',
  under_repair: 'amber',
  withdrawn: 'gray',
};

export function CopiesPage() {
  const { user } = useAuth();
  const [books, setBooks] = useState<FieldOption[]>([]);
  const [locations, setLocations] = useState<FieldOption[]>([]);

  useEffect(() => {
    const f = { filter: { school_id: user?.school_id }, per_page: 500 };
    libraryApi.catalog
      .list(f)
      .then((r) => setBooks(r.data.map((b) => ({ value: String(b.id), label: b.title }))));
    libraryApi.locations
      .list(f)
      .then((r) => setLocations(r.data.map((l) => ({ value: String(l.id), label: l.name }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    {
      name: 'book_id',
      label: 'Book',
      type: 'select',
      options: [{ value: '', label: 'Select…' }, ...books],
      required: true,
    },
    { name: 'copy_number', label: 'Copy number', type: 'text', required: true },
    {
      name: 'location_id',
      label: 'Location',
      type: 'select',
      options: [{ value: '', label: '—' }, ...locations],
    },
    { name: 'shelf', label: 'Shelf', type: 'text' },
    { name: 'rack', label: 'Rack', type: 'text' },
    { name: 'acquisition_date', label: 'Acquisition date', type: 'date' },
    { name: 'purchase_price', label: 'Purchase price', type: 'number' },
    {
      name: 'condition',
      label: 'Condition',
      type: 'select',
      options: COPY_CONDITIONS.map((c) => ({ value: c, label: c })),
    },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: COPY_STATUSES.map((s) => ({ value: s, label: s.replace('_', ' ') })),
    },
  ];

  const columns: AXColumn<Copy>[] = [
    {
      key: 'copy_number',
      header: 'Copy #',
      render: (r) => <span className="font-medium">{r.copy_number}</span>,
    },
    { key: 'book', header: 'Book', render: (r) => r.book ?? '—' },
    {
      key: 'barcode',
      header: 'Barcode',
      render: (r) => <code className="text-xs text-gray-500">{r.identity_number ?? '—'}</code>,
    },
    { key: 'condition', header: 'Condition', render: (r) => r.condition },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status.replace('_', ' ')}</AXBadge>
      ),
    },
  ];

  return (
    <EntityManager<Copy>
      title="Copies"
      icon="clone"
      unitLabel="copies"
      api={libraryApi.copies}
      columns={columns}
      fields={fields}
      emptyForm={{
        book_id: '',
        copy_number: '',
        location_id: '',
        shelf: '',
        rack: '',
        acquisition_date: '',
        purchase_price: '',
        condition: 'good',
        status: 'available',
      }}
      toForm={(r) => ({
        book_id: String(r.book_id),
        copy_number: r.copy_number,
        location_id: r.location_id ? String(r.location_id) : '',
        shelf: r.shelf ?? '',
        rack: r.rack ?? '',
        acquisition_date: r.acquisition_date ?? '',
        purchase_price: r.purchase_price ?? '',
        condition: r.condition,
        status: r.status,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="copy_number"
      searchPlaceholder="Search copies…"
      sort="copy_number"
    />
  );
}
