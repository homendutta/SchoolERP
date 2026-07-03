/* Document Verification — verify by number / code / QR (Identity Platform). */
import { useState } from 'react';
import { AXBadge, AXInput, AXSelect } from '@ui/ax';
import { VERIFY_METHODS, documentsApi } from './api';

export function DocumentVerificationPage() {
  const [form, setForm] = useState({ method: 'document_number', identifier: '' });
  const [result, setResult] = useState<Record<string, unknown> | null>(null);
  const [error, setError] = useState<string | null>(null);

  const verify = async () => {
    setError(null);
    setResult(null);
    try {
      const res = await documentsApi.verify({ method: form.method, identifier: form.identifier });
      setResult(res);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Verification failed.');
    }
  };

  const doc = (result?.document as Record<string, unknown>) ?? null;
  const verified = Boolean(result?.verified);

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-shield-halved text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Verify Document</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-48">
          <AXSelect
            label="Method"
            value={form.method}
            onChange={(e) => setForm((f) => ({ ...f, method: e.target.value }))}
            options={VERIFY_METHODS.map((m) => ({ value: m, label: m.replace(/_/g, ' ') }))}
          />
        </div>
        <div className="w-72">
          <AXInput
            label="Identifier (number / code / QR value)"
            value={form.identifier}
            onChange={(e) => setForm((f) => ({ ...f, identifier: e.target.value }))}
          />
        </div>
        <button
          onClick={verify}
          disabled={!form.identifier}
          className="rounded-md bg-[var(--navy-primary)] px-5 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Verify
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      {result && (
        <div className="erp-card space-y-2">
          <AXBadge tone={verified ? 'green' : 'red'}>
            {verified ? 'Valid document' : String(result.result ?? 'Invalid')}
          </AXBadge>
          {doc && (
            <dl className="grid grid-cols-2 gap-2 text-sm md:grid-cols-3">
              {Object.entries(doc).map(([k, v]) => (
                <div key={k}>
                  <dt className="text-xs text-gray-500">{k.replace(/_/g, ' ')}</dt>
                  <dd className="font-medium">{String(v ?? '—')}</dd>
                </div>
              ))}
            </dl>
          )}
        </div>
      )}
    </div>
  );
}
