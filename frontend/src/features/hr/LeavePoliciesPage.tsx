/* Leave policies — separate from leave type; drives allocation & approval levels. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import {
  EntityManager,
  statusCell,
  type Field,
  type FieldOption,
} from '@features/academic/EntityManager';
import { hrApi, type Ref } from './api';

export function LeavePoliciesPage() {
  const { user } = useAuth();
  const [types, setTypes] = useState<FieldOption[]>([]);

  useEffect(() => {
    hrApi.leaveTypes
      .list({ filter: { school_id: user?.school_id }, per_page: 500 })
      .then((r) => setTypes(r.data.map((t) => ({ value: String(t.id), label: String(t.name) }))));
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'leave_type_id', label: 'Leave type', type: 'select', options: types, required: true },
    { name: 'name', label: 'Policy name', type: 'text', required: true },
    { name: 'annual_allocation', label: 'Annual allocation (days)', type: 'number' },
    { name: 'approval_levels', label: 'Approval levels', type: 'number' },
    { name: 'carry_forward_limit', label: 'Carry-forward limit', type: 'number' },
    { name: 'carry_forward', label: 'Carry forward allowed', type: 'checkbox' },
    { name: 'encashment_allowed', label: 'Encashment allowed', type: 'checkbox' },
    { name: 'negative_balance_allowed', label: 'Negative balance allowed', type: 'checkbox' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Policy',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    {
      key: 'type',
      header: 'Leave type',
      render: (r) => String((r.leaveType as { name?: string })?.name ?? '—'),
    },
    { key: 'alloc', header: 'Allocation', render: (r) => String(r.annual_allocation ?? 0) },
    { key: 'levels', header: 'Approvals', render: (r) => String(r.approval_levels ?? 1) },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Leave Policies"
      icon="scroll"
      unitLabel="policies"
      api={hrApi.leavePolicies}
      columns={columns}
      fields={fields}
      emptyForm={{
        leave_type_id: '',
        name: '',
        annual_allocation: 12,
        approval_levels: 1,
        carry_forward_limit: 0,
        carry_forward: false,
        encashment_allowed: false,
        negative_balance_allowed: false,
      }}
      toForm={(r) => ({
        leave_type_id: String(r.leave_type_id),
        name: r.name,
        annual_allocation: (r.annual_allocation as number) ?? 0,
        approval_levels: (r.approval_levels as number) ?? 1,
        carry_forward_limit: (r.carry_forward_limit as number) ?? 0,
        carry_forward: Boolean(r.carry_forward),
        encashment_allowed: Boolean(r.encashment_allowed),
        negative_balance_allowed: Boolean(r.negative_balance_allowed),
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search policies…"
      sort="name"
    />
  );
}
