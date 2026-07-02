/* Library Management API bindings. Catalog ≠ physical copy; borrowing is always
 * against a copy, borrowers resolved via the Identity Platform. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Book {
  id: number;
  school_id: number;
  title: string;
  subtitle: string | null;
  isbn: string | null;
  edition: string | null;
  language: string | null;
  publication_year: number | null;
  description: string | null;
  publisher_id: number | null;
  publisher?: string | null;
  category_id: number | null;
  category?: string | null;
  cover_media_id: number | null;
  authors?: Array<{ id: number; name: string }>;
  copies_count?: number;
  status: string;
}

export interface Copy {
  id: number;
  school_id: number;
  book_id: number;
  book?: string | null;
  copy_number: string;
  identity_id: number | null;
  identity_number?: string | null;
  barcode?: string | null;
  qr_payload?: string | null;
  location_id: number | null;
  location?: string | null;
  shelf: string | null;
  rack: string | null;
  acquisition_date: string | null;
  purchase_price: number | null;
  condition: string;
  status: string;
}

export interface Borrowing {
  id: number;
  identity_number?: string | null;
  borrower?: string | null;
  owner_type: string;
  copy_number?: string | null;
  book?: string | null;
  borrow_date: string | null;
  due_date: string | null;
  return_date: string | null;
  status: string;
  renewals_count: number;
  late_days: number;
  fine_amount: number;
  damage_notes: string | null;
}

export interface Reservation {
  id: number;
  identity_number?: string | null;
  borrower?: string | null;
  book?: string | null;
  book_id: number;
  status: string;
  queue_position: number;
  reserved_at: string | null;
  available_at: string | null;
}

export interface Ref {
  id: number;
  name: string;
  code?: string | null;
  status?: string;
  [k: string]: unknown;
}

export const COPY_STATUSES = [
  'available',
  'borrowed',
  'reserved',
  'lost',
  'damaged',
  'under_repair',
  'withdrawn',
];
export const COPY_CONDITIONS = ['new', 'good', 'fair', 'poor'];
export const BORROW_STATUSES = ['borrowed', 'returned', 'overdue', 'lost'];
export const INVENTORY_STATUSES = ['verified', 'missing', 'misplaced', 'damaged'];
export const FINE_MODES = ['daily', 'flat'];

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

export const libraryApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/library/dashboard?${qs({ school_id: schoolId })}`),

  catalog: crud<Book>('/library/catalog'),
  authors: crud<Ref>('/library/authors'),
  publishers: crud<Ref>('/library/publishers'),
  categories: crud<Ref>('/library/categories'),
  locations: crud<Ref>('/library/locations'),
  copies: crud<Copy>('/library/copies'),
  fineRules: crud<Ref>('/library/fine-rules'),

  borrowings: (params: Record<string, unknown> = {}) =>
    apiPage<Borrowing>(`/library/borrowings?${qs(params)}`),
  borrow: (payload: Record<string, unknown>) =>
    apiClient.post<Borrowing>('/library/borrow', payload),
  returnCopy: (payload: Record<string, unknown>) =>
    apiClient.post<Borrowing>('/library/return', payload),
  renew: (payload: Record<string, unknown>) => apiClient.post<Borrowing>('/library/renew', payload),

  reservations: (params: Record<string, unknown> = {}) =>
    apiPage<Reservation>(`/library/reservations?${qs(params)}`),
  reserve: (payload: Record<string, unknown>) =>
    apiClient.post<Reservation>('/library/reservations', payload),
  cancelReservation: (id: number) => apiClient.post(`/library/reservations/${id}/cancel`),

  inventory: (params: Record<string, unknown> = {}) =>
    apiPage<Record<string, unknown>>(`/library/inventory?${qs(params)}`),
  recordInventory: (payload: Record<string, unknown>) =>
    apiClient.post('/library/inventory', payload),
  inventoryReport: (schoolId: number) =>
    apiClient.get<Record<string, number>>(
      `/library/inventory/report?${qs({ school_id: schoolId })}`
    ),
};

export type { PageMeta };
