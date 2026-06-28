/* Identity List — searchable directory; view opens full details (QR + barcode).
 * Immutable fields are never editable. */
import { useEffect, useMemo, useState } from 'react';
import {
  AXBadge,
  AXModal,
  AXPagination,
  AXSearch,
  AXSelect,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { identityApi, type Identity } from './api';
import { IdentityCard } from './IdentityCard';

const TYPES = ['student', 'guardian', 'staff'];
const STATUS = ['active', 'disabled'];

export function IdentityListPage() {
  const [rows, setRows] = useState<Identity[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 15,
  });
  const [loading, setLoading] = useState(false);
  const [q, setQ] = useState('');
  const [type, setType] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [selected, setSelected] = useState<Identity | null>(null);

  const load = useMemo(
    () => () => {
      setLoading(true);
      const params: Record<string, unknown> = { page };
      if (q) params.search = { identity_number: q };
      const filter: Record<string, string> = {};
      if (type) filter.identity_type = type;
      if (status) filter.status = status;
      if (Object.keys(filter).length) params.filter = filter;
      identityApi
        .search(params)
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        })
        .finally(() => setLoading(false));
    },
    [q, type, status, page]
  );

  useEffect(() => {
    load();
  }, [load]);

  const columns: AXColumn<Identity>[] = [
    {
      key: 'identity_number',
      header: 'Identity No.',
      render: (r) => <code className="text-xs text-gray-500">{r.identity_number}</code>,
    },
    {
      key: 'owner',
      header: 'Owner',
      render: (r) => <span className="font-medium">{r.owner?.name ?? '—'}</span>,
    },
    {
      key: 'type',
      header: 'Type',
      render: (r) => <AXBadge tone="navy">{r.identity_type}</AXBadge>,
    },
    {
      key: 'public',
      header: 'Public ID',
      render: (r) => <code className="text-xs text-gray-400">{r.public_identifier}</code>,
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={r.status === 'active' ? 'green' : 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'actions',
      header: '',
      className: 'text-right',
      render: (r) => (
        <button
          onClick={() => setSelected(r)}
          title="View"
          className="text-gray-500 hover:text-[var(--navy-accent)]"
        >
          <i className="fas fa-eye" />
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-fingerprint text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Identities</h2>
        <AXBadge tone="navy">{meta.total} total</AXBadge>
      </div>

      <div className="flex flex-wrap items-center gap-3">
        <div className="w-44">
          <AXSelect
            value={type}
            onChange={(e) => {
              setType(e.target.value);
              setPage(1);
            }}
            options={[
              { value: '', label: 'Type: All' },
              ...TYPES.map((t) => ({ value: t, label: t })),
            ]}
          />
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
        <div className="min-w-[16rem] flex-1">
          <AXSearch
            onSearch={(t) => {
              setQ(t);
              setPage(1);
            }}
            placeholder="Search by identity number…"
          />
        </div>
      </div>

      <AXTable
        columns={columns}
        rows={rows}
        rowKey={(r) => r.id}
        loading={loading}
        empty="No identities found."
      />
      <AXPagination meta={meta} onPage={setPage} />

      <AXModal open={selected !== null} title="Identity Details" onClose={() => setSelected(null)}>
        {selected && (
          <IdentityCard
            identity={selected}
            onChange={(i) => {
              setSelected(i);
              load();
            }}
          />
        )}
      </AXModal>
    </div>
  );
}
