/* Identity Details — look up a single identity by number / public identifier and
 * view its QR + barcode. Immutable fields are read-only. */
import { useState } from 'react';
import { AXInput } from '@ui/ax';
import { identityApi, type Identity } from './api';
import { IdentityCard } from './IdentityCard';

export function IdentityDetailsPage() {
  const [term, setTerm] = useState('');
  const [identity, setIdentity] = useState<Identity | null>(null);
  const [error, setError] = useState<string | null>(null);

  const lookup = async () => {
    setError(null);
    setIdentity(null);
    if (!term.trim()) return;
    const res = await identityApi.search({
      search: { identity_number: term, public_identifier: term },
    });
    const found =
      res.data.find(
        (i) => i.identity_number === term.trim() || i.public_identifier === term.trim()
      ) ?? res.data[0];
    if (found) setIdentity(found);
    else setError('No identity found for that number / public identifier.');
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-id-card text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Identity Details</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="flex-1">
          <AXInput
            label="Identity number or public identifier"
            value={term}
            onChange={(e) => setTerm(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && lookup()}
            placeholder="e.g. 1 or id_xxxxxxxx"
          />
        </div>
        <button
          onClick={lookup}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white hover:bg-[var(--navy-hover)]"
        >
          <i className="fas fa-magnifying-glass mr-1" /> Look up
        </button>
      </div>

      {error && (
        <div className="rounded-md bg-red-50 px-3 py-2 text-sm text-[var(--danger)]">{error}</div>
      )}
      {identity && <IdentityCard identity={identity} onChange={setIdentity} />}
    </div>
  );
}
