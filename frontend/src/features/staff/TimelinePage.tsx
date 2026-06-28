/* Staff Timeline — every important event, newest first. */
import { useEffect, useState } from 'react';
import { staffApi, type TimelineEntry } from './api';
import { StaffPicker, useStaffList } from './StaffPicker';

export function TimelinePage() {
  const staff = useStaffList();
  const [id, setId] = useState('');
  const [entries, setEntries] = useState<TimelineEntry[]>([]);

  useEffect(() => {
    if (!id) {
      setEntries([]);
      return;
    }
    staffApi.timeline(Number(id)).then((e) => setEntries(Array.isArray(e) ? e : []));
  }, [id]);

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-timeline text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Staff Timeline</h2>
        </div>
        <StaffPicker value={id} onChange={setId} staff={staff} />
      </div>

      {id && (
        <div className="erp-card">
          {entries.length === 0 ? (
            <p className="text-sm text-gray-400">No events yet.</p>
          ) : (
            <ol className="relative border-l border-gray-200 pl-6">
              {entries.map((e) => (
                <li key={e.id} className="mb-5">
                  <span className="absolute -left-1.5 mt-1 h-3 w-3 rounded-full bg-[var(--navy-primary)]" />
                  <div className="text-sm font-medium text-[var(--navy-primary)]">{e.title}</div>
                  <div className="text-xs text-gray-400">
                    {e.event_type} · {e.created_at?.slice(0, 19).replace('T', ' ')}
                  </div>
                  {e.description && (
                    <div className="mt-1 text-sm text-gray-600">{e.description}</div>
                  )}
                </li>
              ))}
            </ol>
          )}
        </div>
      )}
    </div>
  );
}
