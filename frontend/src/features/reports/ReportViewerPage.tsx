/* Report Viewer — pick a report, run it, then export (CSV/Excel), print or save. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXSelect } from '@ui/ax';
import { reportsApi, type CatalogItem, type RunResult } from './api';

export function ReportViewerPage() {
  const { user } = useAuth();
  const [catalog, setCatalog] = useState<CatalogItem[]>([]);
  const [key, setKey] = useState('');
  const [filterText, setFilterText] = useState('');
  const [filterCol, setFilterCol] = useState('');
  const [result, setResult] = useState<RunResult | null>(null);
  const [status, setStatus] = useState<string | null>(null);

  useEffect(() => {
    reportsApi
      .catalog()
      .then(setCatalog)
      .catch(() => undefined);
  }, []);

  const current = useMemo(() => catalog.find((c) => c.key === key), [catalog, key]);
  const filter = () => (filterCol && filterText ? { [filterCol]: filterText } : undefined);

  const run = async () => {
    setStatus(null);
    try {
      const res = await reportsApi.run({
        report_key: key,
        school_id: user?.school_id,
        filter: filter(),
      });
      setResult(res);
    } catch (e) {
      setStatus(e instanceof Error ? e.message : 'Run failed.');
    }
  };

  const doExport = (format: string) =>
    reportsApi
      .download({ report_key: key, format, school_id: user?.school_id, filter: filter() })
      .catch((e) => setStatus(e instanceof Error ? e.message : 'Export failed.'));

  const doPrint = () =>
    reportsApi.print({
      report_key: key,
      school_id: user?.school_id,
      filter: filter(),
      options: { title: current?.name, header: current?.category },
    });

  const save = async () => {
    if (!current) return;
    await reportsApi.saved.create({
      school_id: user?.school_id,
      report_key: key,
      name: current.name,
      filters: filter() ?? {},
    });
    setStatus('Saved.');
  };

  const cols = current ? Object.entries(current.columns) : [];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-table text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Report Viewer</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-64">
          <AXSelect
            label="Report"
            value={key}
            onChange={(e) => {
              setKey(e.target.value);
              setResult(null);
              setFilterCol('');
            }}
            options={[
              { value: '', label: 'Select…' },
              ...catalog.map((c) => ({ value: c.key, label: `${c.module} · ${c.name}` })),
            ]}
          />
        </div>
        {current && (
          <>
            <div className="w-44">
              <AXSelect
                label="Filter column"
                value={filterCol}
                onChange={(e) => setFilterCol(e.target.value)}
                options={[
                  { value: '', label: '—' },
                  ...cols.map(([k, l]) => ({ value: k, label: l })),
                ]}
              />
            </div>
            <div className="w-40">
              <AXInput
                label="Filter value"
                value={filterText}
                onChange={(e) => setFilterText(e.target.value)}
              />
            </div>
          </>
        )}
        <button
          onClick={run}
          disabled={!key}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Run
        </button>
        {result && (
          <>
            <button
              onClick={() => doExport('csv')}
              className="rounded-md border border-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-[var(--navy-primary)]"
            >
              CSV
            </button>
            <button
              onClick={() => doExport('xlsx')}
              className="rounded-md border border-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-[var(--navy-primary)]"
            >
              Excel
            </button>
            <button
              onClick={doPrint}
              className="rounded-md border border-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-[var(--navy-primary)]"
            >
              Print
            </button>
            <button
              onClick={save}
              className="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-600"
            >
              Save
            </button>
          </>
        )}
        {status && <AXBadge tone="navy">{status}</AXBadge>}
      </div>

      {result && (
        <div className="erp-card overflow-x-auto">
          <div className="mb-2 text-xs text-gray-500">{result.total} row(s)</div>
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 text-left text-gray-500">
                {cols.map(([k, l]) => (
                  <th key={k} className="px-2 py-1">
                    {l}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {result.rows.map((row, i) => (
                <tr key={i} className="border-b border-gray-100">
                  {cols.map(([k]) => (
                    <td key={k} className="px-2 py-1">
                      {String(row[k] ?? '')}
                    </td>
                  ))}
                </tr>
              ))}
            </tbody>
            {Object.keys(result.totals).length > 0 && (
              <tfoot>
                <tr className="font-semibold text-[var(--navy-primary)]">
                  {cols.map(([k]) => (
                    <td key={k} className="px-2 py-1">
                      {result.totals[k] !== undefined ? result.totals[k] : ''}
                    </td>
                  ))}
                </tr>
              </tfoot>
            )}
          </table>
        </div>
      )}
    </div>
  );
}
