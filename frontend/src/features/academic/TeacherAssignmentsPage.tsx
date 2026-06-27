/* Teacher Subject Assignment — Teacher → Year → Class → Section → Subject.
 * Multiple teachers per subject are supported. */
import { useState } from 'react';
import { AXBadge, type AXColumn } from '@ui/ax';
import { academicApi, type TeacherSubjectAssignment } from './api';
import { EntityManager, type Field } from './EntityManager';
import { useAllSections, useClasses, useSubjects, useTeachers, useYears } from './useReference';

export function TeacherAssignmentsPage() {
  const years = useYears();
  const classes = useClasses();
  const sections = useAllSections();
  const subjects = useSubjects();
  const teachers = useTeachers();
  const [yearId, setYearId] = useState('');
  const [classId, setClassId] = useState('');

  const fields: Field[] = [
    {
      name: 'academic_year_id',
      label: 'Academic year',
      type: 'select',
      options: years,
      required: true,
    },
    { name: 'class_id', label: 'Class', type: 'select', options: classes, required: true },
    { name: 'section_id', label: 'Section', type: 'select', options: sections, required: true },
    { name: 'subject_id', label: 'Subject', type: 'select', options: subjects, required: true },
    { name: 'teacher_id', label: 'Teacher', type: 'select', options: teachers, required: true },
    { name: 'is_primary', label: 'Primary teacher', type: 'checkbox' },
  ];

  const columns: AXColumn<TeacherSubjectAssignment>[] = [
    {
      key: 'teacher',
      header: 'Teacher',
      render: (r) => <span className="font-medium">{r.teacher?.name ?? `#${r.teacher_id}`}</span>,
    },
    {
      key: 'subject',
      header: 'Subject',
      render: (r) => (r.subject ? `${r.subject.code} — ${r.subject.name}` : `#${r.subject_id}`),
    },
    { key: 'class', header: 'Class', render: (r) => r.class?.name ?? `#${r.class_id}` },
    { key: 'section', header: 'Section', render: (r) => r.section?.name ?? `#${r.section_id}` },
    {
      key: 'is_primary',
      header: 'Primary',
      render: (r) =>
        r.is_primary ? (
          <AXBadge tone="green">Primary</AXBadge>
        ) : (
          <span className="text-gray-300">—</span>
        ),
    },
  ];

  const listFilter: Record<string, string> = {};
  if (yearId) listFilter.academic_year_id = yearId;
  if (classId) listFilter.class_id = classId;

  return (
    <EntityManager<TeacherSubjectAssignment>
      title="Teacher Subject Assignments"
      icon="chalkboard-user"
      unitLabel="assignments"
      api={academicApi.teacherAssignments}
      columns={columns}
      fields={fields}
      emptyForm={{
        academic_year_id: yearId || '',
        class_id: classId || '',
        section_id: '',
        subject_id: '',
        teacher_id: '',
        is_primary: false,
      }}
      toForm={(r) => ({
        academic_year_id: r.academic_year_id,
        class_id: r.class_id,
        section_id: r.section_id,
        subject_id: r.subject_id,
        teacher_id: r.teacher_id,
        is_primary: r.is_primary,
      })}
      listParams={Object.keys(listFilter).length ? { filter: listFilter } : {}}
      filters={[
        {
          name: 'academic_year_id',
          label: 'Year',
          options: years,
          value: yearId,
          onChange: setYearId,
        },
        {
          name: 'class_id',
          label: 'Class',
          options: classes,
          value: classId,
          onChange: setClassId,
        },
      ]}
    />
  );
}
