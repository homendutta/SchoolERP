/* AX overlays: AXModal, AXConfirm. */
import { useState, type ReactNode } from 'react';

interface AXModalProps {
  open: boolean;
  title: string;
  onClose: () => void;
  children: ReactNode;
  footer?: ReactNode;
}

export function AXModal({ open, title, onClose, children, footer }: AXModalProps) {
  if (!open) return null;
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40" onClick={onClose} />
      <div className="relative z-10 w-full max-w-lg rounded-lg bg-white shadow-xl">
        <div className="flex items-center justify-between border-b px-5 py-3">
          <h3 className="font-semibold text-[var(--navy-primary)]">{title}</h3>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600" aria-label="Close">
            <i className="fas fa-times" />
          </button>
        </div>
        <div className="max-h-[70vh] overflow-y-auto p-5">{children}</div>
        {footer && <div className="flex justify-end gap-2 border-t px-5 py-3">{footer}</div>}
      </div>
    </div>
  );
}

interface AXConfirmProps {
  open: boolean;
  title?: string;
  message: string;
  confirmLabel?: string;
  tone?: 'danger' | 'navy';
  onConfirm: () => void;
  onCancel: () => void;
}

export function AXConfirm({
  open, title = 'Please confirm', message, confirmLabel = 'Confirm', tone = 'danger', onConfirm, onCancel,
}: AXConfirmProps) {
  return (
    <AXModal
      open={open}
      title={title}
      onClose={onCancel}
      footer={
        <>
          <button onClick={onCancel} className="rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
            Cancel
          </button>
          <button
            onClick={onConfirm}
            className={`rounded-md px-4 py-2 text-sm font-semibold text-white ${
              tone === 'danger' ? 'bg-[var(--danger)]' : 'bg-[var(--navy-primary)]'
            }`}
          >
            {confirmLabel}
          </button>
        </>
      }
    >
      <p className="text-sm text-gray-600">{message}</p>
    </AXModal>
  );
}

/** Hook to drive an AXConfirm imperatively. */
export function useConfirm() {
  const [state, setState] = useState<{ open: boolean; message: string; action: () => void }>({
    open: false,
    message: '',
    action: () => {},
  });
  return {
    confirmProps: {
      open: state.open,
      message: state.message,
      onConfirm: () => {
        state.action();
        setState((s) => ({ ...s, open: false }));
      },
      onCancel: () => setState((s) => ({ ...s, open: false })),
    },
    ask: (message: string, action: () => void) => setState({ open: true, message, action }),
  };
}
