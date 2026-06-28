/* Staff Import — Upload → Validate → Preview → Import → Summary. Validates
 * duplicate employee numbers, department, designation, joining date. */
import { useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXTable, type AXColumn } from '@ui/ax';
import { staffApi } from './api';

type Row = Record<string, string>;

function parseCsv(text: string): Row[] {
  const lines = text
    .trim()
    .split(/\r?\n/)
    .filter((l) => l.trim() !== '');
  if (lines.length < 2) return [];
  const headers = lines[0].split(',').map((h) => h.trim());
  return lines.slice(1).map((line) => {
    const cells = line.split(',');
    return Object.fromEntries(headers.map((h, i) => [h, (cells[i] ?? '').trim()]));
  });
}

export function ImportPage() {
  const { user } = useAuth();
  const [text, setText] = useState(
    'employee_number,name,phone,email,department_id,designation_id,employment_type,joining_date,is_teaching\nE100,New Staff,9876500000,staff@example.test,1,1,full_time,2025-04-01,true'
  );
  const [rows, setRows] = useState<Row[]>([]);
  const [errors, setErrors] = useState<Record<number, string[]>>({});
  const [valid, setValid] = useState<boolean | null>(null);
  const [summary, setSummary] = useState<{ created: number; skipped: number } | null>(null);

  const withSchool = (parsed: Row[]) =>
    parsed.map((r) => ({ school_id: String(user?.school_id ?? ''), ...r }));

  const validate = async () => {
    const parsed = withSchool(parseCsv(text));
    setRows(parsed);
    setSummary(null);
    const res = await staffApi.import.validate(parsed);
    setValid(res.valid);
    setErrors(res.errors ?? {});
  };

  const execute = async () => {
    const parsed = rows.length ? rows : withSchool(parseCsv(text));
    const res = await staffApi.import.execute(parsed);
    setSummary(res);
    setValid(null);
  };

  const columns: AXColumn<Row>[] = [
    { key: '_i', header: '#', render: (r) => Number(r.__i) + 1 },
    { key: 'employee_number', header: 'Emp. No.', render: (r) => r.employee_number },
    { key: 'name', header: 'Name', render: (r) => r.name },
    {
      key: 'errors',
      header: 'Validation',
      render: (r) =>
        errors[Number(r.__i)] ? (
          <AXBadge tone="red">{errors[Number(r.__i)].join(', ')}</AXBadge>
        ) : (
          <AXBadge tone="green">OK</AXBadge>
        ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-file-import text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Staff Import</h2>
      </div>

      <div className="erp-card space-y-3">
        <label className="block text-sm font-medium text-gray-700">CSV (first row = headers)</label>
        <textarea
          value={text}
          onChange={(e) => setText(e.target.value)}
          rows={6}
          className="w-full rounded-md border border-gray-300 p-3 font-mono text-xs outline-none focus:border-[var(--navy-accent)]"
        />
        <div className="flex gap-2">
          <button
            onClick={validate}
            className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
          >
            <i className="fas fa-circle-check mr-1" /> Validate &amp; Preview
          </button>
          <button
            onClick={execute}
            disabled={valid === false}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            <i className="fas fa-upload mr-1" /> Import
          </button>
        </div>
      </div>

      {rows.length > 0 && (
        <AXTable
          columns={columns}
          rows={rows.map((r, i) => ({ ...r, __i: String(i) }))}
          rowKey={(r) => r.__i}
          empty="No rows."
        />
      )}
      {valid !== null && (
        <AXBadge tone={valid ? 'green' : 'red'}>
          {valid ? 'All rows valid' : 'Some rows have errors'}
        </AXBadge>
      )}
      {summary && (
        <div className="erp-card">
          <h3 className="mb-2 text-sm font-semibold text-[var(--navy-primary)]">Import Summary</h3>
          <div className="flex gap-4 text-sm">
            <span>
              Created: <strong className="text-[var(--success)]">{summary.created}</strong>
            </span>
            <span>
              Skipped: <strong className="text-gray-500">{summary.skipped}</strong>
            </span>
          </div>
        </div>
      )}
    </div>
  );
}
