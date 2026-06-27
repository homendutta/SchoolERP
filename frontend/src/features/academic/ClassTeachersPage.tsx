/* Class Teacher Assignment — only one active per Academic Year / Class / Section.
 * Assigning a new teacher supersedes the current one; full history is preserved. */
import { useEffect, useMemo, useState } from 'react';
import { AXBadge, AXForm, AXModal, AXSelect, AXStatus, AXTable, type AXColumn } from '@ui/ax';
import { academicApi, type ClassTeacher } from './api';
import { useAllSections, useClasses, useTeachers, useYears } from './useReference';

export function ClassTeachersPage() {
  const years = useYears();
  const classes = useClasses();
  const sections = useAllSections();
  const teachers = useTeachers();

  const [rows, setRows] = useState<ClassTeacher[]>([]);
  const [loading, setLoading] = useState(false);
  const [showHistory, setShowHistory] = useState(false);
  const [filter, setFilter] = useState({ academic_year_id: '', class_id: '', section_id: '' });
  const [modal, setModal] = useState(false);
  const [form, setForm] = useState({
    academic_year_id: '',
    class_id: '',
    section_id: '',
    teacher_id: '',
  });
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const params = useMemo(() => {
    const p: Record<string, string> = {};
    Object.entries(filter).forEach(([k, v]) => v && (p[k] = v));
    return p;
  }, [filter]);

  const scoped = filter.academic_year_id && filter.class_id && filter.section_id;

  const load = useMemo(
    () => () => {
      setLoading(true);
      const req =
        showHistory && scoped
          ? academicApi.classTeachers.history(params)
          : academicApi.classTeachers.list(params);
      req.then((data) => setRows(Array.isArray(data) ? data : [])).finally(() => setLoading(false));
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [JSON.stringify(params), showHistory, scoped]
  );

  useEffect(() => {
    load();
  }, [load]);

  const teacherName = (id: number) =>
    teachers.find((t) => t.value === String(id))?.label ?? `#${id}`;

  const save = async () => {
    setSaving(true);
    setError(null);
    try {
      await academicApi.classTeachers.assign(form);
      setModal(false);
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Assign failed.');
    } finally {
      setSaving(false);
    }
  };

  const columns: AXColumn<ClassTeacher>[] = [
    {
      key: 'teacher',
      header: 'Teacher',
      render: (r) => (
        <span className="font-medium">{r.teacher?.name ?? teacherName(r.teacher_id)}</span>
      ),
    },
    { key: 'assigned_on', header: 'Assigned', render: (r) => r.assigned_on ?? '—' },
    { key: 'ended_on', header: 'Ended', render: (r) => r.ended_on ?? '—' },
    {
      key: 'is_active',
      header: 'Status',
      render: (r) => (
        <AXStatus active={r.is_active} activeLabel="Active" inactiveLabel="Superseded" />
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-user-tie text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Class Teachers</h2>
          <AXBadge tone="navy">{rows.length} shown</AXBadge>
        </div>
        <button
          onClick={() => {
            setForm({ ...filter, teacher_id: '' });
            setError(null);
            setModal(true);
          }}
          className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white hover:bg-[var(--navy-hover)]"
        >
          <i className="fas fa-plus mr-1" /> Assign
        </button>
      </div>

      <div className="flex flex-wrap items-center gap-3">
        <div className="w-48">
          <AXSelect
            value={filter.academic_year_id}
            onChange={(e) => setFilter((f) => ({ ...f, academic_year_id: e.target.value }))}
            options={[{ value: '', label: 'Year: All' }, ...years]}
          />
        </div>
        <div className="w-48">
          <AXSelect
            value={filter.class_id}
            onChange={(e) => setFilter((f) => ({ ...f, class_id: e.target.value }))}
            options={[{ value: '', label: 'Class: All' }, ...classes]}
          />
        </div>
        <div className="w-48">
          <AXSelect
            value={filter.section_id}
            onChange={(e) => setFilter((f) => ({ ...f, section_id: e.target.value }))}
            options={[{ value: '', label: 'Section: All' }, ...sections]}
          />
        </div>
        <label className="flex items-center gap-2 text-sm text-gray-600">
          <input
            type="checkbox"
            checked={showHistory}
            onChange={(e) => setShowHistory(e.target.checked)}
            disabled={!scoped}
          />
          Show full history{' '}
          {!scoped && <span className="text-xs text-gray-400">(pick year + class + section)</span>}
        </label>
      </div>

      <AXTable
        columns={columns}
        rows={rows}
        rowKey={(r) => r.id}
        loading={loading}
        empty="No class-teacher assignments."
      />

      <AXModal open={modal} title="Assign Class Teacher" onClose={() => setModal(false)}>
        <AXForm
          onSubmit={save}
          submitting={saving}
          onCancel={() => setModal(false)}
          submitLabel="Assign"
        >
          {error && (
            <div className="rounded-md bg-red-50 px-3 py-2 text-sm text-[var(--danger)]">
              {error}
            </div>
          )}
          <AXSelect
            label="Academic year"
            value={form.academic_year_id}
            onChange={(e) => setForm((f) => ({ ...f, academic_year_id: e.target.value }))}
            options={[{ value: '', label: '—' }, ...years]}
          />
          <AXSelect
            label="Class"
            value={form.class_id}
            onChange={(e) => setForm((f) => ({ ...f, class_id: e.target.value }))}
            options={[{ value: '', label: '—' }, ...classes]}
          />
          <AXSelect
            label="Section"
            value={form.section_id}
            onChange={(e) => setForm((f) => ({ ...f, section_id: e.target.value }))}
            options={[{ value: '', label: '—' }, ...sections]}
          />
          <AXSelect
            label="Teacher"
            value={form.teacher_id}
            onChange={(e) => setForm((f) => ({ ...f, teacher_id: e.target.value }))}
            options={[{ value: '', label: '—' }, ...teachers]}
          />
        </AXForm>
      </AXModal>
    </div>
  );
}
