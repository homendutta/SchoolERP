/* Academic module API bindings — Academic Years, Terms, Calendar, Classes,
 * Sections, Rooms, Subjects, Subject Groups, Teacher & Class-Teacher
 * assignments. All requests go through the single shared API client. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

// ---------------- Types ----------------
export interface AcademicYear {
  id: number;
  uuid: string;
  school_id: number;
  name: string;
  short_name: string | null;
  slug: string;
  start_date: string | null;
  end_date: string | null;
  is_current: boolean;
  sort_order: number;
  status: string;
  version: number;
  archived: boolean;
}

export interface Term {
  id: number;
  academic_year_id: number;
  name: string;
  short_name: string | null;
  start_date: string | null;
  end_date: string | null;
  sort_order: number;
  status: string;
  archived: boolean;
}

export interface HolidayType {
  id: number;
  name: string;
  slug: string;
  color: string | null;
  status: string;
  archived: boolean;
}

export interface AcademicCalendar {
  id: number;
  school_id: number;
  academic_year_id: number;
  name: string;
  status: string;
  archived: boolean;
}

export interface CalendarEvent {
  id: number;
  academic_calendar_id: number;
  holiday_type_id: number | null;
  title: string;
  description: string | null;
  event_type: string;
  start_date: string | null;
  end_date: string | null;
  is_recurring: boolean;
  status: string;
  archived: boolean;
}

export interface SchoolClass {
  id: number;
  school_id: number;
  code: string;
  name: string;
  short_name: string | null;
  slug: string;
  display_order: number;
  status: string;
  version: number;
  archived: boolean;
}

export interface Room {
  id: number;
  school_id: number;
  room_type_id: number | null;
  code: string;
  name: string;
  capacity: number | null;
  building: string | null;
  display_order: number;
  status: string;
  archived: boolean;
}

export interface Section {
  id: number;
  class_id: number;
  room_id: number | null;
  name: string;
  capacity: number | null;
  display_order: number;
  status: string;
  archived: boolean;
}

export interface Subject {
  id: number;
  school_id: number;
  subject_type_id: number | null;
  code: string;
  name: string;
  short_name: string | null;
  slug: string;
  theory: boolean;
  practical: boolean;
  credits: number;
  display_order: number;
  status: string;
  archived: boolean;
}

export interface SubjectGroup {
  id: number;
  school_id: number;
  code: string;
  name: string;
  slug: string;
  display_order: number;
  status: string;
  subjects?: Subject[];
  subject_ids?: number[];
  archived: boolean;
}

export interface TeacherSubjectAssignment {
  id: number;
  academic_year_id: number;
  class_id: number;
  section_id: number;
  subject_id: number;
  teacher_id: number;
  is_primary: boolean;
  status: string;
  teacher?: { id: number; name: string };
  subject?: { id: number; code: string; name: string };
  class?: { id: number; name: string };
  section?: { id: number; name: string };
  archived: boolean;
}

export interface ClassTeacher {
  id: number;
  academic_year_id: number;
  class_id: number;
  section_id: number;
  teacher_id: number;
  teacher?: { id: number; name: string };
  is_active: boolean;
  assigned_on: string | null;
  ended_on: string | null;
}

/** Serialize nested params (filter[x]=y, search[x]=y) the API understands. */
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

/** Build the standard CRUD binding set for a resource path. */
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

export const academicApi = {
  years: {
    ...crud<AcademicYear>('/academic/academic-years'),
    setCurrent: (id: number) =>
      apiClient.post<AcademicYear>(`/academic/academic-years/${id}/set-current`),
  },
  terms: crud<Term>('/academic/terms'),
  calendars: crud<AcademicCalendar>('/academic/academic-calendar/calendars'),
  events: crud<CalendarEvent>('/academic/academic-calendar/events'),
  holidayTypes: crud<HolidayType>('/academic/academic-calendar/holiday-types'),
  classes: crud<SchoolClass>('/academic/classes'),
  sections: crud<Section>('/academic/sections'),
  rooms: crud<Room>('/academic/rooms'),
  subjects: crud<Subject>('/academic/subjects'),
  subjectGroups: crud<SubjectGroup>('/academic/subject-groups'),
  teacherAssignments: crud<TeacherSubjectAssignment>('/academic/teacher-subject-assignments'),
  classTeachers: {
    list: (params: Record<string, unknown> = {}) =>
      apiClient.get<ClassTeacher[]>(`/academic/class-teachers?${qs(params)}`),
    history: (params: Record<string, unknown>) =>
      apiClient.get<ClassTeacher[]>(`/academic/class-teachers/history?${qs(params)}`),
    assign: (d: Record<string, unknown>) =>
      apiClient.post<ClassTeacher>('/academic/class-teachers', d),
  },
};

export type { PageMeta };
