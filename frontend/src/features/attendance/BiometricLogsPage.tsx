/* Biometric Logs — immutable audit of every device event (read-only). */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXSelect, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { attendanceApi, type BiometricLog } from './api';

const STATUS = ['pending', 'processed', 'unmatched', 'failed'];
const TONES: Record<string, 'navy' | 'green' | 'amber' | 'red' | 'gray'> = {
  processed: 'green',
  pending: 'amber',
  unmatched: 'red',
  failed: 'red',
};

export function BiometricLogsPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<BiometricLog[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [loading, setLoading] = useState(false);
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);

  const load = useMemo(
    () => () => {
      setLoading(true);
      attendanceApi
        .biometricLogs({ page, school_id: user?.school_id, processing_status: status })
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        })
        .finally(() => setLoading(false));
    },
    [status, page, user?.school_id]
  );

  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<BiometricLog>[] = [
    {
      key: 'event_time',
      header: 'Event Time',
      render: (r) => (
        <span className="text-xs text-gray-500">
          {r.event_time?.slice(0, 19).replace('T', ' ')}
        </span>
      ),
    },
    {
      key: 'identity_number',
      header: 'Identity',
      render: (r) => <code className="text-xs text-gray-500">{r.identity_number}</code>,
    },
    { key: 'device', header: 'Device', render: (r) => r.device?.name ?? '—' },
    { key: 'direction', header: 'Direction', render: (r) => r.direction ?? '—' },
    {
      key: 'status',
      header: 'Processing',
      render: (r) => (
        <AXBadge tone={TONES[r.processing_status] ?? 'gray'}>{r.processing_status}</AXBadge>
      ),
    },
    {
      key: 'attendance',
      header: 'Attendance',
      render: (r) => (r.attendance_id ? `#${r.attendance_id}` : '—'),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className="fas fa-microchip text-[var(--navy-primary)]" />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Biometric Logs</h2>
          <AXBadge tone="gray">immutable</AXBadge>
        </div>
        <div className="w-44">
          <AXSelect
            value={status}
            onChange={(e) => {
              setStatus(e.target.value);
              setPage(1);
            }}
            options={[
              { value: '', label: 'Status: All' },
              ...STATUS.map((s) => ({ value: s, label: s })),
            ]}
          />
        </div>
      </div>

      <AXTable
        columns={columns}
        rows={rows}
        rowKey={(r) => r.id}
        loading={loading}
        empty="No biometric events yet."
      />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
