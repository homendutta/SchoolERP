/* AXForm — minimal controlled form wrapper with submit handling. */
import { type FormEvent, type ReactNode } from 'react';

interface AXFormProps {
  onSubmit: () => void | Promise<void>;
  children: ReactNode;
  submitting?: boolean;
  submitLabel?: string;
  onCancel?: () => void;
}

export function AXForm({ onSubmit, children, submitting, submitLabel = 'Save', onCancel }: AXFormProps) {
  const handle = (e: FormEvent) => {
    e.preventDefault();
    void onSubmit();
  };
  return (
    <form onSubmit={handle} className="space-y-4">
      {children}
      <div className="flex justify-end gap-2 pt-2">
        {onCancel && (
          <button type="button" onClick={onCancel} className="rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
            Cancel
          </button>
        )}
        <button
          type="submit"
          disabled={submitting}
          className="flex items-center gap-2 rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white hover:bg-[var(--navy-hover)] disabled:opacity-60"
        >
          {submitting && <i className="fas fa-spinner fa-spin" />}
          {submitLabel}
        </button>
      </div>
    </form>
  );
}
