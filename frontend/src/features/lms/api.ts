/* Learning Management System API bindings (Sprint 19). Portal-authenticated.
 * Teachers manage content for their assigned subjects; students/parents are
 * isolated server-side. Homework/assignments/quizzes are independent of the
 * Examination module; files use the Media Platform. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Ref {
  id: number;
  status?: string;
  archived?: boolean;
  [k: string]: unknown;
}

export const LMS_STATUSES = ['draft', 'published', 'scheduled', 'archived'];
export const MATERIAL_TYPES = ['pdf', 'docx', 'ppt', 'xls', 'image', 'audio', 'video', 'zip'];
export const QUESTION_TYPES = ['multiple_choice', 'true_false', 'short_answer', 'fill_blank'];
export const REVIEW_ACTIONS = ['comment', 'grade', 'return', 'approve'];

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

export const lmsApi = {
  dashboard: () => apiClient.get<Record<string, unknown>>('/lms/dashboard'),
  progress: (studentId: number) =>
    apiClient.get<Record<string, unknown>>(`/lms/progress?${qs({ student_id: studentId })}`),

  lessonPlans: crud('/lms/lesson-plans'),
  lessons: crud('/lms/lessons'),
  materials: crud('/lms/materials'),
  homework: crud('/lms/homework'),
  assignments: crud('/lms/assignments'),
  resources: crud('/lms/resources'),
  quizzes: crud('/lms/quizzes'),
  discussions: crud('/lms/discussions'),

  // Submissions + reviews
  submissions: (params: Record<string, unknown>) =>
    apiClient.get<Array<Record<string, unknown>>>(`/lms/submissions?${qs(params)}`),
  submit: (payload: Record<string, unknown>) => apiClient.post('/lms/submissions', payload),
  review: (payload: Record<string, unknown>) => apiClient.post('/lms/reviews', payload),

  // Quiz attempts
  attempts: (params: Record<string, unknown>) =>
    apiClient.get<Array<Record<string, unknown>>>(`/lms/attempts?${qs(params)}`),
  attempt: (payload: Record<string, unknown>) => apiClient.post('/lms/attempts', payload),

  // Discussion replies + moderation
  post: (discussionId: number, payload: Record<string, unknown>) =>
    apiClient.post(`/lms/discussions/${discussionId}/posts`, payload),
  moderate: (postId: number) => apiClient.post(`/lms/discussions/posts/${postId}/moderate`),
};

export type { PageMeta };
