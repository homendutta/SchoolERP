/* Website Settings — per-school singleton (site identity, contact, social, footer). */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXInput } from '@ui/ax';
import { cmsApi } from './api';

export function WebsiteSettingsPage() {
  const { user } = useAuth();
  const [form, setForm] = useState<Record<string, string>>({
    site_name: '',
    email: '',
    phone: '',
    address: '',
    footer: '',
    copyright: '',
    google_map: '',
    logo_media_id: '',
    favicon_media_id: '',
    facebook: '',
    instagram: '',
    youtube: '',
  });
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    cmsApi.getSettings(user?.school_id ?? undefined).then((s) => {
      const social = (s.social_links as Record<string, string> | null) ?? {};
      setForm((f) => ({
        ...f,
        site_name: (s.site_name as string) ?? '',
        email: (s.email as string) ?? '',
        phone: (s.phone as string) ?? '',
        address: (s.address as string) ?? '',
        footer: (s.footer as string) ?? '',
        copyright: (s.copyright as string) ?? '',
        google_map: (s.google_map as string) ?? '',
        logo_media_id: s.logo_media_id ? String(s.logo_media_id) : '',
        favicon_media_id: s.favicon_media_id ? String(s.favicon_media_id) : '',
        facebook: social.facebook ?? '',
        instagram: social.instagram ?? '',
        youtube: social.youtube ?? '',
      }));
    });
  }, [user?.school_id]);

  const set = (k: string) => (e: React.ChangeEvent<HTMLInputElement>) =>
    setForm((f) => ({ ...f, [k]: e.target.value }));

  const save = async () => {
    setError(null);
    setSaved(false);
    try {
      await cmsApi.saveSettings({
        school_id: user?.school_id,
        site_name: form.site_name || null,
        email: form.email || null,
        phone: form.phone || null,
        address: form.address || null,
        footer: form.footer || null,
        copyright: form.copyright || null,
        google_map: form.google_map || null,
        logo_media_id: form.logo_media_id ? Number(form.logo_media_id) : null,
        favicon_media_id: form.favicon_media_id ? Number(form.favicon_media_id) : null,
        social_links: { facebook: form.facebook, instagram: form.instagram, youtube: form.youtube },
      });
      setSaved(true);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not save settings.');
    }
  };

  const group = (title: string, keys: Array<[string, string]>) => (
    <div className="erp-card space-y-3">
      <h3 className="text-sm font-semibold text-[var(--navy-primary)]">{title}</h3>
      <div className="grid gap-3 md:grid-cols-2">
        {keys.map(([k, label]) => (
          <AXInput key={k} label={label} value={form[k]} onChange={set(k)} />
        ))}
      </div>
    </div>
  );

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-sliders text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Website Settings</h2>
      </div>

      {group('Identity', [
        ['site_name', 'Site name'],
        ['logo_media_id', 'Logo (Media id)'],
        ['favicon_media_id', 'Favicon (Media id)'],
        ['copyright', 'Copyright'],
      ])}
      {group('Contact', [
        ['email', 'Email'],
        ['phone', 'Phone'],
        ['address', 'Address'],
        ['google_map', 'Google Map embed URL'],
      ])}
      {group('Social links', [
        ['facebook', 'Facebook'],
        ['instagram', 'Instagram'],
        ['youtube', 'YouTube'],
      ])}
      {group('Footer', [['footer', 'Footer text']])}

      <div className="flex items-center gap-3">
        <button
          onClick={save}
          className="rounded-md bg-[var(--navy-primary)] px-5 py-2 text-sm font-semibold text-white"
        >
          Save settings
        </button>
        {saved && <AXBadge tone="green">Saved</AXBadge>}
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>
    </div>
  );
}
