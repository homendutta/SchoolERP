/* Consumables — stock items (never given an Identity; never mixed with assets). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { inventoryApi, type Ref } from './api';

export function ConsumablesPage() {
  const { user } = useAuth();
  const [categories, setCategories] = useState<FieldOption[]>([]);

  useEffect(() => {
    inventoryApi.categories
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) =>
        setCategories(r.data.map((c) => ({ value: String(c.id), label: String(c.name) })))
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    {
      name: 'category_id',
      label: 'Category',
      type: 'select',
      options: [{ value: '', label: '—' }, ...categories],
    },
    { name: 'unit', label: 'Unit', type: 'text' },
    { name: 'minimum_stock', label: 'Minimum stock', type: 'number' },
    { name: 'current_stock', label: 'Current stock', type: 'number' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Item',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'unit', header: 'Unit', render: (r) => String(r.unit ?? '—') },
    { key: 'current', header: 'Current', render: (r) => String(r.current_stock ?? 0) },
    {
      key: 'low',
      header: '',
      render: (r) =>
        Number(r.current_stock) <= Number(r.minimum_stock) ? (
          <AXBadge tone="red">low</AXBadge>
        ) : (
          <AXBadge tone="green">ok</AXBadge>
        ),
    },
  ];

  return (
    <EntityManager<Ref>
      title="Consumables"
      icon="boxes-stacked"
      unitLabel="consumables"
      api={inventoryApi.consumables}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        code: '',
        category_id: '',
        unit: 'unit',
        minimum_stock: 0,
        current_stock: 0,
      }}
      toForm={(r) => ({
        name: String(r.name),
        code: (r.code as string) ?? '',
        category_id: r.category_id ? String(r.category_id) : '',
        unit: (r.unit as string) ?? 'unit',
        minimum_stock: (r.minimum_stock as number) ?? 0,
        current_stock: (r.current_stock as number) ?? 0,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search consumables…"
      sort="name"
    />
  );
}
