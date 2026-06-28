/* Reusable identity detail card — shows immutable identity data plus a
 * dynamically-rendered QR and barcode, with enable/disable + regenerate. */
import { useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { identityApi, type Identity } from './api';

function Field({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div>
      <div className="text-xs uppercase tracking-wide text-gray-400">{label}</div>
      <div className="text-sm text-gray-800">{value || '—'}</div>
    </div>
  );
}

export function IdentityCard({
  identity,
  onChange,
}: {
  identity: Identity;
  onChange?: (i: Identity) => void;
}) {
  const [qr, setQr] = useState('');
  const [barcode, setBarcode] = useState('');
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    identityApi.qrSvg(identity.id).then(setQr);
    identityApi.barcodeSvg(identity.id).then(setBarcode);
  }, [identity.id, identity.barcode_value]);

  const regenerate = async () => {
    setBusy(true);
    try {
      const updated = await identityApi.regenerate(identity.id);
      onChange?.(updated);
      setQr(await identityApi.qrSvg(identity.id));
      setBarcode(await identityApi.barcodeSvg(identity.id));
    } finally {
      setBusy(false);
    }
  };

  const toggleStatus = async () => {
    setBusy(true);
    try {
      const next = identity.status === 'active' ? 'disabled' : 'active';
      onChange?.(await identityApi.setStatus(identity.id, next));
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="erp-card space-y-4">
      <div className="flex flex-wrap items-center gap-3">
        <span className="text-lg font-semibold text-[var(--navy-primary)]">
          #{identity.identity_number}
        </span>
        <AXBadge tone="navy">{identity.identity_type}</AXBadge>
        <AXBadge tone={identity.status === 'active' ? 'green' : 'gray'}>{identity.status}</AXBadge>
        <div className="ml-auto flex gap-2">
          <button
            onClick={regenerate}
            disabled={busy}
            className="rounded-md bg-gray-100 px-3 py-1.5 text-sm font-semibold text-gray-700 disabled:opacity-60"
          >
            <i className="fas fa-rotate mr-1" /> Regenerate
          </button>
          <button
            onClick={toggleStatus}
            disabled={busy}
            className="rounded-md px-3 py-1.5 text-sm font-semibold text-white disabled:opacity-60"
            style={{
              background: identity.status === 'active' ? 'var(--danger)' : 'var(--success)',
            }}
          >
            {identity.status === 'active' ? 'Disable' : 'Enable'}
          </button>
        </div>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        <Field label="Owner" value={identity.owner?.name} />
        <Field label="Owner Type" value={identity.identity_type} />
        <Field
          label="Public Identifier"
          value={<code className="text-xs">{identity.public_identifier}</code>}
        />
        <Field label="Created" value={identity.created_at?.slice(0, 10)} />
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <div>
          <div className="mb-1 text-xs uppercase tracking-wide text-gray-400">QR Code</div>
          <div
            className="inline-block rounded-md border border-gray-200 p-2"
            style={{ width: 180 }}
            dangerouslySetInnerHTML={{ __html: qr }}
          />
        </div>
        <div>
          <div className="mb-1 text-xs uppercase tracking-wide text-gray-400">Barcode</div>
          <div
            className="overflow-x-auto rounded-md border border-gray-200 p-2"
            dangerouslySetInnerHTML={{ __html: barcode }}
          />
        </div>
      </div>
    </div>
  );
}
