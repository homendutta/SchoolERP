/* Admissions module API bindings — Enquiries, Applications, Documents,
 * Verification, Approval, Enrollment, Import, Dashboard. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Enquiry {
  id: number;
  school_id: number;
  academic_year_id: number | null;
  enquiry_number: string;
  student_name: string;
  guardian_name: string | null;
  phone: string | null;
  email: string | null;
  class_interested: string | null;
  source_id: number | null;
  status: string;
  remarks: string | null;
  follow_up_date: string | null;
  archived: boolean;
}

export interface ApprovalStep {
  id: number;
  application_id: number;
  name: string;
  role_slug: string | null;
  sort_order: number;
  status: string;
  actor_id: number | null;
  acted_at: string | null;
  remarks: string | null;
}

export interface AdmissionDocument {
  id: number;
  application_id: number;
  document_type_id: number | null;
  document_type?: { id: number; label: string; value: string };
  media_id: number | null;
  title: string | null;
  status: string;
  remarks: string | null;
}

export interface Application {
  id: number;
  school_id: number;
  application_number: string;
  academic_year_id: number;
  class_id: number;
  section_id: number | null;
  student_name: string;
  gender: string | null;
  date_of_birth: string | null;
  guardian_name: string;
  guardian_relation: string | null;
  guardian_phone: string | null;
  guardian_email: string | null;
  address: string | null;
  previous_school: string | null;
  previous_class: string | null;
  remarks: string | null;
  status: string;
  verification_status: string;
  enrolled_student_id: number | null;
  documents?: AdmissionDocument[];
  approval_steps?: ApprovalStep[];
  archived: boolean;
}

export interface WorkflowStep {
  id: number;
  school_id: number;
  name: string;
  role_slug: string | null;
  sort_order: number;
  is_active: boolean;
}

export interface VerificationLog {
  id: number;
  application_id: number;
  document_id: number | null;
  from_status: string | null;
  to_status: string;
  remarks: string | null;
  actor_id: number | null;
  created_at: string | null;
}

export interface EnrollmentResult {
  application: Application;
  student: { id: number; admission_number: string; name: string };
  guardian: { id: number; parent_number: string; name: string };
  credentials: {
    student: { username: string; password: string };
    parent: { username: string; password: string };
  };
}

export interface DashboardData {
  widgets: {
    today_enquiries: number;
    pending_applications: number;
    approved: number;
    rejected: number;
    month_admissions: number;
    conversion_rate: number;
  };
  charts: {
    monthly_admissions: Array<{ month: string; count: number }>;
    enquiry_sources: Array<{ label: string; count: number }>;
    status_distribution: Array<{ label: string; count: number }>;
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

export const admissionsApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<DashboardData>(
      `/admissions/dashboard${schoolId ? `?school_id=${schoolId}` : ''}`
    ),

  enquiries: crud<Enquiry>('/admissions/enquiries'),

  applications: {
    ...crud<Application>('/admissions/applications'),
    get: (id: number) => apiClient.get<Application>(`/admissions/applications/${id}`),
    submit: (id: number) => apiClient.post<Application>(`/admissions/applications/${id}/submit`),
  },

  documents: crud<AdmissionDocument>('/admissions/documents'),

  verification: {
    application: (id: number, status: string, remarks?: string) =>
      apiClient.post<Application>(`/admissions/verification/applications/${id}`, {
        status,
        remarks,
      }),
    document: (id: number, status: string, remarks?: string) =>
      apiClient.post<AdmissionDocument>(`/admissions/verification/documents/${id}`, {
        status,
        remarks,
      }),
    history: (id: number) =>
      apiClient.get<VerificationLog[]>(`/admissions/verification/applications/${id}/history`),
  },

  workflowSteps: crud<WorkflowStep>('/admissions/approval/workflow-steps'),

  approval: {
    start: (id: number) =>
      apiClient.post<Application>(`/admissions/approval/applications/${id}/start`),
    act: (stepId: number, decision: string, remarks?: string) =>
      apiClient.post<Application>(`/admissions/approval/steps/${stepId}/act`, {
        decision,
        remarks,
      }),
  },

  enroll: (id: number) => apiClient.post<EnrollmentResult>(`/admissions/enroll/${id}`),

  import: {
    validate: (rows: Record<string, unknown>[]) =>
      apiClient.post<{ valid: boolean; errors: Record<number, string[]> }>(
        '/admissions/import/validate',
        { rows }
      ),
    execute: (rows: Record<string, unknown>[]) =>
      apiClient.post<{ created: number; skipped: number }>('/admissions/import/execute', { rows }),
  },
};

export type { PageMeta };
