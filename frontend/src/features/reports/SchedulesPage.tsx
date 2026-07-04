/* Scheduled Reports — queued runs, optional email delivery via Communication. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { EXPORT_FORMATS, FREQUENCIES, reportsApi, type CatalogItem, type Ref } from './api';

const TONES: Record<string, 'gray' | 'navy' | 'amber'> = {
  daily: 'navy',
  weekly: 'amber',
  monthly: 'gray',
};

export function SchedulesPage() {
  const { user } = useAuth();
  const [reports, setReports] = useState<FieldOption[]>([]);

  useEffect(() => {
    reportsApi
      .catalog()
      .then((c) =>
        setReports(c.map((r: CatalogItem) => ({ value: r.key, label: `${r.module} · ${r.name}` })))
      )
      .catch(() => undefined);
  }, []);

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'report_key', label: 'Report', type: 'select', options: reports, required: true },
    {
      name: 'frequency',
      label: 'Frequency',
      type: 'select',
      options: FREQUENCIES.map((f) => ({ value: f, label: f })),
    },
    {
      name: 'format',
      label: 'Format',
      type: 'select',
      options: EXPORT_FORMATS.map((f) => ({ value: f, label: f })),
    },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Name',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'report', header: 'Report', render: (r) => String(r.report_key) },
    {
      key: 'freq',
      header: 'Frequency',
      render: (r) => (
        <AXBadge tone={TONES[String(r.frequency)] ?? 'gray'}>{String(r.frequency)}</AXBadge>
      ),
    },
    { key: 'next', header: 'Next run', render: (r) => String(r.next_run_at ?? '—').slice(0, 16) },
  ];

  return (
    <EntityManager<Ref>
      title="Scheduled Reports"
      icon="clock"
      unitLabel="schedules"
      api={reportsApi.schedules}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', report_key: '', frequency: 'weekly', format: 'csv' }}
      toForm={(r) => ({
        name: r.name,
        report_key: String(r.report_key ?? ''),
        frequency: String(r.frequency ?? 'weekly'),
        format: String(r.format ?? 'csv'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
      rowExtras={(r, reload) => (
        <button
          onClick={() => reportsApi.runSchedule(r.id).then(reload)}
          title="Run now (queues the export)"
          className="hover:text-[var(--navy-accent)]"
        >
          <i className="fas fa-play" />
        </button>
      )}
    />
  );
}
