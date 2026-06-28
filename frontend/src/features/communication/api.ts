/* Communication Management API bindings. The central hub — every module
 * publishes here; nothing sends Email/SMS/Push/In-App directly. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Template {
  id: number;
  school_id: number;
  name: string;
  code: string;
  channel: string;
  subject: string | null;
  body: string;
  variables: string[];
  language: string;
  status: string;
}

export interface Message {
  id: number;
  school_id: number;
  batch_id: number | null;
  channel: string;
  recipient_name: string | null;
  recipient_type: string | null;
  address: string | null;
  subject: string | null;
  body: string;
  status: string;
  is_mandatory: boolean;
  scheduled_at: string | null;
  attempts: number;
  max_attempts: number;
  sent_at: string | null;
  delivered_at: string | null;
  read_at: string | null;
  error: string | null;
  logs?: Array<{ event: string; detail: string | null; at: string | null }>;
}

export interface Announcement {
  id: number;
  school_id: number;
  title: string;
  body: string;
  audience_type: string;
  class_id: number | null;
  status: string;
  published_at: string | null;
}

export interface Circular {
  id: number;
  school_id: number;
  title: string;
  body: string;
  media_id: number | null;
  audience_type: string;
  publish_date: string | null;
  expiry_date: string | null;
  status: string;
}

export interface ChannelSetting {
  id: number;
  school_id: number;
  channel: string;
  is_enabled: boolean;
  provider: string | null;
  max_attempts: number;
  retry_delay_seconds: number;
  backoff: string;
}

export const CHANNELS = ['email', 'sms', 'push', 'in_app'];
export const AUDIENCE_TYPES = [
  'school',
  'class',
  'section',
  'students',
  'guardians',
  'staff',
  'teachers',
  'administrators',
  'department',
];
export const BACKOFFS = ['fixed', 'linear', 'exponential'];
export const MESSAGE_STATUSES = [
  'pending',
  'processing',
  'sent',
  'delivered',
  'failed',
  'cancelled',
  'read',
];

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

export const communicationApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(
      `/communication/dashboard?${qs({ school_id: schoolId })}`
    ),

  templates: crud<Template>('/communication/templates'),
  announcements: crud<Announcement>('/communication/announcements'),
  circulars: crud<Circular>('/communication/circulars'),

  messages: (params: Record<string, unknown> = {}) =>
    apiPage<Message>(`/communication/messages?${qs(params)}`),
  scheduled: (schoolId: number) =>
    apiPage<Message>(`/communication/messages/scheduled?${qs({ school_id: schoolId })}`),
  publish: (payload: Record<string, unknown>) => apiClient.post('/communication/messages', payload),
  processQueue: (schoolId: number) =>
    apiClient.post<{ processed: number }>('/communication/queue/process', { school_id: schoolId }),
  retry: (id: number) => apiClient.post(`/communication/messages/${id}/retry`),
  markRead: (id: number) => apiClient.post(`/communication/messages/${id}/read`),
  cancel: (id: number) => apiClient.post(`/communication/messages/${id}/cancel`),
  message: (id: number) => apiClient.get<Message>(`/communication/messages/${id}`),

  channels: (schoolId?: number) =>
    apiClient.get<{
      settings: ChannelSetting[];
      available_channels: string[];
      active_providers: string[];
    }>(`/communication/channels?${qs({ filter: { school_id: schoolId } })}`),
  saveChannel: (payload: Record<string, unknown>) =>
    apiClient.post('/communication/channels', payload),

  preferences: (userId?: number) =>
    apiClient.get<{
      user_id: number;
      preferences: Array<{ channel: string; is_enabled: boolean }>;
    }>(`/communication/preferences?${qs({ user_id: userId })}`),
  savePreferences: (
    preferences: Array<{ channel: string; is_enabled: boolean }>,
    userId?: number
  ) => apiClient.put('/communication/preferences', { user_id: userId, preferences }),
};

export type { PageMeta };
