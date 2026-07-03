/* Leave types — configurable (nothing hardcoded). */
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, statusCell, type Field } from '@features/academic/EntityManager';
import { hrApi, type Ref } from './api';

export function LeaveTypesPage() {
  const { user } = useAuth();

  const fields: Field[] = [
    { name: 'name', label: 'Leave type', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    { name: 'is_paid', label: 'Paid leave', type: 'checkbox' },
    { name: 'description', label: 'Description', type: 'text' },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'name',
      header: 'Name',
      render: (r) => <span className="font-medium">{String(r.name)}</span>,
    },
    { key: 'code', header: 'Code', render: (r) => String(r.code ?? '—') },
    { key: 'paid', header: 'Paid', render: (r) => (r.is_paid ? '✓' : '—') },
    { key: 'status', header: 'Status', render: statusCell },
  ];

  return (
    <EntityManager<Ref>
      title="Leave Types"
      icon="tags"
      unitLabel="types"
      api={hrApi.leaveTypes}
      columns={columns}
      fields={fields}
      emptyForm={{ name: '', code: '', is_paid: true, description: '' }}
      toForm={(r) => ({
        name: r.name,
        code: r.code ?? '',
        is_paid: Boolean(r.is_paid),
        description: r.description ?? '',
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search leave types…"
      sort="name"
    />
  );
}
