/* Salary Structures — versioned; assembled from salary components. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXPagination, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { payrollApi, type Ref } from './api';

export function StructuresPage() {
  const { user } = useAuth();
  const [components, setComponents] = useState<Ref[]>([]);
  const [rows, setRows] = useState<Ref[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);
  const [form, setForm] = useState<{
    name: string;
    grade: string;
    effective_date: string;
    picked: number[];
  }>({
    name: '',
    grade: '',
    effective_date: '',
    picked: [],
  });
  const [error, setError] = useState<string | null>(null);

  const load = useMemo(
    () => () =>
      payrollApi.structures.list({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );

  useEffect(() => {
    payrollApi.components
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setComponents(r.data));
  }, [user?.school_id]);
  useEffect(() => {
    load();
  }, [load]);

  const toggle = (id: number) =>
    setForm((f) => ({
      ...f,
      picked: f.picked.includes(id) ? f.picked.filter((x) => x !== id) : [...f.picked, id],
    }));

  const submit = async () => {
    setError(null);
    try {
      await payrollApi.structures.create({
        school_id: user?.school_id,
        name: form.name,
        grade: form.grade || null,
        effective_date: form.effective_date || null,
        components: form.picked.map((id, i) => ({ component_id: id, sequence: i })),
      });
      setForm({ name: '', grade: '', effective_date: '', picked: [] });
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not save structure.');
    }
  };

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Structure',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'grade', header: 'Grade', render: (r) => String(r.grade ?? '—') },
    { key: 'version', header: 'Version', render: (r) => String(r.version ?? 1) },
    {
      key: 'components',
      header: 'Components',
      render: (r) => String(Array.isArray(r.components) ? r.components.length : 0),
    },
    { key: 'effective', header: 'Effective', render: (r) => String(r.effective_date ?? '—') },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-layer-group text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Salary Structures</h2>
      </div>

      <div className="erp-card space-y-3">
        <div className="flex flex-wrap items-end gap-3">
          <div className="w-52">
            <AXInput
              label="Structure name"
              value={form.name}
              onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
            />
          </div>
          <div className="w-28">
            <AXInput
              label="Grade"
              value={form.grade}
              onChange={(e) => setForm((f) => ({ ...f, grade: e.target.value }))}
            />
          </div>
          <div className="w-40">
            <AXInput
              label="Effective date"
              type="date"
              value={form.effective_date}
              onChange={(e) => setForm((f) => ({ ...f, effective_date: e.target.value }))}
            />
          </div>
          <button
            onClick={submit}
            disabled={!form.name || form.picked.length === 0}
            className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            Save structure
          </button>
          {error && <AXBadge tone="red">{error}</AXBadge>}
        </div>
        <div>
          <span className="mb-1 block text-sm font-medium text-gray-700">Components</span>
          <div className="flex max-h-40 flex-wrap gap-2 overflow-y-auto rounded-md border border-gray-300 p-2">
            {components.map((c) => {
              const on = form.picked.includes(c.id);
              return (
                <button
                  type="button"
                  key={c.id}
                  onClick={() => toggle(c.id)}
                  className={`rounded-full px-3 py-1 text-xs ${on ? 'bg-[var(--navy-primary)] text-white' : 'bg-gray-100 text-gray-600'}`}
                >
                  {String(c.name)} · {String(c.component_type).replace(/_/g, ' ')}
                </button>
              );
            })}
            {components.length === 0 && (
              <span className="text-xs text-gray-400">Create salary components first.</span>
            )}
          </div>
        </div>
      </div>

      <AXTable
        columns={columns}
        rows={rows}
        rowKey={(r) => r.id}
        empty="No salary structures yet."
      />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
