/* Examination module API bindings. Reuses Academic subjects, Timetable periods,
 * Rooms, Staff and the Identity Platform. Optional/elective subjects are honoured
 * server-side: a student is only ever marked/graded on assigned subjects. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface ExamType {
  id: number;
  school_id: number;
  name: string;
  code: string | null;
  weightage: number | null;
  description: string | null;
  is_active: boolean;
  status: string;
}

export interface ExamSession {
  id: number;
  school_id: number;
  academic_year_id: number;
  academic_year?: string | null;
  term_id: number | null;
  term?: string | null;
  exam_type_id: number;
  exam_type?: string | null;
  name: string;
  start_date: string | null;
  end_date: string | null;
  status: string;
  ranking_method: string;
  description: string | null;
}

export interface ExamSubject {
  id: number;
  exam_session_id: number;
  class_id: number;
  class?: string | null;
  section_id: number | null;
  subject_id: number;
  subject?: string | null;
  is_elective: boolean;
  max_marks: number;
  passing_marks: number;
  status: string;
}

export interface ExamGrade {
  id: number;
  school_id: number;
  code: string;
  name: string | null;
  min_percentage: number;
  max_percentage: number;
  grade_point: number | null;
  remarks: string | null;
  is_failing: boolean;
  status: string;
}

export interface ExamComponent {
  id: number;
  school_id: number;
  name: string;
  code: string | null;
  is_active: boolean;
  status: string;
}

export interface ExamSchedule {
  id: number;
  exam_session_id: number;
  exam_subject_id: number;
  subject?: string | null;
  class?: string | null;
  exam_date: string | null;
  period_id: number | null;
  period?: string | null;
  room_id: number | null;
  room?: string | null;
  duration_minutes: number;
  status: string;
}

export interface ExamResult {
  id: number;
  exam_session_id: number;
  student_id: number;
  student?: string | null;
  admission_number?: string | null;
  class_id: number | null;
  class?: string | null;
  total_obtained: number;
  total_max: number;
  percentage: number;
  grade?: string | null;
  gpa: number | null;
  result_status: string;
  rank: number | null;
  subjects_count: number;
  failed_count: number;
  is_published: boolean;
}

export const EXAM_SESSION_STATUSES = ['draft', 'scheduled', 'ongoing', 'completed', 'published'];
export const RANKING_METHODS = ['dense', 'competition', 'none'];
export const EXAM_ATTENDANCE_STATUSES = ['present', 'absent', 'malpractice', 'medical_leave'];

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
    archive: (id: number) => apiClient.delete(`${base}/${id}`),
    restore: (id: number) => apiClient.post(`${base}/${id}`),
    bulkDelete: (ids: number[]) => apiClient.post(`${base}/bulk-delete`, { ids }),
  };
}

export interface AssignedStudent {
  student_id: number;
  student: string | null;
  admission_number: string | null;
}

export const examApi = {
  dashboard: (params: Record<string, unknown>) =>
    apiClient.get<Record<string, unknown>>(`/examinations/dashboard?${qs(params)}`),

  types: crud<ExamType>('/examinations/types'),
  grades: crud<ExamGrade>('/examinations/grades'),
  components: crud<ExamComponent>('/examinations/components'),
  reportCardTemplates: crud<Record<string, unknown>>('/examinations/report-card-templates'),
  sessions: crud<ExamSession>('/examinations/sessions'),
  subjects: crud<ExamSubject>('/examinations/subjects'),
  schedules: crud<ExamSchedule>('/examinations/schedules'),

  assignSubjects: (sessionId: number) =>
    apiClient.post(`/examinations/sessions/${sessionId}/assign-subjects`),
  processResults: (sessionId: number) =>
    apiClient.post(`/examinations/sessions/${sessionId}/process`),
  publishResults: (sessionId: number) =>
    apiClient.post(`/examinations/sessions/${sessionId}/publish`),

  subjectStudents: (subjectId: number) =>
    apiClient.get<AssignedStudent[]>(`/examinations/subjects/${subjectId}/students`),
  assignStudent: (subjectId: number, studentId: number) =>
    apiClient.post(`/examinations/subjects/${subjectId}/assign-student`, { student_id: studentId }),
  unassignStudent: (subjectId: number, studentId: number) =>
    apiClient.post(`/examinations/subjects/${subjectId}/unassign-student`, {
      student_id: studentId,
    }),

  invigilators: {
    list: (params: Record<string, unknown> = {}) =>
      apiPage<Record<string, unknown>>(`/examinations/invigilators?${qs(params)}`),
    create: (d: Record<string, unknown>) => apiClient.post('/examinations/invigilators', d),
    remove: (id: number) => apiClient.delete(`/examinations/invigilators/${id}`),
  },
  seating: {
    list: (params: Record<string, unknown> = {}) =>
      apiPage<Record<string, unknown>>(`/examinations/seating?${qs(params)}`),
    create: (d: Record<string, unknown>) => apiClient.post('/examinations/seating', d),
  },

  examAttendance: {
    list: (params: Record<string, unknown> = {}) =>
      apiPage<Record<string, unknown>>(`/examinations/attendance?${qs(params)}`),
    mark: (payload: Record<string, unknown>) =>
      apiClient.post<{ marked: number }>('/examinations/attendance', payload),
  },

  marks: {
    list: (examSubjectId: number) =>
      apiClient.get<Record<string, unknown>[]>(
        `/examinations/marks?${qs({ exam_subject_id: examSubjectId })}`
      ),
    save: (payload: Record<string, unknown>) =>
      apiClient.post<{ saved: number; skipped: number }>('/examinations/marks', payload),
  },

  results: (params: Record<string, unknown> = {}) =>
    apiPage<ExamResult>(`/examinations/results?${qs(params)}`),
  reportCard: (sessionId: number, studentId: number) =>
    apiClient.get<Record<string, unknown>>(
      `/examinations/report-cards?${qs({ exam_session_id: sessionId, student_id: studentId })}`
    ),
  tabulation: (params: Record<string, unknown>) =>
    apiClient.get<Record<string, unknown>>(`/examinations/tabulation?${qs(params)}`),
  promotionReadiness: (params: Record<string, unknown>) =>
    apiClient.get<Record<string, unknown>>(`/examinations/promotion-readiness?${qs(params)}`),
};

export type { PageMeta };
