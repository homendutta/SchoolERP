/* Shared portal chrome: title, role badge and (for parents) a child selector. */
import type { ReactNode } from 'react';
import { AXBadge, AXSelect } from '@ui/ax';
import type { PortalContext } from './api';

export function PortalShell({
  title,
  icon,
  context,
  studentId,
  onStudent,
  requiresStudent = true,
  error,
  children,
}: {
  title: string;
  icon: string;
  context: PortalContext | null;
  studentId: number | null;
  onStudent?: (id: number) => void;
  requiresStudent?: boolean;
  error?: string | null;
  children: ReactNode;
}) {
  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <div className="flex items-center gap-2">
          <i className={`fas fa-${icon} text-[var(--navy-primary)]`} />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">{title}</h2>
          {context && <AXBadge tone="navy">{context.role}</AXBadge>}
        </div>
        {context && context.students.length > 1 && onStudent && (
          <div className="w-56">
            <AXSelect
              label="Child"
              value={studentId ? String(studentId) : ''}
              onChange={(e) => onStudent(Number(e.target.value))}
              options={context.students.map((s) => ({
                value: String(s.id),
                label: s.name ?? `#${s.id}`,
              }))}
            />
          </div>
        )}
      </div>

      {error && <AXBadge tone="red">{error}</AXBadge>}
      {requiresStudent && context && context.students.length === 0 ? (
        <p className="text-sm text-gray-500">This view is available to students and parents.</p>
      ) : (
        children
      )}
    </div>
  );
}
