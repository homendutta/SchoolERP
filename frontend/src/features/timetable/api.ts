/* Timetable module API bindings. The class timetable is the single source of
 * truth; teacher and room timetables are derived. Writes run clash detection
 * server-side. */
import { useEffect, useState } from 'react';
import { apiClient, apiPage, type PageMeta } from '@core/api/client';
import { staffApi } from '@features/staff/api';
import type { FieldOption } from '@features/academic/EntityManager';

export interface Period {
  id: number;
  school_id: number;
  name: string;
  code: string | null;
  start_time: string | null;
  end_time: string | null;
  sort_order: number;
  is_break: boolean;
  status: string;
}

export interface WorkingDay {
  id: number;
  school_id: number;
  weekday: string;
  is_working: boolean;
  sort_order: number;
}

export interface Template {
  id: number;
  school_id: number;
  academic_year_id: number | null;
  academic_year?: string | null;
  name: string;
  code: string | null;
  description: string | null;
  is_active: boolean;
  status: string;
  entries_count?: number;
}

export interface TimetableSlot {
  id: number;
  school_id: number;
  template_id: number | null;
  academic_year_id: number;
  class_id: number;
  class?: string | null;
  section_id: number | null;
  section?: string | null;
  weekday: string;
  period_id: number;
  period?: string | null;
  subject_id: number;
  subject?: string | null;
  teacher_id: number | null;
  teacher?: string | null;
  room_id: number | null;
  room?: string | null;
  status: string;
}

export interface Substitution {
  id: number;
  school_id: number;
  original_teacher_id: number | null;
  original_teacher?: string | null;
  substitute_teacher_id: number;
  substitute_teacher?: string | null;
  date: string | null;
  period_id: number;
  period?: string | null;
  class_id: number | null;
  class?: string | null;
  subject_id: number | null;
  reason: string | null;
  status: string;
}

export interface Workload {
  teacher_id: number;
  periods_per_week: number;
  periods_per_day: Array<{ weekday: string; count: number }>;
  subject_load: Array<{ subject_id: number; count: number }>;
  class_load: Array<{ class_id: number; section_id: number | null; count: number }>;
}

export interface TimetableDashboardData {
  widgets: {
    total_timetables: number;
    teacher_load: number;
    room_usage: number;
    daily_classes: number;
  };
  charts: {
    teacher_workload: Array<{ teacher_id: number; periods_per_week: number }>;
    room_utilization: Array<{ room_id: number; count: number }>;
    subject_distribution: Array<{ subject_id: number; count: number }>;
    daily_classes: Array<{ period: string; count: number }>;
  };
}

export const WEEKDAYS = [
  'monday',
  'tuesday',
  'wednesday',
  'thursday',
  'friday',
  'saturday',
  'sunday',
];
export const SPECIAL_EVENT_TYPES = ['holiday', 'event', 'exam', 'festival', 'function'];
export const SUBSTITUTION_STATUSES = ['planned', 'confirmed', 'cancelled'];

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

export const timetableApi = {
  dashboard: (params: Record<string, unknown>) =>
    apiClient.get<TimetableDashboardData>(`/timetable/dashboard?${qs(params)}`),

  periods: crud<Period>('/timetable/periods'),
  templates: crud<Template>('/timetable/templates'),
  substitutions: crud<Substitution>('/timetable/substitutions'),
  specialEvents: crud<Record<string, unknown>>('/timetable/special-events'),

  workingDays: {
    list: (params: Record<string, unknown> = {}) =>
      apiPage<WorkingDay>(`/timetable/working-days?${qs(params)}`),
    sync: (school_id: number, days: Array<{ weekday: string; is_working: boolean }>) =>
      apiClient.post<WorkingDay[]>('/timetable/working-days/sync', { school_id, days }),
  },

  classTimetable: {
    ...crud<TimetableSlot>('/timetable/classes'),
    grid: (params: Record<string, unknown>) =>
      apiClient.get<TimetableSlot[]>(`/timetable/classes/grid?${qs(params)}`),
  },

  copyTemplate: (payload: Record<string, unknown>) =>
    apiClient.post<{ copied: number }>('/timetable/templates/copy', payload),

  teacherTimetable: (teacherId: number, params: Record<string, unknown>) =>
    apiClient.get<{ slots: TimetableSlot[]; workload: Workload }>(
      `/timetable/teachers/${teacherId}?${qs(params)}`
    ),
  roomTimetable: (roomId: number, params: Record<string, unknown>) =>
    apiClient.get<TimetableSlot[]>(`/timetable/rooms/${roomId}?${qs(params)}`),
};

/** Teaching staff as select options (the timetable teacher is a Staff member). */
export function useStaffTeachers(): FieldOption[] {
  const [options, setOptions] = useState<FieldOption[]>([]);
  useEffect(() => {
    let active = true;
    staffApi.staff
      .list({ per_page: 200, sort: 'name' })
      .then((r) => {
        if (active)
          setOptions(
            r.data.map((s) => ({ value: String(s.id), label: `${s.employee_number} — ${s.name}` }))
          );
      })
      .catch(() => {});
    return () => {
      active = false;
    };
  }, []);
  return options;
}

export type { PageMeta };
