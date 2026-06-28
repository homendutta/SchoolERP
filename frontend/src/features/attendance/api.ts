/* Attendance module API bindings — one engine, three sources (manual / import /
 * biometric). Read endpoints are identity-based and scoped by owner. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface AttendanceRecord {
  id: number;
  uuid: string;
  identity_id: number;
  identity_number: string | null;
  owner?: { id: number; name: string | null; type: string };
  owner_type: string;
  class_id: number | null;
  section_id: number | null;
  department_id: number | null;
  session_id: number | null;
  session?: { id: number; label: string; value: string };
  shift: string | null;
  attendance_date: string | null;
  status: string;
  source: string;
  check_in_time: string | null;
  check_out_time: string | null;
  is_late: boolean;
  remarks: string | null;
}

export interface Device {
  id: number;
  school_id: number;
  name: string;
  device_type: string;
  device_identifier: string;
  location: string | null;
  status: string;
  last_sync_at: string | null;
}

export interface BiometricLog {
  id: number;
  device_id: number | null;
  device?: { id: number; name: string; device_identifier: string };
  identity_number: string | null;
  event_time: string | null;
  direction: string | null;
  processing_status: string;
  attendance_id: number | null;
  created_at: string | null;
}

export interface AttendanceDashboardData {
  widgets: {
    present: number;
    absent: number;
    late: number;
    leave: number;
    attendance_percentage: number;
  };
  charts: {
    daily: Array<{ period: string; count: number }>;
    weekly: Array<{ period: string; count: number }>;
    monthly: Array<{ period: string; count: number }>;
  };
}

export const ATTENDANCE_STATUS = [
  'present',
  'absent',
  'late',
  'half_day',
  'leave',
  'holiday',
  'weekend',
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

export const attendanceApi = {
  dashboard: (type: 'student' | 'staff', schoolId?: number) =>
    apiClient.get<AttendanceDashboardData>(
      `/attendance/dashboard?${qs({ type, school_id: schoolId })}`
    ),

  student: (params: Record<string, unknown> = {}) =>
    apiPage<AttendanceRecord>(`/attendance/student?${qs(params)}`),
  staff: (params: Record<string, unknown> = {}) =>
    apiPage<AttendanceRecord>(`/attendance/staff?${qs(params)}`),

  mark: (payload: Record<string, unknown>) =>
    apiClient.post<{ marked: number; skipped: number; unmatched: number }>(
      '/attendance/manual',
      payload
    ),
  correct: (id: number, payload: Record<string, unknown>) =>
    apiClient.put<AttendanceRecord>(`/attendance/manual/${id}`, payload),

  devices: crud<Device>('/attendance/devices'),

  biometricLogs: (params: Record<string, unknown> = {}) =>
    apiPage<BiometricLog>(`/attendance/biometric/logs?${qs(params)}`),
  biometricEvent: (payload: Record<string, unknown>) =>
    apiClient.post('/attendance/biometric/events', payload),

  import: {
    validate: (rows: Record<string, unknown>[]) =>
      apiClient.post<{ valid: boolean; errors: Record<number, string[]> }>(
        '/attendance/import/validate',
        { rows }
      ),
    execute: (rows: Record<string, unknown>[]) =>
      apiClient.post<{ marked: number; skipped: number; unmatched: number }>(
        '/attendance/import/execute',
        { rows }
      ),
  },
};

export type { PageMeta };
