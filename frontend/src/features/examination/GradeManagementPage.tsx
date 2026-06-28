/* Grade Management — configurable grading scale + marks components. */
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import { EntityManager, type Field } from '@features/academic/EntityManager';
import { examApi, type ExamComponent, type ExamGrade } from './api';

const gradeFields: Field[] = [
  { name: 'code', label: 'Grade code', type: 'text', required: true },
  { name: 'name', label: 'Name', type: 'text' },
  { name: 'min_percentage', label: 'Min %', type: 'number', required: true },
  { name: 'max_percentage', label: 'Max %', type: 'number', required: true },
  { name: 'grade_point', label: 'Grade point', type: 'number' },
  { name: 'remarks', label: 'Remarks', type: 'text' },
  { name: 'is_failing', label: 'Failing grade', type: 'checkbox' },
];

const componentFields: Field[] = [
  { name: 'name', label: 'Name', type: 'text', required: true },
  { name: 'code', label: 'Code', type: 'text' },
];

export function GradeManagementPage() {
  const { user } = useAuth();

  const gradeColumns: AXColumn<ExamGrade>[] = [
    {
      key: 'code',
      header: 'Grade',
      render: (r) => <span className="font-semibold text-[var(--navy-primary)]">{r.code}</span>,
    },
    { key: 'range', header: 'Range', render: (r) => `${r.min_percentage}% – ${r.max_percentage}%` },
    { key: 'gp', header: 'Grade Point', render: (r) => r.grade_point ?? '—' },
    { key: 'remarks', header: 'Remarks', render: (r) => r.remarks ?? '—' },
    {
      key: 'fail',
      header: '',
      render: (r) => (r.is_failing ? <AXBadge tone="red">fail</AXBadge> : null),
    },
  ];

  const componentColumns: AXColumn<ExamComponent>[] = [
    {
      key: 'name',
      header: 'Component',
      render: (r) => <span className="font-medium">{r.name}</span>,
    },
    { key: 'code', header: 'Code', render: (r) => r.code ?? '—' },
  ];

  return (
    <div className="space-y-6">
      <EntityManager<ExamGrade>
        title="Grading Scale"
        icon="award"
        unitLabel="grades"
        api={examApi.grades}
        columns={gradeColumns}
        fields={gradeFields}
        emptyForm={{
          code: '',
          name: '',
          min_percentage: 0,
          max_percentage: 0,
          grade_point: '',
          remarks: '',
          is_failing: false,
        }}
        toForm={(r) => ({
          code: r.code,
          name: r.name ?? '',
          min_percentage: r.min_percentage,
          max_percentage: r.max_percentage,
          grade_point: r.grade_point ?? '',
          remarks: r.remarks ?? '',
          is_failing: r.is_failing,
        })}
        createDefaults={{ school_id: user?.school_id }}
        sort="min_percentage"
      />

      <EntityManager<ExamComponent>
        title="Marks Components"
        icon="layer-group"
        unitLabel="components"
        api={examApi.components}
        columns={componentColumns}
        fields={componentFields}
        emptyForm={{ name: '', code: '' }}
        toForm={(r) => ({ name: r.name, code: r.code ?? '' })}
        createDefaults={{ school_id: user?.school_id }}
        searchKey="name"
        searchPlaceholder="Search components…"
        sort="sort_order"
      />
    </div>
  );
}
