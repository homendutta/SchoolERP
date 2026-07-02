/* Asset Models — reusable types of asset (with depreciation metadata). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { DEPRECIATION_METHODS, inventoryApi, type Ref } from './api';

export function ModelsPage() {
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
    {
      name: 'category_id',
      label: 'Category',
      type: 'select',
      options: [{ value: '', label: '—' }, ...categories],
    },
    { name: 'brand', label: 'Brand', type: 'text' },
    { name: 'manufacturer', label: 'Manufacturer', type: 'text' },
    { name: 'model_number', label: 'Model number', type: 'text' },
    { name: 'default_warranty_months', label: 'Default warranty (months)', type: 'number' },
    {
      name: 'depreciation_method',
      label: 'Depreciation method',
      type: 'select',
      options: DEPRECIATION_METHODS.map((d) => ({ value: d, label: d.replace('_', ' ') })),
    },
    { name: 'useful_life_years', label: 'Useful life (years)', type: 'number' },
    { name: 'salvage_value', label: 'Salvage value', type: 'number' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Model',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'brand', header: 'Brand', render: (r) => String(r.brand ?? '—') },
    {
      key: 'category',
      header: 'Category',
      render: (r) => String((r.category as { name?: string })?.name ?? '—'),
    },
    {
      key: 'dep',
      header: 'Depreciation',
      render: (r) => String(r.depreciation_method).replace('_', ' '),
    },
  ];

  return (
    <EntityManager<Ref>
      title="Asset Models"
      icon="cubes"
      unitLabel="models"
      api={inventoryApi.models}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        category_id: '',
        brand: '',
        manufacturer: '',
        model_number: '',
        default_warranty_months: '',
        depreciation_method: 'none',
        useful_life_years: '',
        salvage_value: '',
      }}
      toForm={(r) => ({
        name: String(r.name),
        category_id: r.category_id ? String(r.category_id) : '',
        brand: (r.brand as string) ?? '',
        manufacturer: (r.manufacturer as string) ?? '',
        model_number: (r.model_number as string) ?? '',
        default_warranty_months: (r.default_warranty_months as number) ?? '',
        depreciation_method: String(r.depreciation_method),
        useful_life_years: (r.useful_life_years as number) ?? '',
        salvage_value: (r.salvage_value as number) ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search models…"
      sort="name"
    />
  );
}
