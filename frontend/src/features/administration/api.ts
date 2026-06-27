/* Administration module API bindings. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface MasterDataValue {
  id: number;
  type_id: number;
  type?: { id: number; name: string; slug: string };
  label: string;
  value: string;
  sort_order: number;
  is_active: boolean;
  archived: boolean;
}

export interface MasterDataType {
  id: number;
  name: string;
  slug: string;
  values_count?: number;
}

export interface SchoolSettings {
  id: number;
  general: Record<string, unknown>;
  branding: { theme_color: string; media_ids: Record<string, number | null> } | null;
  contact: Record<string, string> | null;
  regional: Record<string, string> | null;
  academic: Record<string, unknown> | null;
}

const qs = (params: Record<string, unknown>) =>
  new URLSearchParams(
    Object.entries(params).flatMap(([k, v]) =>
      v && typeof v === 'object'
        ? Object.entries(v).map(([k2, v2]) => [`${k}[${k2}]`, String(v2)])
        : v !== undefined && v !== '' && v !== null
          ? [[k, String(v)]]
          : []
    ) as [string, string][]
  ).toString();

export interface FeatureFlag {
  key: string;
  label: string | null;
  is_enabled: boolean;
}

export interface NumberSequence {
  id: number;
  key: string;
  label: string | null;
  initial_number: number;
  current_number: number;
  maximum_number: number | null;
  prefix: string;
  suffix: string;
  padding: number;
  increment: number;
  manual_entry_allowed: boolean;
  format: string;
  reset_policy: string | null;
}

export interface PaymentGatewaySummary {
  provider: string;
  mode: string;
  is_enabled: boolean;
  is_default: boolean;
  configured: boolean;
}

export const adminApi = {
  // School settings
  getSchool: () => apiClient.get<SchoolSettings>('/admin/school'),
  updateSchool: (data: Record<string, unknown>) => apiClient.put<SchoolSettings>('/admin/school', data),

  // Master data
  listTypes: () => apiPage<MasterDataType>('/admin/master-data/types?per_page=100'),
  listValues: (params: Record<string, unknown>) =>
    apiPage<MasterDataValue>(`/admin/master-data/values?${qs(params)}`),
  createValue: (d: Partial<MasterDataValue>) => apiClient.post('/admin/master-data/values', d),
  updateValue: (id: number, d: Partial<MasterDataValue>) => apiClient.put(`/admin/master-data/values/${id}`, d),
  archiveValue: (id: number) => apiClient.post(`/admin/master-data/values/${id}/archive`),
  restoreValue: (id: number) => apiClient.post(`/admin/master-data/values/${id}/restore`),
  bulkDeleteValues: (ids: number[]) => apiClient.post('/admin/master-data/values/bulk-delete', { ids }),

  // Feature flags
  listFeatureFlags: () => apiClient.get<FeatureFlag[]>('/admin/feature-flags'),
  toggleFeatureFlag: (key: string, is_enabled: boolean) =>
    apiClient.put<FeatureFlag>(`/admin/feature-flags/${key}`, { is_enabled }),

  // Number generator
  listSequences: () => apiPage<NumberSequence>('/admin/number-sequences?per_page=100'),
  previewNumber: (key: string) => apiClient.get<{ key: string; next: string }>(`/admin/number-sequences/${key}/preview`),
  resetNumber: (key: string) => apiClient.post(`/admin/number-sequences/${key}/reset`),
  updateSequence: (id: number, d: Partial<NumberSequence>) => apiClient.put(`/admin/number-sequences/${id}`, d),

  // Gateways
  getEmailGateway: () => apiClient.get<Record<string, unknown>>('/admin/gateways/email'),
  updateEmailGateway: (d: Record<string, unknown>) => apiClient.put('/admin/gateways/email', d),
  testEmailGateway: () => apiClient.post<{ ok: boolean; message: string }>('/admin/gateways/email/test'),
  getSmsGateway: () => apiClient.get<Record<string, unknown>>('/admin/gateways/sms'),
  updateSmsGateway: (d: Record<string, unknown>) => apiClient.put('/admin/gateways/sms', d),
  listPaymentGateways: () => apiClient.get<PaymentGatewaySummary[]>('/admin/gateways/payments'),
  updatePaymentGateway: (provider: string, d: Record<string, unknown>) =>
    apiClient.put(`/admin/gateways/payments/${provider}`, d),
};

export type { PageMeta };
