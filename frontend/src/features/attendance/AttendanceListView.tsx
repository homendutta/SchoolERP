/* Shared attendance list (student or staff) with search, filters and authorized
 * correction. Reads the same unified engine table. */
import { useEffect, useMemo, useState } from 'react';
import {
  AXBadge,
  AXForm,
  AXInput,
  AXModal,
  AXPagination,
  AXSearch,
  AXSelect,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { attendanceApi, ATTENDANCE_STATUS, type AttendanceRecord } from './api';

const TONES: Record<string, 'navy' | 'green' | 'amber' | 'red' | 'gray'> = {
  present: 'green',
  late: 'amber',
  half_day: 'amber',
  absent: 'red',
  leave: 'navy',
  holiday: 'gray',
  weekend: 'gray',
};

export function AttendanceListView({ kind }: { kind: 'student' | 'staff' }) {
  const [rows, setRows] = useState<AttendanceRecord[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 15,
  });
  const [loading, setLoading] = useState(false);
  const [q, setQ] = useState('');
  const [status, setStatus] = useState('');
  const [date, setDate] = useState('');
  const [page, setPage] = useState(1);
  const [edit, setEdit] = useState<AttendanceRecord | null>(null);
  const [form, setForm] = useState({ status: '', remarks: '' });
  const [saving, setSaving] = useState(false);

  const load = useMemo(
    () => () => {
      setLoading(true);
      const params: Record<string, unknown> = { page, sort: 'attendance_date' };
      const search: Record<string, string> = {};
      if (q) search.identity_number = q;
      if (status) search.status = status;
      if (date) search.attendance_date = date;
      if (Object.keys(search).length) params.search = search;
      const fetch = kind === 'student' ? attendanceApi.student : attendanceApi.staff;
      fetch(params)
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        })
        .finally(() => setLoading(false));
    },
    [kind, q, status, date, page]
  );

  useEffect(() => {
    load();
  }, [load]);

  const save = async () => {
    if (!edit) return;
    setSaving(true);
    try {
      await attendanceApi.correct(edit.id, form);
      setEdit(null);
      load();
    } finally {
      setSaving(false);
    }
  };

  const columns: AXColumn<AttendanceRecord>[] = [
    {
      key: 'identity_number',
      header: 'Identity',
      render: (r) => <code className="text-xs text-gray-500">{r.identity_number}</code>,
    },
    {
      key: 'owner',
      header: kind === 'student' ? 'Student' : 'Staff',
      render: (r) => <span className="font-medium">{r.owner?.name ?? '—'}</span>,
    },
    { key: 'attendance_date', header: 'Date', render: (r) => r.attendance_date ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    { key: 'source', header: 'Source', render: (r) => <AXBadge tone="navy">{r.source}</AXBadge> },
    { key: 'check_in', header: 'In', render: (r) => r.check_in_time ?? '—' },
    {
      key: 'actions',
      header: '',
      className: 'text-right',
      render: (r) => (
        <button
          onClick={() => {
            setForm({ status: r.status, remarks: r.remarks ?? '' });
            setEdit(r);
          }}
          title="Correct"
          className="text-gray-500 hover:text-[var(--navy-accent)]"
        >
          <i className="fas fa-pen" />
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center gap-3">
        <div className="w-44">
          <AXSelect
            value={status}
            onChange={(e) => {
              setStatus(e.target.value);
              setPage(1);
            }}
            options={[
              { value: '', label: 'Status: All' },
              ...ATTENDANCE_STATUS.map((s) => ({ value: s, label: s })),
            ]}
          />
        </div>
        <div className="w-44">
          <AXInput
            type="date"
            value={date}
            onChange={(e) => {
              setDate(e.target.value);
              setPage(1);
            }}
          />
        </div>
        <div className="min-w-[14rem] flex-1">
          <AXSearch
            onSearch={(t) => {
              setQ(t);
              setPage(1);
            }}
            placeholder="Search identity number…"
          />
        </div>
      </div>

      <AXTable
        columns={columns}
        rows={rows}
        rowKey={(r) => r.id}
        loading={loading}
        empty="No attendance records."
      />
      <AXPagination meta={meta} onPage={setPage} />

      <AXModal open={edit !== null} title="Correct Attendance" onClose={() => setEdit(null)}>
        <AXForm
          onSubmit={save}
          submitting={saving}
          onCancel={() => setEdit(null)}
          submitLabel="Save Correction"
        >
          <AXSelect
            label="Status"
            value={form.status}
            onChange={(e) => setForm((f) => ({ ...f, status: e.target.value }))}
            options={ATTENDANCE_STATUS.map((s) => ({ value: s, label: s }))}
          />
          <AXInput
            label="Remarks"
            value={form.remarks}
            onChange={(e) => setForm((f) => ({ ...f, remarks: e.target.value }))}
          />
        </AXForm>
      </AXModal>
    </div>
  );
}
