/* AX data display: AXTable, AXPagination, AXFilter. */
import type { ReactNode } from 'react';

export interface AXColumn<T> {
  key: string;
  header: string;
  render?: (row: T) => ReactNode;
  className?: string;
}

interface AXTableProps<T> {
  columns: AXColumn<T>[];
  rows: T[];
  rowKey: (row: T) => string | number;
  loading?: boolean;
  empty?: string;
  selectable?: boolean;
  selected?: Array<string | number>;
  onToggle?: (id: string | number) => void;
  onToggleAll?: (checked: boolean) => void;
}

export function AXTable<T>({
  columns, rows, rowKey, loading, empty = 'No records found.',
  selectable, selected = [], onToggle, onToggleAll,
}: AXTableProps<T>) {
  const allChecked = rows.length > 0 && selected.length === rows.length;
  return (
    <div className="overflow-x-auto rounded-lg border border-gray-200 bg-white">
      <table className="w-full text-left text-sm">
        <thead className="border-b bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
          <tr>
            {selectable && (
              <th className="w-10 px-3 py-3">
                <input type="checkbox" checked={allChecked} onChange={(e) => onToggleAll?.(e.target.checked)} />
              </th>
            )}
            {columns.map((c) => (
              <th key={c.key} className={`px-4 py-3 ${c.className ?? ''}`}>{c.header}</th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100">
          {loading ? (
            <tr><td colSpan={columns.length + (selectable ? 1 : 0)} className="px-4 py-8 text-center text-gray-400">
              <i className="fas fa-spinner fa-spin" /> Loading…
            </td></tr>
          ) : rows.length === 0 ? (
            <tr><td colSpan={columns.length + (selectable ? 1 : 0)} className="px-4 py-8 text-center text-gray-400">{empty}</td></tr>
          ) : (
            rows.map((row) => {
              const id = rowKey(row);
              return (
                <tr key={id} className="hover:bg-gray-50">
                  {selectable && (
                    <td className="px-3 py-2">
                      <input type="checkbox" checked={selected.includes(id)} onChange={() => onToggle?.(id)} />
                    </td>
                  )}
                  {columns.map((c) => (
                    <td key={c.key} className={`px-4 py-2 ${c.className ?? ''}`}>
                      {c.render ? c.render(row) : String((row as Record<string, unknown>)[c.key] ?? '')}
                    </td>
                  ))}
                </tr>
              );
            })
          )}
        </tbody>
      </table>
    </div>
  );
}

export interface AXPageMeta {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
}

export function AXPagination({ meta, onPage }: { meta: AXPageMeta; onPage: (page: number) => void }) {
  if (meta.last_page <= 1) return null;
  return (
    <div className="flex items-center justify-between px-1 py-3 text-sm text-gray-500">
      <span>{meta.total} record(s)</span>
      <div className="flex items-center gap-1">
        <button
          disabled={meta.current_page <= 1}
          onClick={() => onPage(meta.current_page - 1)}
          className="rounded px-2 py-1 disabled:opacity-40 hover:bg-gray-100"
        >
          <i className="fas fa-chevron-left" />
        </button>
        <span className="px-2">Page {meta.current_page} / {meta.last_page}</span>
        <button
          disabled={meta.current_page >= meta.last_page}
          onClick={() => onPage(meta.current_page + 1)}
          className="rounded px-2 py-1 disabled:opacity-40 hover:bg-gray-100"
        >
          <i className="fas fa-chevron-right" />
        </button>
      </div>
    </div>
  );
}

interface AXFilterProps {
  filters: { key: string; label: string; options: { value: string; label: string }[] }[];
  values: Record<string, string>;
  onChange: (key: string, value: string) => void;
}

export function AXFilter({ filters, values, onChange }: AXFilterProps) {
  return (
    <div className="flex flex-wrap gap-2">
      {filters.map((f) => (
        <select
          key={f.key}
          value={values[f.key] ?? ''}
          onChange={(e) => onChange(f.key, e.target.value)}
          className="rounded-md border border-gray-300 px-3 py-2 text-sm outline-none"
        >
          <option value="">{f.label}: All</option>
          {f.options.map((o) => (
            <option key={o.value} value={o.value}>{o.label}</option>
          ))}
        </select>
      ))}
    </div>
  );
}
