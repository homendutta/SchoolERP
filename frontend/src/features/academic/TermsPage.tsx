/* Terms — defined per academic year (schools set their own; nothing hardcoded). */
import { useState } from 'react';
import type { AXColumn } from '@ui/ax';
import { academicApi, type Term } from './api';
import { EntityManager, statusCell, type Field } from './EntityManager';
import { useYears } from './useReference';

export function TermsPage() {
  const years = useYears();
  const [yearId, setYearId] = useState('');

  const fields: Field[] = [
    {
      name: 'academic_year_id',
      label: 'Academic year',
      type: 'select',
      options: years,
      required: true,
    },
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'short_name', label: 'Short name', type: 'text' },
    { name: 'start_date', label: 'Start date', type: 'date', required: true },
    { name: 'end_date', label: 'End date', type: 'date', required: true },
    { name: 'sort_order', label: 'Sort order', type: 'number' },
  ];

  const columns: AXColumn<Term>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    {
      key: 'period',
      header: 'Period',
      render: (r) => (
        <span className="text-xs text-gray-500">
          {r.start_date} → {r.end_date}
        </span>
      ),
    },
    { key: 'sort_order', header: 'Order' },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Term>
      title="Terms"
      icon="layer-group"
      unitLabel="terms"
      api={academicApi.terms}
      columns={columns}
      fields={fields}
      emptyForm={{
        academic_year_id: yearId || '',
        name: '',
        short_name: '',
        start_date: '',
        end_date: '',
        sort_order: 0,
      }}
      toForm={(r) => ({
        academic_year_id: r.academic_year_id,
        name: r.name,
        short_name: r.short_name ?? '',
        start_date: r.start_date ?? '',
        end_date: r.end_date ?? '',
        sort_order: r.sort_order,
      })}
      listParams={yearId ? { filter: { academic_year_id: yearId } } : {}}
      filters={[
        {
          name: 'academic_year_id',
          label: 'Academic year',
          options: years,
          value: yearId,
          onChange: setYearId,
        },
      ]}
      sort="sort_order"
    />
  );
}
