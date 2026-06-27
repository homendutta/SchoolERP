/* AX feedback: AXBadge, AXStatus. */
import type { ReactNode } from 'react';

type Tone = 'navy' | 'green' | 'amber' | 'red' | 'gray';

const TONES: Record<Tone, string> = {
  navy: 'bg-[var(--navy-primary)]/10 text-[var(--navy-primary)]',
  green: 'bg-[var(--success)]/15 text-[var(--success)]',
  amber: 'bg-[var(--warning)]/20 text-[#9a7a00]',
  red: 'bg-[var(--danger)]/15 text-[var(--danger)]',
  gray: 'bg-gray-100 text-gray-600',
};

export function AXBadge({ children, tone = 'gray' }: { children: ReactNode; tone?: Tone }) {
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${TONES[tone]}`}>
      {children}
    </span>
  );
}

/** Active / Archived (or custom) status pill. */
export function AXStatus({ active, activeLabel = 'Active', inactiveLabel = 'Archived' }: {
  active: boolean;
  activeLabel?: string;
  inactiveLabel?: string;
}) {
  return (
    <AXBadge tone={active ? 'green' : 'gray'}>
      <i className={`fas fa-circle mr-1 text-[6px] ${active ? '' : 'opacity-50'}`} />
      {active ? activeLabel : inactiveLabel}
    </AXBadge>
  );
}
