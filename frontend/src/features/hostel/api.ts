/* Hostel Management API bindings. Students occupy beds (never rooms directly);
 * a bed is single-occupant; history is preserved. Fees are collected by Finance;
 * notifications go through Communication. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Ref {
  id: number;
  [k: string]: unknown;
}

export interface Bed extends Ref {
  room_id: number;
  bed_number: string;
  bed_code: string | null;
  status: string;
}

export interface Allocation extends Ref {
  student_id: number;
  hostel_id: number;
  room_id: number;
  bed_id: number;
  status: string;
  student?: { name: string };
  hostel?: { name: string };
  room?: { room_number: string };
  bed?: { bed_number: string };
}

export const HOSTEL_GENDERS = ['boys', 'girls', 'co_ed'];
export const BED_STATUSES = ['available', 'occupied', 'reserved', 'under_maintenance'];
export const WARDEN_ROLES = ['chief', 'assistant'];
export const VISITOR_STATUSES = ['pending', 'approved', 'checked_in', 'checked_out', 'rejected'];
export const MAINT_CATEGORIES = [
  'electrical',
  'plumbing',
  'furniture',
  'cleaning',
  'security',
  'other',
];
export const MAINT_PRIORITIES = ['low', 'medium', 'high', 'urgent'];
export const MAINT_STATUSES = ['open', 'in_progress', 'completed', 'cancelled'];
export const FEE_TYPES = ['hostel', 'security_deposit', 'mess', 'electricity', 'special'];
export const TRANSFER_TYPES = ['room', 'bed', 'building', 'hostel'];

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

export const hostelApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/hostel/dashboard?${qs({ school_id: schoolId })}`),
  occupancy: (schoolId?: number, hostelId?: number) =>
    apiClient.get<Record<string, number>>(
      `/hostel/occupancy?${qs({ school_id: schoolId, hostel_id: hostelId })}`
    ),

  hostels: crud<Ref>('/hostel/hostels'),
  buildings: crud<Ref>('/hostel/buildings'),
  floors: crud<Ref>('/hostel/floors'),
  rooms: crud<Ref>('/hostel/rooms'),
  beds: crud<Bed>('/hostel/beds'),
  wardens: crud<Ref>('/hostel/wardens'),
  visitors: crud<Ref>('/hostel/visitors'),
  maintenance: crud<Ref>('/hostel/maintenance'),
  fees: crud<Ref>('/hostel/fees'),

  allocations: (params: Record<string, unknown> = {}) =>
    apiPage<Allocation>(`/hostel/allocations?${qs(params)}`),
  allocate: (payload: Record<string, unknown>) =>
    apiClient.post<Allocation>('/hostel/allocations', payload),
  checkout: (id: number) => apiClient.post(`/hostel/allocations/${id}/checkout`),

  transfers: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/hostel/transfers?${qs(params)}`),
  transfer: (payload: Record<string, unknown>) =>
    apiClient.post<Allocation>('/hostel/transfers', payload),
};

export type { PageMeta };
