/* Timetable Templates (Summer / Winter / Exam) + copy between academic years. */
import { useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXModal, AXSelect, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { useYears } from '@features/academic/useReference';
import { timetableApi, type Template } from './api';

export function TemplatesPage() {
  const { user } = useAuth();
  const years = useYears();
  const [copyOpen, setCopyOpen] = useState(false);
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');
  const [result, setResult] = useState<string | null>(null);

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    {
      name: 'academic_year_id',
      label: 'Academic year',
      type: 'select',
      options: [{ value: '', label: '—' }, ...years],
    },
    { name: 'description', label: 'Description', type: 'text' },
    { name: 'is_active', label: 'Active', type: 'checkbox' },
  ];

  const columns: AXColumn<Template>[] = [
    {
      key: 'name',
      header: 'Template',
      render: (r) => <span className="font-medium">{r.name}</span>,
    },
    { key: 'code', header: 'Code', render: (r) => r.code ?? '—' },
    { key: 'year', header: 'Year', render: (r) => r.academic_year ?? '—' },
    { key: 'entries', header: 'Slots', render: (r) => r.entries_count ?? 0 },
    {
      key: 'is_active',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={r.is_active ? 'green' : 'gray'}>
          {r.is_active ? 'Active' : 'Inactive'}
        </AXBadge>
      ),
    },
  ];

  const runCopy = async () => {
    setResult(null);
    const res = await timetableApi.copyTemplate({
      school_id: user?.school_id,
      from_academic_year_id: Number(from),
      to_academic_year_id: Number(to),
    });
    setResult(`Copied ${res.copied} slot(s).`);
  };

  return (
    <div className="space-y-3">
      <div className="flex justify-end">
        <button
          onClick={() => setCopyOpen(true)}
          className="rounded-md bg-[var(--navy-accent)] px-3 py-2 text-sm font-semibold text-white"
        >
          <i className="fas fa-copy mr-1" /> Copy timetable between years
        </button>
      </div>

      <EntityManager<Template>
        title="Timetable Templates"
        icon="layer-group"
        unitLabel="templates"
        api={timetableApi.templates}
        columns={columns}
        fields={fields}
        emptyForm={{ name: '', code: '', academic_year_id: '', description: '', is_active: false }}
        toForm={(r) => ({
          name: r.name,
          code: r.code ?? '',
          academic_year_id: r.academic_year_id ? String(r.academic_year_id) : '',
          description: r.description ?? '',
          is_active: r.is_active,
        })}
        createDefaults={{ school_id: user?.school_id }}
        searchKey="name"
        searchPlaceholder="Search templates…"
        sort="name"
      />

      <AXModal open={copyOpen} title="Copy timetable" onClose={() => setCopyOpen(false)}>
        <div className="space-y-3">
          <AXSelect
            label="From academic year"
            value={from}
            onChange={(e) => setFrom(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...years]}
          />
          <AXSelect
            label="To academic year"
            value={to}
            onChange={(e) => setTo(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...years]}
          />
          {result && <AXBadge tone="green">{result}</AXBadge>}
          <div className="flex justify-end gap-2">
            <button
              onClick={() => setCopyOpen(false)}
              className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
            >
              Close
            </button>
            <button
              onClick={runCopy}
              disabled={!from || !to || from === to}
              className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
            >
              Copy
            </button>
          </div>
        </div>
      </AXModal>
    </div>
  );
}
