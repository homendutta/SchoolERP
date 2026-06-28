/* Staff List — create (employee number auto-generated), search, filter, edit,
 * archive. Department & Designation come from Master Data. */
import { useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { AXBadge, type AXColumn } from '@ui/ax';
import {
  EntityManager,
  statusCell,
  type Field,
  type FieldOption,
} from '@features/academic/EntityManager';
import { useMasterValues } from '@features/academic/useReference';
import { staffApi, type Staff } from './api';
import { useStaffList } from './StaffPicker';

const EMPLOYMENT: FieldOption[] = [
  { value: 'full_time', label: 'Full Time' },
  { value: 'part_time', label: 'Part Time' },
  { value: 'contract', label: 'Contract' },
  { value: 'probation', label: 'Probation' },
  { value: 'temporary', label: 'Temporary' },
  { value: 'visiting', label: 'Visiting' },
];
const STATUS: FieldOption[] = [
  { value: 'applicant', label: 'Applicant' },
  { value: 'active', label: 'Active' },
  { value: 'on_leave', label: 'On Leave' },
  { value: 'suspended', label: 'Suspended' },
  { value: 'resigned', label: 'Resigned' },
  { value: 'retired', label: 'Retired' },
  { value: 'terminated', label: 'Terminated' },
  { value: 'archived', label: 'Archived' },
];
const TONES: Record<string, 'navy' | 'green' | 'amber' | 'red' | 'gray'> = {
  active: 'green',
  on_leave: 'amber',
  suspended: 'amber',
  resigned: 'red',
  terminated: 'red',
  retired: 'gray',
  applicant: 'navy',
};

export function StaffListPage() {
  const { user } = useAuth();
  const departments = useMasterValues('departments');
  const designations = useMasterValues('designations');
  const genders = useMasterValues('genders');
  const bloodGroups = useMasterValues('blood_groups');
  const managers = useStaffList();
  const [status, setStatus] = useState('');
  const [departmentId, setDepartmentId] = useState('');

  const managerOptions: FieldOption[] = managers.map((m) => ({
    value: String(m.id),
    label: `${m.employee_number} — ${m.name}`,
  }));

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    // Employee number is admin-editable (auto-generated when left blank).
    { name: 'employee_number', label: 'Employee number (optional)', type: 'text' },
    { name: 'gender_id', label: 'Gender', type: 'select', options: genders },
    { name: 'blood_group_id', label: 'Blood group', type: 'select', options: bloodGroups },
    { name: 'phone', label: 'Phone', type: 'text' },
    { name: 'email', label: 'Email', type: 'text' },
    { name: 'department_id', label: 'Department', type: 'select', options: departments },
    { name: 'designation_id', label: 'Designation', type: 'select', options: designations },
    {
      name: 'reporting_manager_id',
      label: 'Reporting manager',
      type: 'select',
      options: managerOptions,
    },
    { name: 'employment_type', label: 'Employment type', type: 'select', options: EMPLOYMENT },
    { name: 'joining_date', label: 'Joining date', type: 'date' },
    { name: 'status', label: 'Status', type: 'select', options: STATUS },
    { name: 'is_teaching', label: 'Teaching staff', type: 'checkbox' },
  ];

  const columns: AXColumn<Staff>[] = [
    {
      key: 'employee_number',
      header: 'Emp. No.',
      render: (r) => <code className="text-xs text-gray-500">{r.employee_number}</code>,
    },
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'department', header: 'Department', render: (r) => r.department?.label ?? '—' },
    { key: 'designation', header: 'Designation', render: (r) => r.designation?.label ?? '—' },
    {
      key: 'teaching',
      header: 'Type',
      render: (r) => (
        <AXBadge tone={r.is_teaching ? 'navy' : 'gray'}>
          {r.is_teaching ? 'Teaching' : 'Non-Teaching'}
        </AXBadge>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (r) => <AXBadge tone={TONES[r.status] ?? 'gray'}>{r.status}</AXBadge>,
    },
    { key: 'archived', header: '', render: statusCell },
  ];

  const filter: Record<string, string> = {};
  if (status) filter.status = status;
  if (departmentId) filter.department_id = departmentId;

  return (
    <EntityManager<Staff>
      title="Staff"
      icon="users"
      unitLabel="staff"
      api={staffApi.staff}
      columns={columns}
      fields={fields}
      emptyForm={{
        name: '',
        employee_number: '',
        gender_id: null,
        blood_group_id: null,
        phone: '',
        email: '',
        department_id: null,
        designation_id: null,
        reporting_manager_id: null,
        employment_type: 'full_time',
        joining_date: '',
        status: 'active',
        is_teaching: false,
      }}
      toForm={(r) => ({
        name: r.name,
        employee_number: r.employee_number,
        gender_id: r.gender_id,
        blood_group_id: r.blood_group_id,
        phone: r.phone ?? '',
        email: r.email ?? '',
        department_id: r.department_id,
        designation_id: r.designation_id,
        reporting_manager_id: r.reporting_manager_id,
        employment_type: r.employment_type ?? '',
        joining_date: r.joining_date ?? '',
        status: r.status,
        is_teaching: r.is_teaching,
      })}
      createDefaults={{ school_id: user?.school_id }}
      searchKey="name"
      searchPlaceholder="Search staff by name…"
      sort="name"
      listParams={Object.keys(filter).length ? { filter } : {}}
      filters={[
        { name: 'status', label: 'Status', options: STATUS, value: status, onChange: setStatus },
        {
          name: 'department_id',
          label: 'Department',
          options: departments,
          value: departmentId,
          onChange: setDepartmentId,
        },
      ]}
    />
  );
}
