/* Feature Flags — enable/disable optional product modules. */
import { useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { adminApi, type FeatureFlag } from './api';

export function FeatureFlagsPage() {
  const [flags, setFlags] = useState<FeatureFlag[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    adminApi.listFeatureFlags().then((f) => setFlags(f)).finally(() => setLoading(false));
  }, []);

  const toggle = async (flag: FeatureFlag) => {
    const next = !flag.is_enabled;
    setFlags((fs) => fs.map((f) => (f.key === flag.key ? { ...f, is_enabled: next } : f)));
    await adminApi.toggleFeatureFlag(flag.key, next);
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-toggle-on text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Feature Flags</h2>
      </div>

      {loading ? (
        <div className="erp-card text-gray-400"><i className="fas fa-spinner fa-spin" /> Loading…</div>
      ) : (
        <div className="grid gap-3 sm:grid-cols-2">
          {flags.map((flag) => (
            <div key={flag.key} className="erp-card flex items-center justify-between">
              <div>
                <div className="font-medium text-[var(--navy-primary)]">{flag.label ?? flag.key}</div>
                <AXBadge tone={flag.is_enabled ? 'green' : 'gray'}>{flag.is_enabled ? 'Enabled' : 'Disabled'}</AXBadge>
              </div>
              <button
                onClick={() => toggle(flag)}
                className={`relative h-6 w-11 rounded-full transition-colors ${flag.is_enabled ? 'bg-[var(--success)]' : 'bg-gray-300'}`}
                aria-label={`Toggle ${flag.key}`}
              >
                <span className={`absolute top-0.5 h-5 w-5 rounded-full bg-white transition-all ${flag.is_enabled ? 'left-[22px]' : 'left-0.5'}`} />
              </button>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
