/* Leave Requests — apply here; every decision runs through the Leave Engine
 * (multi-level approval, balance tracking, timeline, audit, communication). */
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
import { staffApi } from '@features/staff/api';
import { hrApi, type Ref } from './api';

const TONES: Record<string, 'amber' | 'green' | 'red' | 'gray'> = {
  pending: 'amber',
  approved: 'green',
  rejected: 'red',
  cancelled: 'gray',
};

export function LeaveRequestsPage() {
  const { user } = useAuth();
  const [employees, setEmployees] = useState<Array<{ value: string; label: string }>>([]);
  const [types, setTypes] = useState<Array<{ value: string; label: string }>>([]);
  const [form, setForm] = useState({
    staff_id: '',
    leave_type_id: '',
    start_date: '',
    end_date: '',
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

  const load = useMemo(
    () => () =>
      hrApi.leaveRequests({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );

  useEffect(() => {
    staffApi.staff
      .list({ per_page: 500, sort: 'name' })
      .then((r) =>
        setEmployees(
          r.data.map((s) => ({ value: String(s.id), label: `${s.employee_number} — ${s.name}` }))
        )
      );
    hrApi.leaveTypes
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setTypes(r.data.map((t) => ({ value: String(t.id), label: String(t.name) }))));
  }, [user?.school_id]);

  useEffect(() => {
    load();
  }, [load]);

  const submit = async () => {
    setError(null);
    try {
      await hrApi.applyLeave({
        school_id: user?.school_id,
        staff_id: Number(form.staff_id),
        leave_type_id: Number(form.leave_type_id),
        start_date: form.start_date,
        end_date: form.end_date,
        reason: form.reason || null,
      });
      setForm({ staff_id: '', leave_type_id: '', start_date: '', end_date: '', reason: '' });
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not apply.');
    }
  };

  const act = (fn: Promise<unknown>) => fn.then(load).catch(() => undefined);

  const columns: AXColumn<Ref>[] = [
    {
      key: 'employee',
      header: 'Employee',
      render: (r) => (
        <span className="font-medium">
          {String((r.employee as { name?: string })?.name ?? r.staff_id)}
        </span>
      ),
    },
    {
      key: 'type',
      header: 'Type',
      render: (r) => String((r.leaveType as { name?: string })?.name ?? '—'),
    },
    { key: 'dates', header: 'Dates', render: (r) => `${r.start_date} → ${r.end_date}` },
    { key: 'days', header: 'Days', render: (r) => String(r.days) },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>,
    },
    {
      key: 'act',
      header: '',
      render: (r) =>
        r.status === 'pending' ? (
          <div className="flex gap-2 text-xs font-semibold">
            <button onClick={() => act(hrApi.approveLeave(r.id))} className="text-[var(--success)]">
              Approve
            </button>
            <button onClick={() => act(hrApi.rejectLeave(r.id))} className="text-[var(--danger)]">
              Reject
            </button>
            <button onClick={() => act(hrApi.cancelLeave(r.id))} className="text-gray-500">
              Cancel
            </button>
          </div>
        ) : null,
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-plane-departure text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Leave Requests</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-56">
          <AXSelect
            label="Employee"
            value={form.staff_id}
            onChange={(e) => setForm((f) => ({ ...f, staff_id: e.target.value }))}
            options={[{ value: '', label: 'Select…' }, ...employees]}
          />
        </div>
        <div className="w-44">
          <AXSelect
            label="Leave type"
            value={form.leave_type_id}
            onChange={(e) => setForm((f) => ({ ...f, leave_type_id: e.target.value }))}
            options={[{ value: '', label: 'Select…' }, ...types]}
          />
        </div>
        <div className="w-40">
          <AXInput
            label="Start"
            type="date"
            value={form.start_date}
            onChange={(e) => setForm((f) => ({ ...f, start_date: e.target.value }))}
          />
        </div>
        <div className="w-40">
          <AXInput
            label="End"
            type="date"
            value={form.end_date}
            onChange={(e) => setForm((f) => ({ ...f, end_date: e.target.value }))}
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
          disabled={!form.staff_id || !form.leave_type_id || !form.start_date || !form.end_date}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Apply
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No leave requests yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
