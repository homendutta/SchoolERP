/* Student Export — reuses the Export engine. Filter then download CSV. */
import { useState } from 'react';
import { AXSelect } from '@ui/ax';
import { useClasses } from '@features/academic/useReference';
import { studentsApi } from './api';

const STATUS = ['active', 'promoted', 'transferred', 'withdrawn', 'graduated', 'archived'];

export function ExportPage() {
  const classes = useClasses();
  const [status, setStatus] = useState('');
  const [classId, setClassId] = useState('');

  const url = studentsApi.exportUrl({ filter: { status, class_id: classId } });

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-file-export text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Student Export</h2>
      </div>

      <div className="erp-card space-y-4">
        <p className="text-sm text-gray-500">
          Choose filters, then download. CSV is available now; Excel and PDF are pluggable drivers
          in the Export engine.
        </p>
        <div className="flex flex-wrap items-end gap-3">
          <div className="w-44">
            <AXSelect
              label="Status"
              value={status}
              onChange={(e) => setStatus(e.target.value)}
              options={[
                { value: '', label: 'All' },
                ...STATUS.map((s) => ({ value: s, label: s })),
              ]}
            />
          </div>
          <div className="w-52">
            <AXSelect
              label="Class"
              value={classId}
              onChange={(e) => setClassId(e.target.value)}
              options={[{ value: '', label: 'All' }, ...classes]}
            />
          </div>
          <a
            href={url}
            className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white hover:bg-[var(--navy-hover)]"
          >
            <i className="fas fa-download mr-1" /> Download CSV
          </a>
        </div>
      </div>
    </div>
  );
}
