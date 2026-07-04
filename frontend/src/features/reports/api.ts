/* Reports & Printing Center API bindings (Sprint 21). One reporting engine, one
 * export engine (CSV/Excel), one print/PDF engine — consumed across the ERP. */
import { apiClient, apiPage, tokenStore, type PageMeta } from '@core/api/client';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? '/api/v1';

export interface Ref {
  id: number;
  status?: string;
  [k: string]: unknown;
}

export interface CatalogItem {
  key: string;
  module: string;
  category: string;
  name: string;
  columns: Record<string, string>;
  totals: string[];
}

export interface RunResult {
  columns: Record<string, string>;
  rows: Array<Record<string, unknown>>;
  total: number;
  totals: Record<string, number>;
  groups: Array<Record<string, unknown>> | null;
  page: number;
  per_page: number;
}

export const FREQUENCIES = ['daily', 'weekly', 'monthly'];
export const EXPORT_FORMATS = ['csv', 'xlsx'];

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

/** Download a binary export (CSV/Excel) with the auth token, then save it. */
async function download(payload: Record<string, unknown>): Promise<void> {
  const token = tokenStore.get();
  const res = await fetch(`${API_BASE_URL}/reports/export`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify(payload),
  });
  if (!res.ok) throw new Error('Export failed.');
  const blob = await res.blob();
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `${String(payload.report_key)}.${payload.format === 'xlsx' ? 'xls' : 'csv'}`;
  a.click();
  URL.revokeObjectURL(url);
}

/** Open a print-ready HTML document in a new window and trigger the print dialog. */
async function print(payload: Record<string, unknown>): Promise<void> {
  const token = tokenStore.get();
  const res = await fetch(`${API_BASE_URL}/reports/print`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify(payload),
  });
  const html = await res.text();
  const w = window.open('', '_blank');
  if (w) {
    w.document.write(html);
    w.document.close();
    w.focus();
    setTimeout(() => w.print(), 300);
  }
}

export const reportsApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/reports/dashboard?${qs({ school_id: schoolId })}`),
  catalog: () => apiClient.get<CatalogItem[]>('/reports/catalog'),
  run: (payload: Record<string, unknown>) => apiClient.post<RunResult>('/reports/run', payload),
  download,
  print,

  saved: crud('/reports/saved'),
  schedules: crud('/reports/schedules'),
  runSchedule: (id: number) => apiClient.post(`/reports/schedules/${id}/run`),
  exports: (params: Record<string, unknown> = {}) => apiPage<Ref>(`/reports/exports?${qs(params)}`),
};

export type { PageMeta };
