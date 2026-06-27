/* Academic Years — create, edit, archive/restore, and mark one year current. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { academicApi, type AcademicYear } from './api';
import { EntityManager, statusCell, type Field } from './EntityManager';

const fields: Field[] = [
  { name: 'name', label: 'Name (e.g. 2025-2026)', type: 'text', required: true },
  { name: 'short_name', label: 'Short name', type: 'text' },
  { name: 'start_date', label: 'Start date', type: 'date', required: true },
  { name: 'end_date', label: 'End date', type: 'date', required: true },
  { name: 'sort_order', label: 'Sort order', type: 'number' },
];

export function AcademicYearsPage() {
  const { user } = useAuth();

  const columns: AXColumn<AcademicYear>[] = [
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
    {
      key: 'is_current',
      header: 'Current',
      render: (r) =>
        r.is_current ? (
          <AXBadge tone="green">Current</AXBadge>
        ) : (
          <span className="text-gray-300">—</span>
        ),
    },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<AcademicYear>
      title="Academic Years"
      icon="calendar-alt"
      unitLabel="years"
      api={academicApi.years}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', short_name: '', start_date: '', end_date: '', sort_order: 0 }}
      toForm={(r) => ({
        name: r.name,
        short_name: r.short_name ?? '',
        start_date: r.start_date ?? '',
        end_date: r.end_date ?? '',
        sort_order: r.sort_order,
        version: r.version,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search academic years…"
      sort="start_date"
      rowExtras={(r, reload) =>
        r.is_current ? null : (
          <button
            onClick={() => academicApi.years.setCurrent(r.id).then(reload)}
            title="Set as current"
            className="hover:text-[var(--success)]"
          >
            <i className="fas fa-circle-check" />
          </button>
        )
      }
    />
  );
}
