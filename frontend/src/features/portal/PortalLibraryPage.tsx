/* Portal Library — borrowed books, due dates, fines (from the Library module). */
import { useEffect, useState } from 'react';
import { AXBadge, AXTable, type AXColumn } from '@ui/ax';
import { portalApi } from './api';
import { usePortal } from './usePortal';
import { PortalShell } from './PortalShell';

export function PortalLibraryPage() {
  const { context, studentId, setStudentId, error } = usePortal();
  const [rows, setRows] = useState<Array<Record<string, unknown>>>([]);

  useEffect(() => {
    if (!studentId) return;
    portalApi
      .library(studentId)
      .then(setRows)
      .catch(() => setRows([]));
  }, [studentId]);

  const columns: AXColumn<Record<string, unknown>>[] = [
    {
      key: 'book',
      header: 'Book',
      render: (r) => <span className="font-medium">#{String(r.book_id)}</span>,
    },
    { key: 'borrow', header: 'Borrowed', render: (r) => String(r.borrow_date ?? '—') },
    { key: 'due', header: 'Due', render: (r) => String(r.due_date ?? '—') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <AXBadge tone={String(r.status) === 'returned' ? 'gray' : 'navy'}>
          {String(r.status)}
        </AXBadge>
      ),
    },
    { key: 'fine', header: 'Fine', render: (r) => String(r.fine_amount ?? 0) },
  ];

  return (
    <PortalShell
      title="Library"
      icon="book"
      context={context}
      studentId={studentId}
      onStudent={setStudentId}
      error={error}
    >
      <AXTable columns={columns} rows={rows} rowKey={(r) => Number(r.id)} empty="No borrowings." />
    </PortalShell>
  );
}
