/* Student Assignment — students belong to a route + stop (never a vehicle);
 * capacity is enforced server-side and history is preserved. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXSelect, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { studentsApi, type Student } from '@features/students/api';
import { transportApi, type Assignment, type Route, type Stop } from './api';

const TONES: Record<string, 'green' | 'gray' | 'amber'> = {
  active: 'green',
  transferred: 'amber',
  cancelled: 'gray',
};

export function StudentAssignmentPage() {
  const { user } = useAuth();
  const [students, setStudents] = useState<Student[]>([]);
  const [routes, setRoutes] = useState<Route[]>([]);
  const [stops, setStops] = useState<Stop[]>([]);
  const [form, setForm] = useState({ student_id: '', route_id: '', stop_id: '' });
  const [error, setError] = useState<string | null>(null);
  const [rows, setRows] = useState<Assignment[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  useEffect(() => {
    studentsApi.list({ per_page: 500, sort: 'name' }).then((r) => setStudents(r.data));
    transportApi.routes
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setRoutes(r.data));
  }, [user?.school_id]);

  useEffect(() => {
    if (!form.route_id) {
      setStops([]);
      return;
    }
    transportApi.stops
      .list({ filter: { route_id: form.route_id }, per_page: 200, sort: 'sequence' })
      .then((r) => setStops(r.data));
  }, [form.route_id]);

  const load = useMemo(
    () => () =>
      transportApi.students({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const submit = async () => {
    setError(null);
    try {
      await transportApi.assign({
        student_id: Number(form.student_id),
        route_id: Number(form.route_id),
        stop_id: Number(form.stop_id),
      });
      setForm({ student_id: '', route_id: '', stop_id: '' });
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not assign.');
    }
  };

  const columns: AXColumn<Assignment>[] = [
    {
      key: 'student',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student?.name ?? r.student_id}</span>,
    },
    { key: 'route', header: 'Route', render: (r) => r.route?.name ?? '—' },
    { key: 'stop', header: 'Stop', render: (r) => r.stop?.name ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'act',
      header: '',
      render: (r) =>
        r.status === 'active' ? (
          <button
            onClick={() => transportApi.cancelAssignment(r.id).then(load)}
            className="text-xs font-semibold text-[var(--danger)]"
          >
            Cancel
          </button>
        ) : null,
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-user-graduate text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">
          Student Transport Assignment
        </h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-52">
          <AXSelect
            label="Student"
            value={form.student_id}
            onChange={(e) => setForm((f) => ({ ...f, student_id: e.target.value }))}
            options={[
              { value: '', label: 'Select…' },
              ...students.map((s) => ({
                value: String(s.id),
                label: `${s.admission_number} — ${s.name}`,
              })),
            ]}
          />
        </div>
        <div className="w-48">
          <AXSelect
            label="Route"
            value={form.route_id}
            onChange={(e) => setForm((f) => ({ ...f, route_id: e.target.value, stop_id: '' }))}
            options={[
              { value: '', label: 'Select…' },
              ...routes.map((r) => ({ value: String(r.id), label: r.name })),
            ]}
          />
        </div>
        <div className="w-44">
          <AXSelect
            label="Stop"
            value={form.stop_id}
            onChange={(e) => setForm((f) => ({ ...f, stop_id: e.target.value }))}
            options={[
              { value: '', label: 'Select…' },
              ...stops.map((s) => ({ value: String(s.id), label: s.name })),
            ]}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.student_id || !form.route_id || !form.stop_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          <i className="fas fa-check mr-1" /> Assign
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No assignments yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
