/* Human Resources API bindings (Sprint 16A). Employee lifecycle: departments &
 * designations (Number Generator codes), employment history (never overwritten),
 * documents (Media refs), shifts, attendance policies (consumed by Attendance),
 * leave (Leave Engine), holidays, performance, training, discipline, separation.
 * Payroll is Sprint 16B. Notifications go through the Communication Engine. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Ref {
  id: number;
  status?: string;
  archived?: boolean;
  [k: string]: unknown;
}

export const RECORD_STATUSES = ['active', 'archived'];
export const EMPLOYMENT_TYPES = [
  'full_time',
  'part_time',
  'contract',
  'probation',
  'temporary',
  'visiting',
];
export const EMPLOYMENT_STATUSES = ['active', 'on_leave', 'suspended', 'separated'];
export const LEAVE_STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];
export const HOLIDAY_TYPES = ['national', 'state', 'school', 'optional'];
export const REVIEW_STATUSES = ['draft', 'scheduled', 'in_progress', 'completed'];
export const TRAINING_STATUSES = ['planned', 'ongoing', 'completed', 'cancelled'];
export const DISCIPLINARY_ACTIONS = [
  'warning',
  'suspension',
  'notice',
  'termination_recommendation',
  'other',
];
export const SEPARATION_TYPES = [
  'resignation',
  'retirement',
  'termination',
  'contract_completion',
  'death',
];
export const CLEARANCE_STATUSES = ['pending', 'in_progress', 'completed'];
export const DOCUMENT_TYPES = [
  'appointment_letter',
  'contract',
  'certificate',
  'identity',
  'qualification',
  'experience',
];

export const qs = (params: Record<string, unknown>) =>
  new URLSearchParams(
    Object.entries(params).flatMap(([k, v]) =>
      v && typeof v === 'object'
        ? Object.entries(v as Record<string, unknown>).flatMap(([k2, v2]) =>
            v2 !== undefined && v2 !== '' && v2 !== null ? [[`${k}[${k2}]`, String(v2)]] : []
          )
        : v !== undefined && v !== '' && v !== null
          ? [[k, String(v)]]
          : []
    ) as [string, string][]
  ).toString();

function crud<T = Ref>(base: string) {
  return {
    list: (params: Record<string, unknown> = {}) => apiPage<T>(`${base}?${qs(params)}`),
    create: (d: Record<string, unknown>) => apiClient.post<T>(base, d),
    update: (id: number, d: Record<string, unknown>) => apiClient.put<T>(`${base}/${id}`, d),
    archive: (id: number) => apiClient.delete(`${base}/${id}`),
    restore: (id: number) => apiClient.post(`${base}/${id}`),
    bulkDelete: (ids: number[]) => apiClient.post(`${base}/bulk-delete`, { ids }),
  };
}

export const hrApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/hr/dashboard?${qs({ school_id: schoolId })}`),

  departments: crud('/hr/departments'),
  designations: crud('/hr/designations'),
  employment: crud('/hr/employment'),
  employeeDocuments: crud('/hr/employee-documents'),
  shifts: crud('/hr/shifts'),
  attendancePolicies: crud('/hr/attendance-policies'),
  leaveTypes: crud('/hr/leave-types'),
  leavePolicies: crud('/hr/leave-policies'),
  holidays: crud('/hr/holidays'),
  performance: crud('/hr/performance'),
  training: crud('/hr/training'),
  discipline: crud('/hr/discipline'),
  separation: crud('/hr/separation'),

  // Leave requests — reads here; all writes go through the Leave Engine.
  leaveRequests: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/hr/leave-requests?${qs(params)}`),
  applyLeave: (payload: Record<string, unknown>) => apiClient.post('/hr/leave-requests', payload),
  approveLeave: (id: number, notes?: string) =>
    apiClient.post(`/hr/leave-requests/${id}/approve`, { notes }),
  rejectLeave: (id: number, notes?: string) =>
    apiClient.post(`/hr/leave-requests/${id}/reject`, { notes }),
  cancelLeave: (id: number) => apiClient.post(`/hr/leave-requests/${id}/cancel`),
  leaveBalances: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/hr/leave-balances?${qs(params)}`),

  // Training participants
  assignTraining: (trainingId: number, staffId: number) =>
    apiClient.post(`/hr/training/${trainingId}/participants`, { staff_id: staffId }),
};

export type { PageMeta };
