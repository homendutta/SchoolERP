/* System / Operations API bindings (Sprint 23 — Production Hardening). Health,
 * diagnostics, config validation, production dashboard, backup manifests +
 * verification, failed-job monitoring and unified logs. */
import { apiClient } from '@core/api/client';

export interface HealthComponent {
  name: string;
  status: string;
  detail: string;
}
export interface Health {
  score: number;
  status: string;
  components: HealthComponent[];
}
export interface ConfigCheck {
  check: string;
  ok: boolean;
  severity: string;
  detail: string;
}

export const systemApi = {
  dashboard: () => apiClient.get<Record<string, unknown>>('/system/dashboard'),
  health: () => apiClient.get<Health>('/system/health'),
  diagnostics: () => apiClient.get<Record<string, unknown>>('/system/diagnostics'),
  config: () => apiClient.get<{ ready: boolean; checks: ConfigCheck[] }>('/system/config'),

  backups: () => apiClient.get<Record<string, unknown>[]>('/system/backups'),
  createBackup: (payload: Record<string, unknown>) =>
    apiClient.post<Record<string, unknown>>('/system/backups', payload),
  verifyBackup: (id: number) =>
    apiClient.post<Record<string, unknown>>(`/system/backups/${id}/verify`),

  failedJobs: () => apiClient.get<Record<string, unknown>>('/system/failed-jobs'),
};
