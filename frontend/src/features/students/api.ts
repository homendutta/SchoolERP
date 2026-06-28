/* Students module API bindings — profile, search, timeline, medical, documents,
 * academic records, transfers, withdrawals, promotion, import, export, dashboard,
 * ID-card/QR. Students are never created here. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface StudentGuardian {
  id: number;
  name: string;
  phone: string | null;
  parent_number: string | null;
  relationship_type_id: number | null;
  is_primary: boolean;
  emergency_contact: boolean;
  pickup_authorized: boolean;
  financial_responsible: boolean;
  notes: string | null;
}

export interface Student {
  id: number;
  uuid: string;
  school_id: number;
  identity_id: number | null;
  admission_number: string;
  name: string;
  phone: string | null;
  email: string | null;
  gender: string | null;
  date_of_birth: string | null;
  religion: string | null;
  nationality: string | null;
  category: string | null;
  address: string | null;
  city: string | null;
  state: string | null;
  postal_code: string | null;
  blood_group_id: number | null;
  blood_group?: { id: number; label: string; value: string };
  allergies: string | null;
  disabilities: string | null;
  medical_notes: string | null;
  emergency_instructions: string | null;
  notes: string | null;
  status: string;
  enrolled_on: string | null;
  photo_media_id: number | null;
  current_record: {
    id: number;
    academic_year?: { id: number; name: string };
    class?: { id: number; name: string };
    section?: { id: number; name: string };
    roll_number: string | null;
  } | null;
  guardians?: StudentGuardian[];
  archived: boolean;
}

export interface TimelineEntry {
  id: number;
  student_id: number;
  event_type: string;
  title: string;
  description: string | null;
  metadata: Record<string, unknown> | null;
  created_at: string | null;
}

export interface AcademicRecord {
  id: number;
  academic_year?: { id: number; name: string };
  class?: { id: number; name: string };
  section?: { id: number; name: string };
  roll_number: string | null;
  admission_number: string | null;
  status: string;
  is_current: boolean;
  promoted_from_record_id: number | null;
  started_on: string | null;
  ended_on: string | null;
}

export interface StudentDoc {
  id: number;
  student_id: number;
  document_type_id: number | null;
  document_type?: { id: number; label: string; value: string };
  media_id: number | null;
  media?: { id: number; uuid: string; url: string | null };
  title: string | null;
  status: string;
}

export interface Transfer {
  id: number;
  student_id: number;
  type: string;
  to_class_id: number | null;
  to_section_id: number | null;
  transfer_date: string | null;
  reason: string | null;
  destination_school: string | null;
  notes: string | null;
}

export interface Withdrawal {
  id: number;
  student_id: number;
  withdraw_date: string | null;
  reason: string | null;
  remarks: string | null;
}

export interface IdCard {
  admission_number: string;
  name: string;
  photo_url: string | null;
  class: string | null;
  section: string | null;
  guardian: string | null;
  blood_group: string | null;
  qr_data: string;
}

export interface StudentDashboardData {
  widgets: {
    total_students: number;
    active: number;
    withdrawn: number;
    graduated: number;
    promoted: number;
    transfers: number;
    new_admissions: number;
  };
  charts: {
    monthly_admissions: Array<{ month: string; count: number }>;
    promotions: Array<{ month: string; count: number }>;
    withdrawals: Array<{ month: string; count: number }>;
    gender_distribution: Array<{ label: string; count: number }>;
    class_distribution: Array<{ label: string; count: number }>;
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

export const studentsApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<StudentDashboardData>(
      `/students/dashboard${schoolId ? `?school_id=${schoolId}` : ''}`
    ),

  list: (params: Record<string, unknown> = {}) => apiPage<Student>(`/students?${qs(params)}`),
  get: (id: number) => apiClient.get<Student>(`/students/${id}`),
  update: (id: number, d: Record<string, unknown>) => apiClient.put<Student>(`/students/${id}`, d),
  idCard: (id: number) => apiClient.get<IdCard>(`/students/${id}/id-card`),

  timeline: (studentId: number) =>
    apiClient.get<TimelineEntry[]>(`/student-timeline?student_id=${studentId}`),

  updateMedical: (id: number, d: Record<string, unknown>) =>
    apiClient.put<Student>(`/student-medical/${id}`, d),

  documents: {
    list: (studentId: number) =>
      apiPage<StudentDoc>(`/student-documents?${qs({ filter: { student_id: studentId } })}`),
    create: (d: Record<string, unknown>) => apiClient.post<StudentDoc>('/student-documents', d),
    remove: (id: number) => apiClient.delete(`/student-documents/${id}`),
  },

  academicRecords: (studentId: number) =>
    apiPage<AcademicRecord>(
      `/student-academic-records?${qs({ filter: { student_id: studentId }, per_page: 100 })}`
    ),

  transfers: {
    list: (studentId: number) =>
      apiClient.get<Transfer[]>(`/student-transfer?student_id=${studentId}`),
    create: (studentId: number, d: Record<string, unknown>) =>
      apiClient.post<Transfer>(`/student-transfer/${studentId}`, d),
  },

  withdrawals: {
    list: (studentId: number) =>
      apiClient.get<Withdrawal[]>(`/student-withdrawal?student_id=${studentId}`),
    create: (studentId: number, d: Record<string, unknown>) =>
      apiClient.post<Withdrawal>(`/student-withdrawal/${studentId}`, d),
  },

  promote: (studentId: number, d: Record<string, unknown>) =>
    apiClient.post<Student>(`/student-promotion/${studentId}`, d),

  import: {
    validate: (rows: Record<string, unknown>[]) =>
      apiClient.post<{ valid: boolean; errors: Record<number, string[]> }>(
        '/student-import/validate',
        { rows }
      ),
    execute: (rows: Record<string, unknown>[]) =>
      apiClient.post<{ created: number; skipped: number }>('/student-import/execute', { rows }),
  },

  exportUrl: (params: Record<string, unknown> = {}) =>
    `/api/v1/student-export?${qs({ format: 'csv', ...params })}`,
};

export type { PageMeta };
