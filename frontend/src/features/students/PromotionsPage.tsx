/* Student Promotion — creates a NEW academic record (history is immutable). */
import { useState } from 'react';
import { AXBadge, AXForm, AXInput, AXSelect } from '@ui/ax';
import { useYears, useClasses, useAllSections } from '@features/academic/useReference';
import { studentsApi } from './api';
import { StudentPicker, useStudentList } from './StudentPicker';

const EMPTY = { academic_year_id: '', class_id: '', section_id: '', roll_number: '' };

export function PromotionsPage() {
  const students = useStudentList();
  const years = useYears();
  const classes = useClasses();
  const sections = useAllSections();
  const [id, setId] = useState('');
  const [form, setForm] = useState<Record<string, string>>(EMPTY);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [done, setDone] = useState(false);

  const save = async () => {
    setSaving(true);
    setError(null);
    try {
      await studentsApi.promote(Number(id), {
        academic_year_id: Number(form.academic_year_id),
        class_id: Number(form.class_id),
        section_id: form.section_id ? Number(form.section_id) : null,
        roll_number: form.roll_number || null,
      });
      setDone(true);
      setForm(EMPTY);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-arrow-up-right-dots text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Promotions</h2>
        </div>
        <StudentPicker
          value={id}
          onChange={(v) => {
            setId(v);
            setDone(false);
          }}
          students={students}
        />
      </div>

      {id && (
        <div className="erp-card">
          {done && (
            <div className="mb-3">
              <AXBadge tone="green">Student promoted — a new academic record was created.</AXBadge>
            </div>
          )}
          {error && (
            <div className="mb-3 rounded-md bg-red-50 px-3 py-2 text-sm text-[var(--danger)]">
              {error}
            </div>
          )}
          <AXForm onSubmit={save} submitting={saving} submitLabel="Promote Student">
            <AXSelect
              label="To academic year"
              value={form.academic_year_id}
              onChange={(e) => setForm((f) => ({ ...f, academic_year_id: e.target.value }))}
              options={[{ value: '', label: '—' }, ...years]}
            />
            <AXSelect
              label="To class"
              value={form.class_id}
              onChange={(e) => setForm((f) => ({ ...f, class_id: e.target.value }))}
              options={[{ value: '', label: '—' }, ...classes]}
            />
            <AXSelect
              label="To section"
              value={form.section_id}
              onChange={(e) => setForm((f) => ({ ...f, section_id: e.target.value }))}
              options={[{ value: '', label: '—' }, ...sections]}
            />
            <AXInput
              label="Roll number"
              value={form.roll_number}
              onChange={(e) => setForm((f) => ({ ...f, roll_number: e.target.value }))}
            />
          </AXForm>
        </div>
      )}
    </div>
  );
}
