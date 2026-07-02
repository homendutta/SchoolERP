/* Student Allocation — students occupy beds (never rooms). Single-occupant;
 * history preserved. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXSelect, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { studentsApi, type Student } from '@features/students/api';
import { hostelApi, type Allocation, type Bed } from './api';

const TONES: Record<string, 'green' | 'gray' | 'amber'> = {
  active: 'green',
  checked_out: 'gray',
  transferred: 'amber',
  cancelled: 'gray',
};

export function StudentAllocationPage() {
  const { user } = useAuth();
  const [students, setStudents] = useState<Student[]>([]);
  const [beds, setBeds] = useState<Bed[]>([]);
  const [form, setForm] = useState({ student_id: '', bed_id: '' });
  const [error, setError] = useState<string | null>(null);
  const [rows, setRows] = useState<Allocation[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const loadBeds = () =>
    hostelApi.beds
      .list({ filter: { school_id: user?.school_id, status: 'available' }, per_page: 500 })
      .then((r) => setBeds(r.data));

  const load = useMemo(
    () => () =>
      hostelApi.allocations({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );

  useEffect(() => {
    studentsApi.list({ per_page: 500, sort: 'name' }).then((r) => setStudents(r.data));
    loadBeds();
  }, [user?.school_id]); // eslint-disable-line react-hooks/exhaustive-deps
  useEffect(() => {
    load();
  }, [load]);

  const submit = async () => {
    setError(null);
    try {
      await hostelApi.allocate({
        student_id: Number(form.student_id),
        bed_id: Number(form.bed_id),
      });
      setForm({ student_id: '', bed_id: '' });
      loadBeds();
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not allocate.');
    }
  };

  const columns: AXColumn<Allocation>[] = [
    {
      key: 'student',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student?.name ?? r.student_id}</span>,
    },
    { key: 'hostel', header: 'Hostel', render: (r) => r.hostel?.name ?? '—' },
    { key: 'room', header: 'Room', render: (r) => r.room?.room_number ?? '—' },
    { key: 'bed', header: 'Bed', render: (r) => r.bed?.bed_number ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status.replace('_', ' ')}</AXBadge>
      ),
    },
    {
      key: 'act',
      header: '',
      render: (r) =>
        r.status === 'active' ? (
          <button
            onClick={() =>
              hostelApi.checkout(r.id).then(() => {
                loadBeds();
                load();
              })
            }
            className="text-xs font-semibold text-[var(--danger)]"
          >
            Checkout
          </button>
        ) : null,
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-bed text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Student Allocation</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-56">
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
        <div className="w-56">
          <AXSelect
            label="Available bed"
            value={form.bed_id}
            onChange={(e) => setForm((f) => ({ ...f, bed_id: e.target.value }))}
            options={[
              { value: '', label: 'Select…' },
              ...beds.map((b) => ({
                value: String(b.id),
                label: `${b.bed_number} (${b.bed_code ?? ''})`,
              })),
            ]}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.student_id || !form.bed_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          <i className="fas fa-check mr-1" /> Allocate
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No allocations yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
