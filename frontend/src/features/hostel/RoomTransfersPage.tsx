/* Room Transfers — move a student to a new bed; new records, full history. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import {
  AXBadge,
  AXInput,
  AXPagination,
  AXSelect,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { studentsApi, type Student } from '@features/students/api';
import { TRANSFER_TYPES, hostelApi, type Bed, type Ref } from './api';

export function RoomTransfersPage() {
  const { user } = useAuth();
  const [students, setStudents] = useState<Student[]>([]);
  const [beds, setBeds] = useState<Bed[]>([]);
  const [form, setForm] = useState({
    student_id: '',
    to_bed_id: '',
    transfer_type: 'bed',
    reason: '',
  });
  const [error, setError] = useState<string | null>(null);
  const [rows, setRows] = useState<Ref[]>([]);
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
      hostelApi.transfers({ page, filter: { school_id: user?.school_id } }).then((r) => {
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
      await hostelApi.transfer({
        student_id: Number(form.student_id),
        to_bed_id: Number(form.to_bed_id),
        transfer_type: form.transfer_type,
        reason: form.reason || null,
      });
      setForm({ student_id: '', to_bed_id: '', transfer_type: 'bed', reason: '' });
      loadBeds();
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not transfer.');
    }
  };

  const columns: AXColumn<Ref>[] = [
    {
      key: 'student',
      header: 'Student',
      render: (r) => (
        <span className="font-medium">
          {String((r.student as { name?: string })?.name ?? r.student_id)}
        </span>
      ),
    },
    {
      key: 'type',
      header: 'Type',
      render: (r) => <AXBadge tone="navy">{String(r.transfer_type ?? '—')}</AXBadge>,
    },
    { key: 'date', header: 'Date', render: (r) => String(r.transfer_date ?? '—') },
    { key: 'reason', header: 'Reason', render: (r) => String(r.reason ?? '—') },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-right-left text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Room Transfers</h2>
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
        <div className="w-52">
          <AXSelect
            label="New bed"
            value={form.to_bed_id}
            onChange={(e) => setForm((f) => ({ ...f, to_bed_id: e.target.value }))}
            options={[
              { value: '', label: 'Select…' },
              ...beds.map((b) => ({
                value: String(b.id),
                label: `${b.bed_number} (${b.bed_code ?? ''})`,
              })),
            ]}
          />
        </div>
        <div className="w-40">
          <AXSelect
            label="Type"
            value={form.transfer_type}
            onChange={(e) => setForm((f) => ({ ...f, transfer_type: e.target.value }))}
            options={TRANSFER_TYPES.map((t) => ({ value: t, label: t }))}
          />
        </div>
        <div className="w-44">
          <AXInput
            label="Reason"
            value={form.reason}
            onChange={(e) => setForm((f) => ({ ...f, reason: e.target.value }))}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.student_id || !form.to_bed_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Transfer
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No transfers yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
