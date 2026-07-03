/* Portal Profile — update contact details + password (photo via Media id). */
import { useEffect, useState } from 'react';
import { AXBadge, AXInput } from '@ui/ax';
import { portalApi } from './api';
import { usePortal } from './usePortal';
import { PortalShell } from './PortalShell';

export function PortalProfilePage() {
  const { context, error } = usePortal();
  const [form, setForm] = useState<Record<string, string>>({
    phone: '',
    address: '',
    photo_media_id: '',
    password: '',
  });
  const [status, setStatus] = useState<string | null>(null);

  useEffect(() => {
    portalApi.profile().then((p) =>
      setForm((f) => ({
        ...f,
        phone: (p.phone as string) ?? '',
        address: (p.address as string) ?? '',
        photo_media_id: p.photo_media_id ? String(p.photo_media_id) : '',
      }))
    );
  }, []);

  const set = (k: string) => (e: React.ChangeEvent<HTMLInputElement>) =>
    setForm((f) => ({ ...f, [k]: e.target.value }));

  const save = async () => {
    setStatus(null);
    try {
      await portalApi.updateProfile({
        phone: form.phone || null,
        address: form.address || null,
        photo_media_id: form.photo_media_id ? Number(form.photo_media_id) : null,
        password: form.password || undefined,
      });
      setForm((f) => ({ ...f, password: '' }));
      setStatus('Saved.');
    } catch (e) {
      setStatus(e instanceof Error ? e.message : 'Could not save.');
    }
  };

  return (
    <PortalShell
      title="My Profile"
      icon="user"
      context={context}
      studentId={null}
      requiresStudent={false}
      error={error}
    >
      <div className="erp-card grid max-w-xl gap-3">
        <AXInput label="Phone" value={form.phone} onChange={set('phone')} />
        <AXInput label="Address" value={form.address} onChange={set('address')} />
        <AXInput
          label="Profile photo (Media id)"
          value={form.photo_media_id}
          onChange={set('photo_media_id')}
        />
        <AXInput
          label="New password"
          type="password"
          value={form.password}
          onChange={set('password')}
        />
        <div className="flex items-center gap-3">
          <button
            onClick={save}
            className="rounded-md bg-[var(--navy-primary)] px-5 py-2 text-sm font-semibold text-white"
          >
            Save
          </button>
          {status && <AXBadge tone="navy">{status}</AXBadge>}
        </div>
      </div>
    </PortalShell>
  );
}
