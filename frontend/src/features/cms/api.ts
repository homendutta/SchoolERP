/* CMS admin API bindings (Sprint 17). The public website consumes the read-only
 * /cms/public/* endpoints; these are the RBAC-protected admin endpoints. Images
 * are Media references (by id); publishing flows through the ERP. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Ref {
  id: number;
  status?: string;
  archived?: boolean;
  [k: string]: unknown;
}

export const CONTENT_STATUSES = ['draft', 'published', 'scheduled', 'archived'];
export const NOTICE_PRIORITIES = ['low', 'normal', 'high', 'urgent'];
export const MENU_LOCATIONS = ['header', 'footer', 'quick_links'];
export const VIDEO_PROVIDERS = ['youtube', 'vimeo', 'self_hosted'];
export const FORM_TYPES = ['contact', 'admission_enquiry', 'general_enquiry'];
export const CATEGORY_TYPES = ['notice', 'news', 'gallery', 'video', 'download'];
export const ENQUIRY_STATUSES = ['new', 'contacted', 'responded', 'closed'];

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

export const cmsApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/cms/dashboard?${qs({ school_id: schoolId })}`),

  getSettings: (schoolId?: number) =>
    apiClient.get<Ref>(`/cms/settings?${qs({ school_id: schoolId })}`),
  saveSettings: (payload: Record<string, unknown>) => apiClient.put<Ref>('/cms/settings', payload),

  categories: crud('/cms/categories'),
  pages: crud('/cms/pages'),
  notices: crud('/cms/notices'),
  news: crud('/cms/news'),
  events: crud('/cms/events'),
  gallery: crud('/cms/gallery'),
  videos: crud('/cms/videos'),
  downloads: crud('/cms/downloads'),
  menus: crud('/cms/menus'),
  forms: crud('/cms/forms'),

  enquiries: (params: Record<string, unknown> = {}) => apiPage<Ref>(`/cms/enquiries?${qs(params)}`),
  updateEnquiry: (id: number, d: Record<string, unknown>) =>
    apiClient.put(`/cms/enquiries/${id}`, d),
  submissions: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/cms/submissions?${qs(params)}`),
  updateSubmission: (id: number, d: Record<string, unknown>) =>
    apiClient.put(`/cms/submissions/${id}`, d),
};

export type { PageMeta };
