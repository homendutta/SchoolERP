/* Student List — enterprise search (admission no, name, guardian) + status/class
 * filters. Students are never created here; rows are editable (profile). */
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
import { useClasses } from '@features/academic/useReference';
import { studentsApi, type Student } from './api';

const STATUS = [
  'applied',
  'enrolled',
  'active',
  'promoted',
  'transferred',
  'withdrawn',
  'graduated',
  'archived',
];
const TONES: Record<string, 'navy' | 'green' | 'amber' | 'red' | 'gray'> = {
  active: 'green',
  promoted: 'navy',
  transferred: 'amber',
  withdrawn: 'red',
  graduated: 'navy',
  archived: 'gray',
};

export function StudentListPage() {
  const classes = useClasses();
  const [rows, setRows] = useState<Student[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 15,
  });
  const [loading, setLoading] = useState(false);
  const [q, setQ] = useState('');
  const [status, setStatus] = useState('');
  const [classId, setClassId] = useState('');
  const [page, setPage] = useState(1);
  const [edit, setEdit] = useState<Student | null>(null);
  const [form, setForm] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);

  const load = useMemo(
    () => () => {
      setLoading(true);
      const params: Record<string, unknown> = { page, sort: 'name' };
      if (q) params.search = { name: q, admission_number: q };
      const filter: Record<string, string> = {};
      if (status) filter.status = status;
      if (classId) filter.class_id = classId;
      if (Object.keys(filter).length) params.filter = filter;
      studentsApi
        .list(params)
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        })
        .finally(() => setLoading(false));
    },
    [q, status, classId, page]
  );

  useEffect(() => {
    load();
  }, [load]);

  const openEdit = (s: Student) => {
    setForm({
      name: s.name,
      phone: s.phone ?? '',
      email: s.email ?? '',
      city: s.city ?? '',
      state: s.state ?? '',
      address: s.address ?? '',
      notes: s.notes ?? '',
    });
    setEdit(s);
  };
  const save = async () => {
    if (!edit) return;
    setSaving(true);
    try {
      await studentsApi.update(edit.id, form);
      setEdit(null);
      load();
    } finally {
      setSaving(false);
    }
  };

  const columns: AXColumn<Student>[] = [
    {
      key: 'admission_number',
      header: 'Adm. No.',
      render: (r) => <code className="text-xs text-gray-500">{r.admission_number}</code>,
    },
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'class', header: 'Class', render: (r) => r.current_record?.class?.name ?? '—' },
    { key: 'section', header: 'Section', render: (r) => r.current_record?.section?.name ?? '—' },
    { key: 'guardian', header: 'Guardian', render: (r) => r.guardians?.[0]?.name ?? '—' },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'actions',
      header: '',
      className: 'text-right',
      render: (r) => (
        <button
          onClick={() => openEdit(r)}
          title="Edit profile"
          className="text-gray-500 hover:text-[var(--navy-accent)]"
        >
          <i className="fas fa-pen" />
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-user-graduate text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Students</h2>
          <AXBadge tone="navy">{meta.total} students</AXBadge>
        </div>
        <a
          href={studentsApi.exportUrl({ filter: { status, class_id: classId } })}
          className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
        >
          <i className="fas fa-file-export mr-1" /> Export CSV
        </a>
      </div>

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
              ...STATUS.map((s) => ({ value: s, label: s })),
            ]}
          />
        </div>
        <div className="w-52">
          <AXSelect
            value={classId}
            onChange={(e) => {
              setClassId(e.target.value);
              setPage(1);
            }}
            options={[{ value: '', label: 'Class: All' }, ...classes]}
          />
        </div>
        <div className="min-w-[16rem] flex-1">
          <AXSearch
            onSearch={(t) => {
              setQ(t);
              setPage(1);
            }}
            placeholder="Search admission no / name…"
          />
        </div>
      </div>

      <AXTable
        columns={columns}
        rows={rows}
        rowKey={(r) => r.id}
        loading={loading}
        empty="No students found."
      />
      <AXPagination meta={meta} onPage={setPage} />

      <AXModal open={edit !== null} title="Edit Student Profile" onClose={() => setEdit(null)}>
        <AXForm onSubmit={save} submitting={saving} onCancel={() => setEdit(null)}>
          <AXInput
            label="Name"
            value={form.name}
            onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
          />
          <AXInput
            label="Phone"
            value={form.phone}
            onChange={(e) => setForm((f) => ({ ...f, phone: e.target.value }))}
          />
          <AXInput
            label="Email"
            value={form.email}
            onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))}
          />
          <AXInput
            label="Address"
            value={form.address}
            onChange={(e) => setForm((f) => ({ ...f, address: e.target.value }))}
          />
          <AXInput
            label="City"
            value={form.city}
            onChange={(e) => setForm((f) => ({ ...f, city: e.target.value }))}
          />
          <AXInput
            label="State"
            value={form.state}
            onChange={(e) => setForm((f) => ({ ...f, state: e.target.value }))}
          />
          <AXInput
            label="Notes"
            value={form.notes}
            onChange={(e) => setForm((f) => ({ ...f, notes: e.target.value }))}
          />
        </AXForm>
      </AXModal>
    </div>
  );
}
