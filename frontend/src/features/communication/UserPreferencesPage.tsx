/* User Preferences — per-channel opt-in/out (respected unless mandatory). */
import { useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { CHANNELS, communicationApi } from './api';

export function UserPreferencesPage() {
  const [prefs, setPrefs] = useState<Record<string, boolean>>(
    Object.fromEntries(CHANNELS.map((c) => [c, true]))
  );
  const [saved, setSaved] = useState(false);

  useEffect(() => {
    communicationApi.preferences().then((r) => {
      setPrefs(Object.fromEntries(r.preferences.map((p) => [p.channel, p.is_enabled])));
    });
  }, []);

  const toggle = (channel: string) => {
    setSaved(false);
    setPrefs((p) => ({ ...p, [channel]: !p[channel] }));
  };

  const save = async () => {
    await communicationApi.savePreferences(
      CHANNELS.map((c) => ({ channel: c, is_enabled: prefs[c] ?? true }))
    );
    setSaved(true);
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-sliders text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">
          Communication Preferences
        </h2>
      </div>

      <div className="erp-card space-y-3">
        <p className="text-sm text-gray-500">
          Choose which channels you receive messages on. Mandatory communications are always
          delivered.
        </p>
        {CHANNELS.map((c) => (
          <label
            key={c}
            className="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3"
          >
            <span className="text-sm font-medium capitalize text-gray-700">
              {c.replace('_', ' ')}
            </span>
            <button
              onClick={() => toggle(c)}
              className={`relative h-6 w-11 rounded-full transition ${prefs[c] ? 'bg-[var(--navy-primary)]' : 'bg-gray-300'}`}
              aria-label={`Toggle ${c}`}
            >
              <span
                className={`absolute top-0.5 h-5 w-5 rounded-full bg-white transition ${prefs[c] ? 'left-[22px]' : 'left-0.5'}`}
              />
            </button>
          </label>
        ))}
        <div className="flex items-center gap-3">
          <button
            onClick={save}
            className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white"
          >
            <i className="fas fa-save mr-1" /> Save
          </button>
          {saved && <AXBadge tone="green">Saved</AXBadge>}
        </div>
      </div>
    </div>
  );
}
