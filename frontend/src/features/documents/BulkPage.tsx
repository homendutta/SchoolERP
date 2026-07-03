/* Bulk Generation — queue documents for a class / section / academic year / dept. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXSelect } from '@ui/ax';
import { BULK_SCOPES, SUBJECT_KINDS, documentsApi, type Ref } from './api';

export function BulkPage() {
  const { user } = useAuth();
  const [templates, setTemplates] = useState<Ref[]>([]);
  const [form, setForm] = useState({
    template_id: '',
    subject_kind: 'student',
    scope: 'class',
    target_id: '',
  });
  const [status, setStatus] = useState<string | null>(null);

  useEffect(() => {
    documentsApi.templates
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setTemplates(r.data));
  }, [user?.school_id]);

  const targetKey = () =>
    ({
      class: 'class_id',
      section: 'section_id',
      academic_year: 'academic_year_id',
      department: 'department_id',
    })[form.scope] ?? 'class_id';

  const submit = async () => {
    setStatus(null);
    try {
      const res = await documentsApi.bulk({
        template_id: Number(form.template_id),
        subject_kind: form.subject_kind,
        scope: form.scope,
        target: { [targetKey()]: Number(form.target_id) },
      });
      setStatus(`Queued ${String(res.queued ?? 0)} document(s).`);
    } catch (e) {
      setStatus(e instanceof Error ? e.message : 'Bulk generation failed.');
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-layer-group text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Bulk Generation</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-64">
          <AXSelect
            label="Template"
            value={form.template_id}
            onChange={(e) => setForm((f) => ({ ...f, template_id: e.target.value }))}
            options={[
              { value: '', label: 'Select…' },
              ...templates.map((t) => ({
                value: String(t.id),
                label: `${String(t.name)} v${String(t.version)}`,
              })),
            ]}
          />
        </div>
        <div className="w-40">
          <AXSelect
            label="Subject"
            value={form.subject_kind}
            onChange={(e) => setForm((f) => ({ ...f, subject_kind: e.target.value }))}
            options={SUBJECT_KINDS.map((s) => ({ value: s, label: s }))}
          />
        </div>
        <div className="w-40">
          <AXSelect
            label="Scope"
            value={form.scope}
            onChange={(e) => setForm((f) => ({ ...f, scope: e.target.value }))}
            options={BULK_SCOPES.map((s) => ({ value: s, label: s.replace(/_/g, ' ') }))}
          />
        </div>
        <div className="w-40">
          <AXInput
            label={`${targetKey().replace(/_/g, ' ')}`}
            type="number"
            value={form.target_id}
            onChange={(e) => setForm((f) => ({ ...f, target_id: e.target.value }))}
          />
        </div>
        <button
          onClick={submit}
          disabled={!form.template_id || !form.target_id}
          className="rounded-md bg-[var(--navy-primary)] px-5 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Queue generation
        </button>
        {status && <AXBadge tone="navy">{status}</AXBadge>}
      </div>
      <p className="text-xs text-gray-500">
        Large runs are processed on the queue so the request never blocks.
      </p>
    </div>
  );
}
