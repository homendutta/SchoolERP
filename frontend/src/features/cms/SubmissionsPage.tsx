/* Contact Submissions — captured from the public site (read + status). */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXPagination, AXSelect, AXTable, type AXColumn, type AXPageMeta } from '@ui/ax';
import { ENQUIRY_STATUSES, cmsApi, type Ref } from './api';

const TONES: Record<string, 'amber' | 'navy' | 'green' | 'gray'> = {
  new: 'amber',
  contacted: 'navy',
  responded: 'green',
  closed: 'gray',
};

export function SubmissionsPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<Ref[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const load = useMemo(
    () => () =>
      cmsApi.submissions({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );
  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Name',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'type', header: 'Type', render: (r) => String(r.type).replace(/_/g, ' ') },
    { key: 'email', header: 'Email', render: (r) => String(r.email ?? '—') },
    { key: 'subject', header: 'Subject', render: (r) => String(r.subject ?? '—') },
    {
      key: 'status',
      header: 'Status',
      render: (r) => (
        <div className="flex items-center gap-2">
          <AXBadge tone={TONES[String(r.status)] ?? 'gray'}>{String(r.status)}</AXBadge>
          <div className="w-32">
            <AXSelect
              value=""
              onChange={(e) => {
                if (e.target.value)
                  cmsApi.updateSubmission(r.id, { status: e.target.value }).then(load);
              }}
              options={[
                { value: '', label: 'Set…' },
                ...ENQUIRY_STATUSES.map((s) => ({ value: s, label: s })),
              ]}
            />
          </div>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-envelope-open-text text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Contact Submissions</h2>
      </div>
      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No submissions yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
