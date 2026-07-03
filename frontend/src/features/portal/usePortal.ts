/* Shared portal context hook — resolves the logged-in user's role + linked
 * students once, and tracks the currently selected child (parents). */
import { useEffect, useState } from 'react';
import { portalApi, type PortalContext } from './api';

export function usePortal() {
  const [context, setContext] = useState<PortalContext | null>(null);
  const [studentId, setStudentId] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    portalApi
      .me()
      .then((ctx) => {
        setContext(ctx);
        if (ctx.students.length > 0) setStudentId(ctx.students[0].id);
      })
      .catch((e) => setError(e instanceof Error ? e.message : 'No portal access.'));
  }, []);

  return { context, studentId, setStudentId, error };
}
