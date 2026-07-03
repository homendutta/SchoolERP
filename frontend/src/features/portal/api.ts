/* Parent / Student / Teacher Portal API bindings (Sprint 18).
 * The portal is a pure CONSUMER of the ERP — these endpoints delegate to the
 * owning modules and are isolated to the caller's own data. Online fee payment
 * reuses the Finance Payment Engine (parents may pay for multiple children). */
import { apiClient } from '@core/api/client';

export interface PortalContext {
  role: 'parent' | 'student' | 'teacher';
  students: Array<{ id: number; name: string | null; admission_number?: string | null }>;
  staff: { id: number; name: string; employee_number: string } | null;
}

export interface PayItem {
  student_id: number;
  amount: number;
  reference?: string | null;
}

const qs = (params: Record<string, unknown>) =>
  new URLSearchParams(
    Object.entries(params).flatMap(([k, v]) =>
      v !== undefined && v !== null && v !== '' ? [[k, String(v)]] : []
    ) as [string, string][]
  ).toString();

export const portalApi = {
  me: () => apiClient.get<PortalContext>('/portal/me'),
  dashboard: () => apiClient.get<Record<string, unknown>>('/portal/dashboard'),
  changePassword: (payload: Record<string, unknown>) =>
    apiClient.post('/portal/change-password', payload),

  attendance: (studentId: number) =>
    apiClient.get<Record<string, unknown>>(`/portal/attendance?${qs({ student_id: studentId })}`),
  examinations: (studentId: number, sessionId?: number) =>
    apiClient.get<Record<string, unknown>>(
      `/portal/examinations?${qs({ student_id: studentId, session_id: sessionId })}`
    ),
  library: (studentId: number) =>
    apiClient.get<Array<Record<string, unknown>>>(
      `/portal/library?${qs({ student_id: studentId })}`
    ),
  transport: (studentId: number) =>
    apiClient.get<Record<string, unknown> | null>(
      `/portal/transport?${qs({ student_id: studentId })}`
    ),
  hostel: (studentId: number) =>
    apiClient.get<Record<string, unknown> | null>(
      `/portal/hostel?${qs({ student_id: studentId })}`
    ),
  timetable: (studentId?: number) =>
    apiClient.get<Array<Record<string, unknown>>>(
      `/portal/timetable?${qs({ student_id: studentId })}`
    ),
  messages: () => apiClient.get<Record<string, unknown>>('/portal/messages'),
  downloads: () => apiClient.get<Array<Record<string, unknown>>>('/portal/downloads'),

  // Finance (parents + students)
  fees: (studentId: number) =>
    apiClient.get<Record<string, unknown>>(`/portal/fees?${qs({ student_id: studentId })}`),
  feeHistory: (studentId: number) =>
    apiClient.get<Array<Record<string, unknown>>>(
      `/portal/fees/history?${qs({ student_id: studentId })}`
    ),
  receipt: (paymentId: number) =>
    apiClient.get<Record<string, unknown>>(`/portal/fees/receipt/${paymentId}`),
  gateways: () => apiClient.get<{ providers: string[] }>('/portal/payment-gateways'),
  pay: (items: PayItem[], gateway?: string) =>
    apiClient.post<Record<string, unknown>>('/portal/fees/pay', { items, gateway }),

  profile: () => apiClient.get<Record<string, unknown>>('/portal/profile'),
  updateProfile: (payload: Record<string, unknown>) => apiClient.put('/portal/profile', payload),
};
