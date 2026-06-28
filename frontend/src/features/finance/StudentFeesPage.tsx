/* Student Fees — assign fee structures (individual / class / bulk) and apply
 * discounts, scholarships and sibling concessions. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import {
  AXBadge,
  AXModal,
  AXPagination,
  AXSelect,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { type FieldOption } from '@features/academic/EntityManager';
import { useClasses } from '@features/academic/useReference';
import { studentsApi, type Student } from '@features/students/api';
import {
  financeApi,
  type Discount,
  type FeeStructure,
  type Scholarship,
  type StudentFee,
} from './api';

const TONES: Record<string, 'green' | 'amber' | 'gray' | 'navy'> = {
  paid: 'green',
  partial: 'amber',
  pending: 'gray',
  overdue: 'navy',
};

export function StudentFeesPage() {
  const { user } = useAuth();
  const classes = useClasses();
  const [rows, setRows] = useState<StudentFee[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);
  const [classId, setClassId] = useState('');
  const [assignOpen, setAssignOpen] = useState(false);
  const [concession, setConcession] = useState<StudentFee | null>(null);

  const load = useMemo(
    () => () => {
      financeApi
        .studentFees({ page, filter: { school_id: user?.school_id, class_id: classId } })
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        });
    },
    [page, classId, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<StudentFee>[] = [
    {
      key: 'student',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student}</span>,
    },
    {
      key: 'adm',
      header: 'Adm. No.',
      render: (r) => <code className="text-xs text-gray-500">{r.admission_number}</code>,
    },
    { key: 'total', header: 'Total', render: (r) => `₹${r.total_amount}` },
    {
      key: 'disc',
      header: 'Disc/Schol',
      render: (r) => `₹${r.discount_amount + r.scholarship_amount}`,
    },
    { key: 'net', header: 'Net', render: (r) => `₹${r.net_amount}` },
    { key: 'paid', header: 'Paid', render: (r) => `₹${r.paid_amount}` },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          onClick={() => setConcession(r)}
          className="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700"
        >
          Concessions
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-file-invoice-dollar text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Student Fees</h2>
        </div>
        <div className="flex items-end gap-2">
          <div className="w-44">
            <AXSelect
              value={classId}
              onChange={(e) => {
                setClassId(e.target.value);
                setPage(1);
              }}
              options={[{ value: '', label: 'All classes' }, ...classes]}
            />
          </div>
          <button
            onClick={() => setAssignOpen(true)}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
          >
            <i className="fas fa-plus mr-1" /> Assign
          </button>
        </div>
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No student fees yet." />
      <AXPagination meta={meta} onPage={setPage} />

      {assignOpen && (
        <AssignModal
          schoolId={user?.school_id}
          classes={classes}
          onClose={() => setAssignOpen(false)}
          onDone={() => {
            setAssignOpen(false);
            load();
          }}
        />
      )}
      {concession && (
        <ConcessionModal
          fee={concession}
          onClose={() => setConcession(null)}
          onDone={() => {
            setConcession(null);
            load();
          }}
        />
      )}
    </div>
  );
}

function AssignModal({
  schoolId,
  classes,
  onClose,
  onDone,
}: {
  schoolId: number | null | undefined;
  classes: FieldOption[];
  onClose: () => void;
  onDone: () => void;
}) {
  const [structures, setStructures] = useState<FieldOption[]>([]);
  const [structureId, setStructureId] = useState('');
  const [mode, setMode] = useState<'student' | 'class'>('student');
  const [classId, setClassId] = useState('');
  const [students, setStudents] = useState<Student[]>([]);
  const [studentId, setStudentId] = useState('');
  const [msg, setMsg] = useState<string | null>(null);

  useEffect(() => {
    financeApi.structures
      .list({ filter: { school_id: schoolId }, per_page: 200 })
      .then((r) =>
        setStructures(r.data.map((s: FeeStructure) => ({ value: String(s.id), label: s.name })))
      );
    studentsApi.list({ per_page: 300, sort: 'name' }).then((r) => setStudents(r.data));
  }, [schoolId]);

  const submit = async () => {
    setMsg(null);
    const payload: Record<string, unknown> = { structure_id: Number(structureId) };
    if (mode === 'student') payload.student_id = Number(studentId);
    else {
      payload.bulk = true;
      payload.class_id = Number(classId);
    }
    await financeApi.assignFee(payload);
    onDone();
  };

  return (
    <AXModal open title="Assign fee structure" onClose={onClose}>
      <div className="space-y-3">
        <AXSelect
          label="Fee structure"
          value={structureId}
          onChange={(e) => setStructureId(e.target.value)}
          options={[{ value: '', label: 'Select…' }, ...structures]}
        />
        <AXSelect
          label="Assign to"
          value={mode}
          onChange={(e) => setMode(e.target.value as 'student' | 'class')}
          options={[
            { value: 'student', label: 'Individual student' },
            { value: 'class', label: 'Whole class (bulk)' },
          ]}
        />
        {mode === 'student' ? (
          <AXSelect
            label="Student"
            value={studentId}
            onChange={(e) => setStudentId(e.target.value)}
            options={[
              { value: '', label: 'Select…' },
              ...students.map((s) => ({
                value: String(s.id),
                label: `${s.admission_number} — ${s.name}`,
              })),
            ]}
          />
        ) : (
          <AXSelect
            label="Class"
            value={classId}
            onChange={(e) => setClassId(e.target.value)}
            options={[{ value: '', label: 'Select…' }, ...classes]}
          />
        )}
        {msg && <AXBadge tone="green">{msg}</AXBadge>}
        <div className="flex justify-end gap-2">
          <button
            onClick={onClose}
            className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700"
          >
            Cancel
          </button>
          <button
            onClick={submit}
            disabled={!structureId || (mode === 'student' ? !studentId : !classId)}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            Assign
          </button>
        </div>
      </div>
    </AXModal>
  );
}

function ConcessionModal({
  fee,
  onClose,
  onDone,
}: {
  fee: StudentFee;
  onClose: () => void;
  onDone: () => void;
}) {
  const [discounts, setDiscounts] = useState<Discount[]>([]);
  const [scholarships, setScholarships] = useState<Scholarship[]>([]);
  const [discountId, setDiscountId] = useState('');
  const [scholarshipId, setScholarshipId] = useState('');

  useEffect(() => {
    financeApi.discounts.list({ per_page: 200 }).then((r) => setDiscounts(r.data));
    financeApi.scholarships.list({ per_page: 200 }).then((r) => setScholarships(r.data));
  }, []);

  const run = async (fn: () => Promise<unknown>) => {
    await fn();
    onDone();
  };

  return (
    <AXModal open title={`Concessions — ${fee.student}`} onClose={onClose}>
      <div className="space-y-4">
        <div className="flex items-end gap-2">
          <div className="flex-1">
            <AXSelect
              label="Discount"
              value={discountId}
              onChange={(e) => setDiscountId(e.target.value)}
              options={[
                { value: '', label: 'Select…' },
                ...discounts.map((d) => ({ value: String(d.id), label: d.name })),
              ]}
            />
          </div>
          <button
            onClick={() =>
              discountId && run(() => financeApi.applyDiscount(fee.id, Number(discountId)))
            }
            disabled={!discountId}
            className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 disabled:opacity-60"
          >
            Apply
          </button>
        </div>
        <div className="flex items-end gap-2">
          <div className="flex-1">
            <AXSelect
              label="Scholarship"
              value={scholarshipId}
              onChange={(e) => setScholarshipId(e.target.value)}
              options={[
                { value: '', label: 'Select…' },
                ...scholarships.map((s) => ({ value: String(s.id), label: s.name })),
              ]}
            />
          </div>
          <button
            onClick={() =>
              scholarshipId && run(() => financeApi.applyScholarship(fee.id, Number(scholarshipId)))
            }
            disabled={!scholarshipId}
            className="rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 disabled:opacity-60"
          >
            Award
          </button>
        </div>
        <button
          onClick={() => run(() => financeApi.applySibling(fee.id))}
          className="w-full rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
        >
          <i className="fas fa-children mr-1" /> Apply sibling concession (auto)
        </button>
      </div>
    </AXModal>
  );
}
