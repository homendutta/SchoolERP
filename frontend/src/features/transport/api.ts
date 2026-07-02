/* Transport Management API bindings. Students are assigned to route+stop (never
 * a vehicle); the vehicle is determined via the trip. Fees are collected by
 * Finance; notifications go through Communication. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Vehicle {
  id: number;
  school_id: number;
  vehicle_number: string;
  registration_number: string | null;
  vehicle_type: string;
  manufacturer: string | null;
  model: string | null;
  year: number | null;
  seating_capacity: number;
  reserved_seats: number;
  fuel_type: string;
  odometer: number | null;
  status: string;
}

export interface Route {
  id: number;
  school_id: number;
  route_code: string;
  name: string;
  start_location: string | null;
  end_location: string | null;
  distance_km: number | null;
  estimated_minutes: number | null;
  stops_count?: number;
  stops?: Stop[];
  status: string;
}

export interface Stop {
  id: number;
  school_id: number;
  route_id: number;
  name: string;
  code: string | null;
  sequence: number;
  pickup_time: string | null;
  drop_time: string | null;
  capacity: number | null;
  status: string;
}

export interface Trip {
  id: number;
  vehicle_id: number;
  route_id: number;
  driver_id: number | null;
  shift: string;
  status: string;
  vehicle?: { vehicle_number: string };
  route?: { name: string };
  driver?: { name: string };
}

export interface Assignment {
  id: number;
  student_id: number;
  route_id: number;
  stop_id: number;
  status: string;
  student?: { name: string };
  route?: { name: string };
  stop?: { name: string };
}

export interface Ref {
  id: number;
  [k: string]: unknown;
}

export const VEHICLE_TYPES = ['bus', 'mini_bus', 'van', 'car', 'auto'];
export const FUEL_TYPES = ['diesel', 'petrol', 'cng', 'electric', 'hybrid'];
export const VEHICLE_STATUSES = ['active', 'inactive', 'under_maintenance', 'retired'];
export const TRIP_SHIFTS = ['morning', 'evening'];
export const TRIP_STATUSES = ['scheduled', 'running', 'completed', 'cancelled'];
export const STAFF_ROLES = ['primary_driver', 'backup_driver', 'attendant', 'helper'];
export const DOCUMENT_TYPES = ['insurance', 'registration', 'pollution', 'fitness', 'permit'];
export const FEE_TYPES = ['route', 'stop', 'special'];

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

export const transportApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/transport/dashboard?${qs({ school_id: schoolId })}`),

  vehicles: crud<Vehicle>('/transport/vehicles'),
  routes: crud<Route>('/transport/routes'),
  stops: crud<Stop>('/transport/stops'),
  trips: crud<Trip>('/transport/trips'),
  drivers: crud<Ref>('/transport/drivers'),
  documents: crud<Ref>('/transport/documents'),
  fees: crud<Ref>('/transport/fees'),
  maintenance: crud<Ref>('/transport/maintenance'),

  students: (params: Record<string, unknown> = {}) =>
    apiPage<Assignment>(`/transport/students?${qs(params)}`),
  assign: (payload: Record<string, unknown>) =>
    apiClient.post<Assignment>('/transport/students', payload),
  cancelAssignment: (id: number) => apiClient.post(`/transport/students/${id}/cancel`),

  route: (id: number) => apiClient.get<Route>(`/transport/routes/${id}`),
};

export type { PageMeta };
