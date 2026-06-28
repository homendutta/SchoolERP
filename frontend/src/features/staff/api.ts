/* Staff module API bindings — profile, search, qualifications, experience,
 * documents, timeline, import, export, dashboard. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Staff {
  id: number;
  uuid: string;
  school_id: number;
  identity_id: number | null;
  employee_number: string;
  name: string;
  gender_id: number | null;
  gender?: { id: number; label: string; value: string };
  date_of_birth: string | null;
  marital_status: string | null;
  blood_group_id: number | null;
  blood_group?: { id: number; label: string; value: string };
  phone: string | null;
  email: string | null;
  address: string | null;
  city: string | null;
  state: string | null;
  postal_code: string | null;
  department_id: number | null;
  department?: { id: number; label: string; value: string };
  designation_id: number | null;
  designation?: { id: number; label: string; value: string };
  employment_type: string | null;
  joining_date: string | null;
  confirmation_date: string | null;
  reporting_manager_id: number | null;
  is_teaching: boolean;
  status: string;
  photo_media_id: number | null;
  notes: string | null;
  qualifications?: Qualification[];
  experiences?: Experience[];
  documents?: StaffDoc[];
  archived: boolean;
}

export interface Qualification {
  id: number;
  staff_id: number;
  qualification: string;
  institution: string | null;
  board_university: string | null;
  year: string | null;
  grade: string | null;
  certificate_media_id: number | null;
}

export interface Experience {
  id: number;
  staff_id: number;
  organization: string;
  designation: string | null;
  from_date: string | null;
  to_date: string | null;
  reason_for_leaving: string | null;
  certificate_media_id: number | null;
}

export interface StaffDoc {
  id: number;
  staff_id: number;
  document_type_id: number | null;
  document_type?: { id: number; label: string; value: string };
  media_id: number | null;
  media?: { id: number; uuid: string; url: string | null };
  title: string | null;
  status: string;
}

export interface TimelineEntry {
  id: number;
  staff_id: number;
  event_type: string;
  title: string;
  description: string | null;
  created_at: string | null;
}

export interface StaffDashboardData {
  widgets: {
    total_staff: number;
    teaching_staff: number;
    non_teaching_staff: number;
    active: number;
    on_leave: number;
    new_joinees: number;
    resigned: number;
  };
  charts: {
    department_distribution: Array<{ label: string; count: number }>;
    designation_distribution: Array<{ label: string; count: number }>;
    monthly_joining: Array<{ month: string; count: number }>;
  };
}

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

function crud<T>(base: string) {
  return {
    list: (params: Record<string, unknown> = {}) => apiPage<T>(`${base}?${qs(params)}`),
    create: (d: Record<string, unknown>) => apiClient.post<T>(base, d),
    update: (id: number, d: Record<string, unknown>) => apiClient.put<T>(`${base}/${id}`, d),
    archive: (id: number) => apiClient.post(`${base}/${id}/archive`),
    restore: (id: number) => apiClient.post(`${base}/${id}/restore`),
    bulkDelete: (ids: number[]) => apiClient.post(`${base}/bulk-delete`, { ids }),
  };
}

export const staffApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<StaffDashboardData>(
      `/staff/dashboard${schoolId ? `?school_id=${schoolId}` : ''}`
    ),

  staff: {
    ...crud<Staff>('/staff'),
    get: (id: number) => apiClient.get<Staff>(`/staff/${id}`),
  },
  qualifications: crud<Qualification>('/staff-qualifications'),
  experience: crud<Experience>('/staff-experience'),
  documents: crud<StaffDoc>('/staff-documents'),

  timeline: (staffId: number) =>
    apiClient.get<TimelineEntry[]>(`/staff-timeline?staff_id=${staffId}`),

  import: {
    validate: (rows: Record<string, unknown>[]) =>
      apiClient.post<{ valid: boolean; errors: Record<number, string[]> }>(
        '/staff-import/validate',
        { rows }
      ),
    execute: (rows: Record<string, unknown>[]) =>
      apiClient.post<{ created: number; skipped: number }>('/staff-import/execute', { rows }),
  },

  exportUrl: (params: Record<string, unknown> = {}) =>
    `/api/v1/staff-export?${qs({ format: 'csv', ...params })}`,
};

export type { PageMeta };
