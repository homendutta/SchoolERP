/* Generate Documents — preview + generate an immutable document from a template. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput, AXSelect } from '@ui/ax';
import { SUBJECT_KINDS, documentsApi, type Ref } from './api';

export function GeneratePage() {
  const { user } = useAuth();
  const [templates, setTemplates] = useState<Ref[]>([]);
  const [form, setForm] = useState({
    template_id: '',
    subject_kind: 'student',
    subject_id: '',
    issued_to: '',
  });
  const [preview, setPreview] = useState<string | null>(null);
  const [status, setStatus] = useState<string | null>(null);

  useEffect(() => {
    documentsApi.templates
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setTemplates(r.data));
  }, [user?.school_id]);

  const doPreview = async () => {
    setStatus(null);
    try {
      const res = await documentsApi.preview({
        template_id: Number(form.template_id),
        subject_kind: form.subject_kind,
        subject_id: form.subject_id ? Number(form.subject_id) : undefined,
      });
      setPreview(String(res.html ?? ''));
    } catch (e) {
      setStatus(e instanceof Error ? e.message : 'Preview failed.');
    }
  };

  const doGenerate = async () => {
    setStatus(null);
    try {
      const doc = await documentsApi.generate({
        template_id: Number(form.template_id),
        subject_kind: form.subject_kind,
        subject_id: Number(form.subject_id),
        issued_to: form.issued_to || undefined,
      });
      setStatus(
        `Generated ${String(doc.document_number)} (code ${String(doc.verification_code)}).`
      );
    } catch (e) {
      setStatus(e instanceof Error ? e.message : 'Generation failed.');
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-file-circle-plus text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Generate Document</h2>
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
        <div className="w-32">
          <AXInput
            label="Subject id"
            type="number"
            value={form.subject_id}
            onChange={(e) => setForm((f) => ({ ...f, subject_id: e.target.value }))}
          />
        </div>
        <div className="w-44">
          <AXInput
            label="Issued to"
            value={form.issued_to}
            onChange={(e) => setForm((f) => ({ ...f, issued_to: e.target.value }))}
          />
        </div>
        <button
          onClick={doPreview}
          disabled={!form.template_id}
          className="rounded-md border border-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-[var(--navy-primary)] disabled:opacity-60"
        >
          Preview
        </button>
        <button
          onClick={doGenerate}
          disabled={!form.template_id || !form.subject_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Generate
        </button>
        {status && <AXBadge tone="navy">{status}</AXBadge>}
      </div>

      {preview !== null && (
        <div className="erp-card">
          <h3 className="mb-2 text-sm font-semibold text-[var(--navy-primary)]">Preview</h3>
          <div
            className="rounded border border-gray-200 bg-white p-4 text-sm"
            dangerouslySetInnerHTML={{ __html: preview }}
          />
        </div>
      )}
    </div>
  );
}
