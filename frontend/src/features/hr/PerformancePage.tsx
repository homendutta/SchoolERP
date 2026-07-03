/* Performance reviews — stored as history; scheduling notifies via Communication. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { staffApi } from '@features/staff/api';
import { REVIEW_STATUSES, hrApi, type Ref } from './api';

export function PerformancePage() {
  const { user } = useAuth();
  const [employees, setEmployees] = useState<FieldOption[]>([]);

  useEffect(() => {
    staffApi.staff
      .list({ per_page: 500, sort: 'name' })
      .then((r) =>
        setEmployees(
          r.data.map((s) => ({ value: String(s.id), label: `${s.employee_number} — ${s.name}` }))
        )
      );
  }, [user?.school_id]);

  const fields: Field[] = [
    { name: 'staff_id', label: 'Employee', type: 'select', options: employees, required: true },
    { name: 'reviewer_id', label: 'Reviewer', type: 'select', options: employees },
    { name: 'review_period_start', label: 'Period start', type: 'date' },
    { name: 'review_period_end', label: 'Period end', type: 'date' },
    { name: 'goals', label: 'Goals', type: 'text' },
    { name: 'rating', label: 'Rating (0-10)', type: 'number' },
    { name: 'comments', label: 'Comments', type: 'text' },
    { name: 'development_plan', label: 'Development plan', type: 'text' },
    {
      name: 'status',
      label: 'Status',
      type: 'select',
      options: REVIEW_STATUSES.map((s) => ({ value: s, label: s.replace('_', ' ') })),
    },
  ];

  const columns: AXColumn<Ref>[] = [
    {
      key: 'employee',
      header: 'Employee',
      render: (r) => (
        <span className="font-medium">
          {String((r.employee as { name?: string })?.name ?? r.staff_id)}
        </span>
      ),
    },
    { key: 'period', header: 'Period end', render: (r) => String(r.review_period_end ?? '—') },
    { key: 'rating', header: 'Rating', render: (r) => String(r.rating ?? '—') },
    { key: 'status', header: 'Status', render: (r) => String(r.status).replace('_', ' ') },
  ];

  return (
    <EntityManager<Ref>
      title="Performance Reviews"
      icon="star"
      unitLabel="reviews"
      api={hrApi.performance}
      columns={columns}
      fields={fields}
      emptyForm={{
        staff_id: '',
        reviewer_id: '',
        review_period_start: '',
        review_period_end: '',
        goals: '',
        rating: '',
        comments: '',
        development_plan: '',
        status: 'scheduled',
      }}
      toForm={(r) => ({
        staff_id: String(r.staff_id),
        reviewer_id: r.reviewer_id ? String(r.reviewer_id) : '',
        review_period_start: (r.review_period_start as string) ?? '',
        review_period_end: (r.review_period_end as string) ?? '',
        goals: r.goals ?? '',
        rating: (r.rating as number) ?? '',
        comments: r.comments ?? '',
        development_plan: r.development_plan ?? '',
        status: String(r.status ?? 'scheduled'),
      })}
      createDefaults={{ school_id: user?.school_id }}
      sort="id"
    />
  );
}
