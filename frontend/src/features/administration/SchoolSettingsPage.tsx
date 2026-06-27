/* School Settings — tabbed editor (General · Branding · Contact · Academic · Regional). */
import { useEffect, useState } from 'react';
import { AXForm, AXInput } from '@ui/ax';
import { adminApi, type SchoolSettings } from './api';

const TABS = ['general', 'branding', 'contact', 'academic', 'regional'] as const;
type Tab = (typeof TABS)[number];

export function SchoolSettingsPage() {
  const [data, setData] = useState<SchoolSettings | null>(null);
  const [tab, setTab] = useState<Tab>('general');
  const [section, setSection] = useState<Record<string, unknown>>({});
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);

  useEffect(() => {
    adminApi.getSchool().then((d) => {
      setData(d);
      setSection((d.general as Record<string, unknown>) ?? {});
    });
  }, []);

  const switchTab = (t: Tab) => {
    if (!data) return;
    setTab(t);
    setSaved(false);
    const current = (data as unknown as Record<string, Record<string, unknown> | null>)[t] ?? {};
    setSection(t === 'branding' ? { theme_color: (data.branding?.theme_color as string) ?? '#001F3F' } : current);
  };

  const field = (key: string) => ({
    value: (section[key] as string) ?? '',
    onChange: (e: React.ChangeEvent<HTMLInputElement>) => setSection((s) => ({ ...s, [key]: e.target.value })),
  });

  const save = async () => {
    setSaving(true);
    try {
      const updated = await adminApi.updateSchool({ [tab]: section });
      setData(updated);
      setSaved(true);
    } finally {
      setSaving(false);
    }
  };

  if (!data) return <div className="erp-card text-gray-400"><i className="fas fa-spinner fa-spin" /> Loading…</div>;

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-school text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">School Settings</h2>
      </div>

      <div className="flex gap-1 border-b">
        {TABS.map((t) => (
          <button
            key={t}
            onClick={() => switchTab(t)}
            className={`px-4 py-2 text-sm capitalize ${tab === t ? 'border-b-2 border-[var(--navy-accent)] font-semibold text-[var(--navy-primary)]' : 'text-gray-500'}`}
          >
            {t}
          </button>
        ))}
      </div>

      <div className="erp-card max-w-xl">
        {saved && <div className="mb-3 rounded bg-green-50 px-3 py-2 text-sm text-[var(--success)]"><i className="fas fa-check mr-1" /> Saved.</div>}
        <AXForm onSubmit={save} submitting={saving}>
          {tab === 'general' && (
            <>
              <AXInput label="School name" {...field('name')} />
              <AXInput label="Short name" {...field('short_name')} />
              <AXInput label="Motto" {...field('motto')} />
              <AXInput label="Established year" {...field('established_year')} />
            </>
          )}
          {tab === 'branding' && (
            <AXInput label="Theme color" type="text" {...field('theme_color')} />
          )}
          {tab === 'contact' && (
            <>
              <AXInput label="Email" type="email" {...field('email')} />
              <AXInput label="Phone" {...field('phone')} />
              <AXInput label="Website" {...field('website')} />
              <AXInput label="Address" {...field('address')} />
            </>
          )}
          {tab === 'academic' && (
            <>
              <AXInput label="Academic year" {...field('academic_year')} />
              <AXInput label="Session label" {...field('session_label')} />
            </>
          )}
          {tab === 'regional' && (
            <>
              <AXInput label="Timezone" {...field('timezone')} />
              <AXInput label="Currency" {...field('currency')} />
              <AXInput label="Locale" {...field('locale')} />
              <AXInput label="Date format" {...field('date_format')} />
            </>
          )}
        </AXForm>
      </div>
    </div>
  );
}
