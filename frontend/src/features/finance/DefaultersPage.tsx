/* Defaulters — generated dynamically (never snapshotted). */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { useClasses } from '@features/academic/useReference';
import { financeApi } from './api';

interface DefaulterRow {
  student_id: number;
  student: string | null;
  admission_number: string | null;
  overdue_items: number;
  outstanding: number;
}

export function DefaultersPage() {
  const { user } = useAuth();
  const classes = useClasses();
  const [classId, setClassId] = useState('');
  const [data, setData] = useState<{
    count: number;
    total_outstanding: number;
    students: DefaulterRow[];
  } | null>(null);

  const load = useMemo(
    () => () => {
      if (!user?.school_id) return;
      financeApi
        .defaulters({ school_id: user.school_id, class_id: classId })
        .then((d) => setData(d as unknown as typeof data));
    },
    [classId, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<DefaulterRow>[] = [
    {
      key: 'student',
      header: 'Student',
      render: (r) => <span className="font-medium">{r.student}</span>,
    },
    {
      key: 'adm',
      header: 'Adm. No.',
      render: (r) => <code className="text-xs text-gray-500">{r.admission_number}</code>,
    },
    { key: 'items', header: 'Overdue items', render: (r) => r.overdue_items },
    {
      key: 'outstanding',
      header: 'Outstanding',
      render: (r) => <span className="font-semibold text-[var(--danger)]">₹{r.outstanding}</span>,
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-user-clock text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Defaulters</h2>
        </div>
        <div className="flex items-center gap-3">
          {data && (
            <AXBadge tone="amber">
              Total: ₹{data.total_outstanding} · {data.count} student(s)
            </AXBadge>
          )}
          <div className="w-44">
            <AXSelect
              value={classId}
              onChange={(e) => setClassId(e.target.value)}
              options={[{ value: '', label: 'All classes' }, ...classes]}
            />
          </div>
        </div>
      </div>

      <AXTable
        columns={columns}
        rows={data?.students ?? []}
        rowKey={(r) => r.student_id}
        empty="No defaulters."
      />
    </div>
  );
}
