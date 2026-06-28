/* Student Academic Records — the immutable academic history (read-only). */
import { useEffect, useState } from 'react';
import { AXBadge, AXTable, type AXColumn } from '@ui/ax';
import { studentsApi, type AcademicRecord } from './api';
import { StudentPicker, useStudentList } from './StudentPicker';

export function AcademicRecordsPage() {
  const students = useStudentList();
  const [id, setId] = useState('');
  const [rows, setRows] = useState<AcademicRecord[]>([]);

  useEffect(() => {
    if (!id) {
      setRows([]);
      return;
    }
    studentsApi.academicRecords(Number(id)).then((r) => setRows(r.data));
  }, [id]);

  const columns: AXColumn<AcademicRecord>[] = [
    { key: 'year', header: 'Academic Year', render: (r) => r.academic_year?.name ?? '—' },
    { key: 'class', header: 'Class', render: (r) => r.class?.name ?? '—' },
    { key: 'section', header: 'Section', render: (r) => r.section?.name ?? '—' },
    { key: 'roll_number', header: 'Roll', render: (r) => r.roll_number ?? '—' },
    {
      key: 'current',
      header: 'Current',
      render: (r) =>
        r.is_current ? (
          <AXBadge tone="green">Current</AXBadge>
        ) : (
          <span className="text-gray-300">—</span>
        ),
    },
    {
      key: 'promoted',
      header: 'Promoted From',
      render: (r) => (r.promoted_from_record_id ? `#${r.promoted_from_record_id}` : '—'),
    },
    {
      key: 'period',
      header: 'Period',
      render: (r) => (
        <span className="text-xs text-gray-500">
          {r.started_on}
          {r.ended_on ? ` → ${r.ended_on}` : ''}
        </span>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-clock-rotate-left text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Academic Records</h2>
        </div>
        <StudentPicker value={id} onChange={setId} students={students} />
      </div>

      {id && (
        <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No academic records." />
      )}
    </div>
  );
}
