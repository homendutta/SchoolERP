/* Inventory & Asset Management API bindings. Physical assets each have their own
 * permanent Identity (barcode/QR); consumables never do. Assignments/transfers/
 * verifications/disposals are historical. Fees/accounting belong to Finance. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Ref {
  id: number;
  [k: string]: unknown;
}

export interface Asset extends Ref {
  asset_number: string;
  serial_number: string | null;
  status: string;
  condition: string;
  assetModel?: { name?: string };
  category?: { name?: string };
  vendor?: { name?: string };
  assetIdentity?: { identity_number?: string; barcode_value?: string };
}

export interface Assignment extends Ref {
  asset_id: number;
  target_type: string;
  target_label: string | null;
  target_reference: string | null;
  identity_id: number | null;
  status: string;
  asset?: { asset_number?: string };
}

export interface LifecycleEvent extends Ref {
  asset_id: number;
  from_status: string | null;
  to_status: string;
  note: string | null;
  created_at: string | null;
}

/* The formal asset lifecycle states (Ordered reserved for future Procurement). */
export const ASSET_STATUSES = [
  'draft',
  'ordered',
  'received',
  'available',
  'assigned',
  'reserved',
  'in_maintenance',
  'lost',
  'stolen',
  'disposed',
];
export const ASSET_CONDITIONS = ['new', 'good', 'fair', 'poor', 'damaged'];
export const DEPRECIATION_METHODS = ['none', 'straight_line', 'written_down_value'];
export const MOVEMENT_TYPES = ['in', 'out', 'adjustment', 'transfer'];
export const ASSIGN_TARGETS = ['staff', 'department', 'room', 'hostel', 'library', 'laboratory'];
export const MAINT_TYPES = ['preventive', 'corrective', 'emergency'];
export const MAINT_PRIORITIES = ['low', 'medium', 'high', 'urgent'];
export const MAINT_STATUSES = ['scheduled', 'in_progress', 'completed', 'cancelled'];
export const WARRANTY_STATUSES = ['active', 'expired', 'void'];
export const VERIFICATION_STATUSES = ['verified', 'missing', 'damaged', 'disposed'];
export const DISPOSAL_METHODS = ['sold', 'scrapped', 'donated', 'written_off'];

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

export const inventoryApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/inventory/dashboard?${qs({ school_id: schoolId })}`),

  categories: crud<Ref>('/inventory/categories'),
  models: crud<Ref>('/inventory/models'),
  vendors: crud<Ref>('/inventory/vendors'),
  assets: crud<Asset>('/inventory/assets'),
  consumables: crud<Ref>('/inventory/consumables'),
  maintenance: crud<Ref>('/inventory/maintenance'),
  warranties: crud<Ref>('/inventory/warranties'),

  movements: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/inventory/movements?${qs(params)}`),
  recordMovement: (payload: Record<string, unknown>) =>
    apiClient.post('/inventory/movements', payload),

  assignments: (params: Record<string, unknown> = {}) =>
    apiPage<Assignment>(`/inventory/assignments?${qs(params)}`),
  assign: (payload: Record<string, unknown>) =>
    apiClient.post<Assignment>('/inventory/assignments', payload),
  returnAsset: (id: number) => apiClient.post(`/inventory/assignments/${id}/return`),

  // Asset lifecycle (state machine) — history + transition through the API.
  lifecycle: (assetId: number) =>
    apiClient.get<LifecycleEvent[]>(`/inventory/assets/${assetId}/lifecycle`),
  transition: (assetId: number, payload: { to_status: string; note?: string | null }) =>
    apiClient.post<Asset>(`/inventory/assets/${assetId}/lifecycle`, payload),

  transfers: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/inventory/transfers?${qs(params)}`),
  transfer: (payload: Record<string, unknown>) =>
    apiClient.post<Assignment>('/inventory/transfers', payload),

  verifications: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/inventory/verifications?${qs(params)}`),
  verify: (payload: Record<string, unknown>) => apiClient.post('/inventory/verifications', payload),
  verificationReport: (schoolId?: number) =>
    apiClient.get<Record<string, number>>(
      `/inventory/verifications/report?${qs({ school_id: schoolId })}`
    ),

  disposals: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/inventory/disposals?${qs(params)}`),
  dispose: (payload: Record<string, unknown>) => apiClient.post('/inventory/disposals', payload),
};

export type { PageMeta };
