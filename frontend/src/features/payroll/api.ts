/* Payroll API bindings (Sprint 16B). Salary components/structures, employee
 * salary assignments + revisions (historical, immutable), overtime, loans,
 * arrears, statutory components, and the idempotent Payroll Engine (runs →
 * payslips). Payroll CONSUMES HR/Attendance/Leave/Finance and never edits them.
 * Numbers use the Number Generator; payslip QR uses the Identity Platform. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface Ref {
  id: number;
  status?: string;
  archived?: boolean;
  [k: string]: unknown;
}

export const COMPONENT_TYPES = ['earning', 'deduction', 'employer_contribution', 'informational'];
export const CALCULATION_TYPES = ['fixed', 'percentage', 'formula'];
export const REVISION_TYPES = ['promotion', 'annual_increment', 'special_increment', 'correction'];
export const LOAN_TYPES = ['loan', 'advance'];
export const ARREAR_TYPES = ['salary', 'adjustment'];
export const STATUTORY_TYPES = ['pf', 'esi', 'professional_tax', 'tds', 'other'];
export const RUN_STATUSES = ['draft', 'processing', 'completed', 'locked', 'cancelled'];
export const SETTLEMENT_STATUSES = ['unpaid', 'paid', 'partially_paid', 'failed'];

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

export const payrollApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/payroll/dashboard?${qs({ school_id: schoolId })}`),

  components: crud('/payroll/components'),
  structures: crud('/payroll/structures'),
  statutory: crud('/payroll/statutory'),
  assignments: crud('/payroll/assignments'),
  revisions: crud('/payroll/revisions'),
  overtime: crud('/payroll/overtime'),
  arrears: crud('/payroll/arrears'),
  loans: crud('/payroll/loans'),
  approveLoan: (id: number) => apiClient.post(`/payroll/loans/${id}/approve`),

  // Payroll runs — the engine processes (idempotent) and locks.
  runs: (params: Record<string, unknown> = {}) => apiPage<Ref>(`/payroll/runs?${qs(params)}`),
  createRun: (payload: Record<string, unknown>) => apiClient.post('/payroll/runs', payload),
  processRun: (id: number) => apiClient.post(`/payroll/runs/${id}/process`),
  lockRun: (id: number) => apiClient.post(`/payroll/runs/${id}/lock`),

  // Payslips — structured data; settlement status.
  payslips: (params: Record<string, unknown> = {}) =>
    apiPage<Ref>(`/payroll/payslips?${qs(params)}`),
  payslip: (id: number) => apiClient.get<Ref>(`/payroll/payslips/${id}`),
  settlePayslip: (id: number, status: string) =>
    apiClient.post(`/payroll/payslips/${id}/settle`, { settlement_status: status }),
};

export type { PageMeta };
