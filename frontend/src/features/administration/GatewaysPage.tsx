/* Gateways — Email (SMTP), SMS, and Payment configuration with test/live modes. */
import { useEffect, useState } from 'react';
import { AXBadge, AXForm, AXInput, AXSelect, AXStatus } from '@ui/ax';
import { adminApi, type PaymentGatewaySummary } from './api';

type Tab = 'email' | 'sms' | 'payment';

export function GatewaysPage() {
  const [tab, setTab] = useState<Tab>('email');

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-plug text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Gateways</h2>
      </div>

      <div className="flex gap-1 border-b">
        {(['email', 'sms', 'payment'] as Tab[]).map((t) => (
          <button
            key={t}
            onClick={() => setTab(t)}
            className={`px-4 py-2 text-sm capitalize ${tab === t ? 'border-b-2 border-[var(--navy-accent)] font-semibold text-[var(--navy-primary)]' : 'text-gray-500'}`}
          >
            {t}
          </button>
        ))}
      </div>

      {tab === 'email' && <EmailGateway />}
      {tab === 'sms' && <SmsGateway />}
      {tab === 'payment' && <PaymentGateways />}
    </div>
  );
}

function EmailGateway() {
  const [form, setForm] = useState<Record<string, unknown>>({});
  const [saving, setSaving] = useState(false);
  const [test, setTest] = useState<string | null>(null);

  useEffect(() => { adminApi.getEmailGateway().then(setForm); }, []);
  const f = (k: string) => ({ value: (form[k] as string) ?? '', onChange: (e: React.ChangeEvent<HTMLInputElement>) => setForm((s) => ({ ...s, [k]: e.target.value })) });

  return (
    <div className="erp-card max-w-xl space-y-3">
      <AXForm
        submitting={saving}
        onSubmit={async () => { setSaving(true); try { await adminApi.updateEmailGateway(form); } finally { setSaving(false); } }}
      >
        <AXInput label="Host" {...f('host')} />
        <AXInput label="Port" type="number" {...f('port')} />
        <AXInput label="From address" type="email" {...f('from_address')} />
        <AXInput label="Username" {...f('username')} />
      </AXForm>
      <div className="flex items-center gap-3">
        <button
          onClick={async () => setTest((await adminApi.testEmailGateway()).message)}
          className="rounded-md border border-[var(--navy-primary)] px-3 py-2 text-sm text-[var(--navy-primary)]"
        >
          <i className="fas fa-vial mr-1" /> Test connection
        </button>
        {test && <span className="text-sm text-gray-600">{test}</span>}
      </div>
    </div>
  );
}

function SmsGateway() {
  const [form, setForm] = useState<Record<string, unknown>>({});
  const [saving, setSaving] = useState(false);
  useEffect(() => { adminApi.getSmsGateway().then(setForm); }, []);
  const f = (k: string) => ({ value: (form[k] as string) ?? '', onChange: (e: React.ChangeEvent<HTMLInputElement>) => setForm((s) => ({ ...s, [k]: e.target.value })) });

  return (
    <div className="erp-card max-w-xl">
      <AXForm
        submitting={saving}
        onSubmit={async () => { setSaving(true); try { await adminApi.updateSmsGateway(form); } finally { setSaving(false); } }}
      >
        <AXInput label="Provider" {...f('provider')} />
        <AXInput label="API URL" {...f('api_url')} />
        <AXInput label="Sender ID" {...f('sender_id')} />
        <AXSelect label="Mode" value={(form.mode as string) ?? 'test'} onChange={(e) => setForm((s) => ({ ...s, mode: e.target.value }))} options={[{ value: 'test', label: 'Test' }, { value: 'live', label: 'Live' }]} />
      </AXForm>
    </div>
  );
}

function PaymentGateways() {
  const [rows, setRows] = useState<PaymentGatewaySummary[]>([]);
  useEffect(() => { adminApi.listPaymentGateways().then(setRows); }, []);

  const toggle = async (g: PaymentGatewaySummary) => {
    const next = !g.is_enabled;
    setRows((rs) => rs.map((r) => (r.provider === g.provider ? { ...r, is_enabled: next } : r)));
    await adminApi.updatePaymentGateway(g.provider, { is_enabled: next, mode: g.mode });
  };

  return (
    <div className="grid gap-3 sm:grid-cols-2">
      {rows.map((g) => (
        <div key={g.provider} className="erp-card flex items-center justify-between">
          <div>
            <div className="font-medium capitalize text-[var(--navy-primary)]">{g.provider}</div>
            <div className="flex gap-2">
              <AXBadge tone={g.mode === 'live' ? 'amber' : 'gray'}>{g.mode}</AXBadge>
              <AXStatus active={g.configured} activeLabel="Configured" inactiveLabel="Not set" />
            </div>
          </div>
          <button
            onClick={() => toggle(g)}
            className={`relative h-6 w-11 rounded-full transition-colors ${g.is_enabled ? 'bg-[var(--success)]' : 'bg-gray-300'}`}
            aria-label={`Toggle ${g.provider}`}
          >
            <span className={`absolute top-0.5 h-5 w-5 rounded-full bg-white transition-all ${g.is_enabled ? 'left-[22px]' : 'left-0.5'}`} />
          </button>
        </div>
      ))}
    </div>
  );
}
