/* Asset Assignments — historical; assign to a target, return frees the asset. */
import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import {
  AXBadge,
  AXInput,
  AXPagination,
  AXSelect,
  AXTable,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';
import { staffApi } from '@features/staff/api';
import { ASSIGN_TARGETS, inventoryApi, type Assignment, type Asset } from './api';

const TONES: Record<string, 'green' | 'gray' | 'amber'> = {
  active: 'green',
  returned: 'gray',
  transferred: 'amber',
};

export function AssetAssignmentsPage() {
  const { user } = useAuth();
  const [assets, setAssets] = useState<Asset[]>([]);
  const [staff, setStaff] = useState<Array<{ value: string; label: string }>>([]);
  const [form, setForm] = useState({
    asset_id: '',
    target_type: 'staff',
    identity_number: '',
    target_reference: '',
    target_label: '',
  });
  const [error, setError] = useState<string | null>(null);
  const [rows, setRows] = useState<Assignment[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 25,
  });
  const [page, setPage] = useState(1);

  const loadAssets = () =>
    inventoryApi.assets
      .list({ filter: { school_id: user?.school_id, status: 'available' }, per_page: 500 })
      .then((r) => setAssets(r.data));
  const load = useMemo(
    () => () =>
      inventoryApi.assignments({ page, filter: { school_id: user?.school_id } }).then((r) => {
        setRows(r.data);
        setMeta(r.meta);
      }),
    [page, user?.school_id]
  );

  useEffect(() => {
    loadAssets();
    staffApi.staff.list({ per_page: 500, sort: 'name' }).then((r) =>
      setStaff(
        // Value is the staff member's Identity number — assignment resolves through
        // the Platform Identity Service, never through the Staff primary key.
        r.data
          .filter((s) => s.identity_number)
          .map((s) => ({
            value: String(s.identity_number),
            label: `${s.employee_number} — ${s.name}`,
          }))
      )
    );
  }, [user?.school_id]); // eslint-disable-line react-hooks/exhaustive-deps
  useEffect(() => {
    load();
  }, [load]);

  const submit = async () => {
    setError(null);
    try {
      await inventoryApi.assign({
        asset_id: Number(form.asset_id),
        target_type: form.target_type,
        identity_number:
          form.target_type === 'staff' && form.identity_number ? form.identity_number : null,
        target_reference:
          form.target_type !== 'staff' && form.target_reference ? form.target_reference : null,
        target_label: form.target_label || null,
      });
      setForm({
        asset_id: '',
        target_type: 'staff',
        identity_number: '',
        target_reference: '',
        target_label: '',
      });
      loadAssets();
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not assign.');
    }
  };

  const columns: AXColumn<Assignment>[] = [
    {
      key: 'asset',
      header: 'Asset',
      render: (r) => <span className="font-medium">{r.asset?.asset_number ?? r.asset_id}</span>,
    },
    {
      key: 'target',
      header: 'Assigned to',
      render: (r) => `${r.target_type}${r.target_label ? ' · ' + r.target_label : ''}`,
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'act',
      header: '',
      render: (r) =>
        r.status === 'active' ? (
          <button
            onClick={() =>
              inventoryApi.returnAsset(r.id).then(() => {
                loadAssets();
                load();
              })
            }
            className="text-xs font-semibold text-[var(--danger)]"
          >
            Return
          </button>
        ) : null,
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-user-tag text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Asset Assignments</h2>
      </div>

      <div className="erp-card flex flex-wrap items-end gap-3">
        <div className="w-52">
          <AXSelect
            label="Asset"
            value={form.asset_id}
            onChange={(e) => setForm((f) => ({ ...f, asset_id: e.target.value }))}
            options={[
              { value: '', label: 'Select…' },
              ...assets.map((a) => ({ value: String(a.id), label: a.asset_number })),
            ]}
          />
        </div>
        <div className="w-40">
          <AXSelect
            label="Target"
            value={form.target_type}
            onChange={(e) => setForm((f) => ({ ...f, target_type: e.target.value }))}
            options={ASSIGN_TARGETS.map((t) => ({ value: t, label: t }))}
          />
        </div>
        {form.target_type === 'staff' ? (
          <div className="w-56">
            <AXSelect
              label="Staff (by Identity)"
              value={form.identity_number}
              onChange={(e) => setForm((f) => ({ ...f, identity_number: e.target.value }))}
              options={[{ value: '', label: 'Select…' }, ...staff]}
            />
          </div>
        ) : (
          <>
            <div className="w-44">
              <AXInput
                label="Target reference"
                value={form.target_reference}
                onChange={(e) => setForm((f) => ({ ...f, target_reference: e.target.value }))}
              />
            </div>
            <div className="w-44">
              <AXInput
                label="Target label"
                value={form.target_label}
                onChange={(e) => setForm((f) => ({ ...f, target_label: e.target.value }))}
              />
            </div>
          </>
        )}
        <button
          onClick={submit}
          disabled={!form.asset_id}
          className="rounded-md bg-[var(--navy-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
        >
          Assign
        </button>
        {error && <AXBadge tone="red">{error}</AXBadge>}
      </div>

      <AXTable columns={columns} rows={rows} rowKey={(r) => r.id} empty="No assignments yet." />
      <AXPagination meta={meta} onPage={setPage} />
    </div>
  );
}
