/* Student Medical — blood group from Master Data, allergies, disabilities,
 * medical notes and emergency instructions. */
import { useEffect, useState } from 'react';
import { AXForm, AXInput, AXSelect } from '@ui/ax';
import { useMasterValues } from '@features/academic/useReference';
import { studentsApi } from './api';
import { StudentPicker, useStudentList } from './StudentPicker';

const EMPTY = {
  blood_group_id: '',
  allergies: '',
  disabilities: '',
  medical_notes: '',
  emergency_instructions: '',
};

export function MedicalPage() {
  const students = useStudentList();
  const bloodGroups = useMasterValues('blood_groups');
  const [id, setId] = useState('');
  const [form, setForm] = useState<Record<string, string>>(EMPTY);
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);

  useEffect(() => {
    setSaved(false);
    if (!id) {
      setForm(EMPTY);
      return;
    }
    studentsApi.get(Number(id)).then((s) =>
      setForm({
        blood_group_id: s.blood_group_id ? String(s.blood_group_id) : '',
        allergies: s.allergies ?? '',
        disabilities: s.disabilities ?? '',
        medical_notes: s.medical_notes ?? '',
        emergency_instructions: s.emergency_instructions ?? '',
      })
    );
  }, [id]);

  const save = async () => {
    setSaving(true);
    try {
      await studentsApi.updateMedical(Number(id), {
        ...form,
        blood_group_id: form.blood_group_id ? Number(form.blood_group_id) : null,
      });
      setSaved(true);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-notes-medical text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Medical</h2>
        </div>
        <StudentPicker value={id} onChange={setId} students={students} />
      </div>

      {id && (
        <div className="erp-card">
          {saved && (
            <div className="mb-3 rounded-md bg-green-50 px-3 py-2 text-sm text-[var(--success)]">
              Saved.
            </div>
          )}
          <AXForm onSubmit={save} submitting={saving} submitLabel="Save Medical Info">
            <AXSelect
              label="Blood group"
              value={form.blood_group_id}
              onChange={(e) => setForm((f) => ({ ...f, blood_group_id: e.target.value }))}
              options={[{ value: '', label: '—' }, ...bloodGroups]}
            />
            <AXInput
              label="Allergies"
              value={form.allergies}
              onChange={(e) => setForm((f) => ({ ...f, allergies: e.target.value }))}
            />
            <AXInput
              label="Disabilities"
              value={form.disabilities}
              onChange={(e) => setForm((f) => ({ ...f, disabilities: e.target.value }))}
            />
            <AXInput
              label="Medical notes"
              value={form.medical_notes}
              onChange={(e) => setForm((f) => ({ ...f, medical_notes: e.target.value }))}
            />
            <AXInput
              label="Emergency instructions"
              value={form.emergency_instructions}
              onChange={(e) => setForm((f) => ({ ...f, emergency_instructions: e.target.value }))}
            />
          </AXForm>
        </div>
      )}
    </div>
  );
}
