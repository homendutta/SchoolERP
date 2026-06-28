/* Admission Approval — configure the school's workflow (one-step or multi-step)
 * and process applications step by step. Nothing about the flow is hardcoded. */
import { useEffect, useState, useCallback } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { admissionsApi, type Application, type ApprovalStep, type WorkflowStep } from './api';

const TONES: Record<string, 'navy' | 'green' | 'amber' | 'red' | 'gray'> = {
  pending: 'amber',
  approved: 'green',
  rejected: 'red',
  on_hold: 'gray',
  skipped: 'gray',
};

export function ApprovalPage() {
  const [tab, setTab] = useState<'process' | 'config'>('process');
  return (
    <div className="space-y-4">
      <div className="flex gap-1 rounded-lg border border-gray-200 bg-white p-1 text-sm">
        <button
          onClick={() => setTab('process')}
          className={`flex items-center gap-2 rounded-md px-3 py-2 font-medium ${tab === 'process' ? 'bg-[var(--navy-primary)] text-white' : 'text-gray-600 hover:bg-gray-100'}`}
        >
          <i className="fas fa-list-check" /> Process Applications
        </button>
        <button
          onClick={() => setTab('config')}
          className={`flex items-center gap-2 rounded-md px-3 py-2 font-medium ${tab === 'config' ? 'bg-[var(--navy-primary)] text-white' : 'text-gray-600 hover:bg-gray-100'}`}
        >
          <i className="fas fa-sliders" /> Workflow Configuration
        </button>
      </div>
      {tab === 'process' ? <ProcessTab /> : <ConfigTab />}
    </div>
  );
}

function ProcessTab() {
  const [apps, setApps] = useState<Application[]>([]);
  const [selectedId, setSelectedId] = useState('');
  const [app, setApp] = useState<Application | null>(null);

  useEffect(() => {
    admissionsApi.applications
      .list({ per_page: 100, sort: 'created_at' })
      .then((r) => setApps(r.data));
  }, []);

  const refresh = useCallback((id: number) => {
    admissionsApi.applications.get(id).then(setApp);
  }, []);

  useEffect(() => {
    if (selectedId) refresh(Number(selectedId));
    else setApp(null);
  }, [selectedId, refresh]);

  const start = () => app && admissionsApi.approval.start(app.id).then(() => refresh(app.id));
  const act = (stepId: number, decision: string) =>
    app && admissionsApi.approval.act(stepId, decision).then(() => refresh(app.id));

  const columns: AXColumn<ApprovalStep>[] = [
    { key: 'sort_order', header: '#', render: (r) => r.sort_order },
    { key: 'name', header: 'Step', render: (r) => <span className="font-medium">{r.name}</span> },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    {
      key: 'actions',
      header: '',
      className: 'text-right',
      render: (r) =>
        r.status === 'pending' ? (
          <div className="flex justify-end gap-2 text-gray-500">
            <button
              onClick={() => act(r.id, 'approved')}
              title="Approve"
              className="hover:text-[var(--success)]"
            >
              <i className="fas fa-check" />
            </button>
            <button
              onClick={() => act(r.id, 'on_hold')}
              title="Hold"
              className="hover:text-amber-600"
            >
              <i className="fas fa-pause" />
            </button>
            <button
              onClick={() => act(r.id, 'rejected')}
              title="Reject"
              className="hover:text-[var(--danger)]"
            >
              <i className="fas fa-xmark" />
            </button>
          </div>
        ) : (
          <span className="text-xs text-gray-400">{r.remarks ?? ''}</span>
        ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center gap-3">
        <div className="w-80">
          <AXSelect
            value={selectedId}
            onChange={(e) => setSelectedId(e.target.value)}
            options={[
              { value: '', label: 'Select an application…' },
              ...apps.map((a) => ({
                value: String(a.id),
                label: `${a.application_number} — ${a.student_name}`,
              })),
            ]}
          />
        </div>
        {app && (app.approval_steps?.length ?? 0) === 0 && (
          <button
            onClick={start}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white"
          >
            <i className="fas fa-play mr-1" /> Start Approval
          </button>
        )}
      </div>

      {app && (
        <>
          <div className="erp-card flex flex-wrap items-center gap-3">
            <span className="font-medium">{app.student_name}</span>
            <AXBadge tone="navy">{app.application_number}</AXBadge>
            <span className="text-sm text-gray-500">Status:</span>
            <AXBadge tone={TONES[app.status] ?? 'navy'}>{app.status}</AXBadge>
          </div>
          <AXTable
            columns={columns}
            rows={app.approval_steps ?? []}
            rowKey={(r) => r.id}
            empty="No approval steps yet — start the workflow."
          />
        </>
      )}
    </div>
  );
}

function ConfigTab() {
  const { user } = useAuth();
  const fields: Field[] = [
    { name: 'name', label: 'Step name', type: 'text', required: true },
    { name: 'role_slug', label: 'Role slug (optional)', type: 'text' },
    { name: 'sort_order', label: 'Order', type: 'number' },
    { name: 'is_active', label: 'Active', type: 'checkbox' },
  ];
  const columns: AXColumn<WorkflowStep>[] = [
    { key: 'sort_order', header: '#', render: (r) => r.sort_order },
    { key: 'name', header: 'Step', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'role_slug', header: 'Role', render: (r) => r.role_slug ?? '—' },
    {
      key: 'is_active',
      header: 'Active',
      render: (r) => statusCell({ status: r.is_active ? 'active' : 'inactive' }),
    },
  ];
  return (
    <EntityManager<WorkflowStep>
      title="Workflow Steps"
      icon="diagram-project"
      unitLabel="steps"
      api={admissionsApi.workflowSteps}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', role_slug: '', sort_order: 0, is_active: true }}
      toForm={(r) => ({
        name: r.name,
        role_slug: r.role_slug ?? '',
        sort_order: r.sort_order,
        is_active: r.is_active,
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="sort_order"
    />
  );
}
