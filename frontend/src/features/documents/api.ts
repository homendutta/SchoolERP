/* Certificate & Document Management API bindings (Sprint 20). Templates are
 * versioned; generated documents are immutable (regeneration = new version); QR
 * renders dynamically; public verification needs no login. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Ref {
  id: number;
  status?: string;
  archived?: boolean;
  [k: string]: unknown;
}

export const ORIENTATIONS = ['portrait', 'landscape'];
export const PAPER_SIZES = ['a4', 'a5', 'letter', 'legal'];
export const SUBJECT_KINDS = ['student', 'staff', 'guardian'];
export const BULK_SCOPES = ['class', 'section', 'academic_year', 'department'];
export const VERIFY_METHODS = ['document_number', 'code', 'qr'];

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

export const documentsApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/documents/dashboard?${qs({ school_id: schoolId })}`),

  categories: crud('/documents/categories'),
  certificateTypes: crud('/documents/certificate-types'),
  templates: crud('/documents/templates'),
  templateVersion: (id: number, payload: Record<string, unknown>) =>
    apiClient.post(`/documents/templates/${id}/version`, payload),

  preview: (payload: Record<string, unknown>) =>
    apiClient.post<Record<string, unknown>>('/documents/preview', payload),
  generate: (payload: Record<string, unknown>) =>
    apiClient.post<Ref>('/documents/generate', payload),
  regenerate: (id: number) => apiClient.post<Ref>(`/documents/history/${id}/regenerate`),
  bulk: (payload: Record<string, unknown>) =>
    apiClient.post<Record<string, unknown>>('/documents/bulk', payload),

  history: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/documents/history?${qs(params)}`),
  document: (id: number) => apiClient.get<Ref>(`/documents/history/${id}`),

  verify: (payload: Record<string, unknown>) =>
    apiClient.post<Record<string, unknown>>('/documents/verify', payload),
};

export type { PageMeta };
