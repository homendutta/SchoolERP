/* Integrations Platform API bindings (Sprint 22). The single gateway to third-
 * party providers: categories, providers (encrypted config), health/test,
 * webhooks, immutable event bus + request logs. Modules never call vendors
 * directly — they resolve a provider by category through the platform. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Ref {
  id: number;
  status?: string;
  archived?: boolean;
  [k: string]: unknown;
}

export const PROVIDER_STATUSES = ['enabled', 'disabled'];
export const HEALTH_STATUSES = ['unknown', 'healthy', 'degraded', 'down'];
export const WEBHOOK_DIRECTIONS = ['incoming', 'outgoing'];
export const LOG_STATUSES = ['success', 'failure'];

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

export const integrationsApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(
      `/integrations/dashboard?${qs({ school_id: schoolId })}`
    ),
  adapters: () => apiClient.get<Array<Record<string, unknown>>>('/integrations/adapters'),

  categories: crud('/integrations/categories'),
  providers: crud('/integrations/providers'),
  webhooks: crud('/integrations/webhooks'),

  health: (id: number) =>
    apiClient.get<Record<string, unknown>>(`/integrations/providers/${id}/health`),
  test: (id: number) =>
    apiClient.post<Record<string, unknown>>(`/integrations/providers/${id}/test`),

  events: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/integrations/events?${qs(params)}`),
  logs: (params: Record<string, unknown> = {}) => apiPage<Ref>(`/integrations/logs?${qs(params)}`),
};

export type { PageMeta };
