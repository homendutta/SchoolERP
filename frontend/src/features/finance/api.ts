/* Finance & Fees API bindings. Fees (what is owed), Payments (what was paid) and
 * the Ledger (accounting impact) are kept separate. Receipt/transaction numbers
 * come from the Number Generator; payment methods from Master Data. */
import { apiClient, apiPage, type PageMeta } from '@core/api/client';

export interface FeeCategory {
  id: number;
  school_id: number;
  name: string;
  code: string | null;
  description: string | null;
  is_active: boolean;
  status: string;
}

export interface FeeMaster {
  id: number;
  school_id: number;
  fee_category_id: number;
  category?: string | null;
  academic_year_id: number | null;
  class_id: number | null;
  class?: string | null;
  name: string;
  amount: number;
  due_date: string | null;
  frequency: string;
  status: string;
}

export interface FeeStructure {
  id: number;
  school_id: number;
  academic_year_id: number | null;
  class_id: number | null;
  name: string;
  code: string | null;
  status: string;
  items_count?: number;
  items?: Array<{
    id: number;
    fee_master_id: number;
    fee_master: string | null;
    amount: number | null;
  }>;
}

export interface Discount {
  id: number;
  school_id: number;
  name: string;
  code: string | null;
  method: string;
  value: number;
  status: string;
}

export interface Scholarship {
  id: number;
  school_id: number;
  name: string;
  code: string | null;
  type: string;
  method: string;
  value: number;
  status: string;
}

export interface SiblingRule {
  id: number;
  school_id: number;
  name: string;
  child_order: number;
  method: string;
  value: number;
  status: string;
}

export interface FineRule {
  id: number;
  school_id: number;
  name: string;
  fee_category_id: number | null;
  category?: string | null;
  mode: string;
  amount: number;
  grace_period_days: number;
  max_fine: number | null;
  status: string;
}

export interface StudentFee {
  id: number;
  student_id: number;
  student?: string | null;
  admission_number?: string | null;
  class?: string | null;
  total_amount: number;
  discount_amount: number;
  scholarship_amount: number;
  net_amount: number;
  paid_amount: number;
  status: string;
  items?: Array<{
    id: number;
    name: string;
    amount: number;
    paid_amount: number;
    due_date: string | null;
    status: string;
  }>;
}

export interface Payment {
  id: number;
  student_id: number;
  student?: string | null;
  admission_number?: string | null;
  receipt_number: string | null;
  transaction_number: string | null;
  payment_method?: string | null;
  amount: number;
  refunded_amount: number;
  paid_on: string | null;
  status: string;
  allocations?: Array<{ student_fee_item_id: number; amount: number }>;
}

export interface Refund {
  id: number;
  student?: string | null;
  receipt_number?: string | null;
  transaction_number: string | null;
  amount: number;
  type: string;
  reason: string | null;
  refunded_on: string | null;
  status: string;
}

export interface Adjustment {
  id: number;
  student?: string | null;
  transaction_number: string | null;
  type: string;
  amount: number;
  reason: string | null;
  status: string;
}

export interface Installment {
  id: number;
  school_id: number;
  student_fee_id: number;
  name: string;
  due_date: string | null;
  amount: number;
  paid_amount: number;
  status: string;
  sort_order: number;
}

export interface LedgerEntry {
  id: number;
  student_id: number | null;
  source_type: string;
  source_id: number;
  entry_type: string;
  amount: number;
  narration: string | null;
  entry_date: string | null;
}

export const FEE_FREQUENCIES = ['one_time', 'monthly', 'quarterly', 'half_yearly', 'yearly'];
export const DISCOUNT_METHODS = ['percentage', 'fixed'];
export const SCHOLARSHIP_TYPES = ['full', 'partial'];
export const FINE_MODES = ['daily', 'weekly', 'monthly', 'flat'];
export const ADJUSTMENT_TYPES = ['credit_note', 'debit_note', 'waiver', 'manual'];

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

export const financeApi = {
  dashboard: (schoolId?: number) =>
    apiClient.get<Record<string, unknown>>(`/finance/dashboard?${qs({ school_id: schoolId })}`),

  categories: crud<FeeCategory>('/finance/categories'),
  masters: crud<FeeMaster>('/finance/masters'),
  structures: crud<FeeStructure>('/finance/structures'),
  discounts: crud<Discount>('/finance/discounts'),
  scholarships: crud<Scholarship>('/finance/scholarships'),
  siblingRules: crud<SiblingRule>('/finance/sibling-discounts'),
  fines: crud<FineRule>('/finance/fines'),
  installments: crud<Installment>('/finance/installments'),

  studentFees: (params: Record<string, unknown> = {}) =>
    apiPage<StudentFee>(`/finance/student-fees?${qs(params)}`),
  studentFee: (id: number) => apiClient.get<StudentFee>(`/finance/student-fees/${id}`),
  assignFee: (payload: Record<string, unknown>) =>
    apiClient.post('/finance/student-fees/assign', payload),
  applyDiscount: (id: number, discountId: number) =>
    apiClient.post(`/finance/student-fees/${id}/discount`, { discount_id: discountId }),
  applyScholarship: (id: number, scholarshipId: number) =>
    apiClient.post(`/finance/student-fees/${id}/scholarship`, { scholarship_id: scholarshipId }),
  applySibling: (id: number) => apiClient.post(`/finance/student-fees/${id}/sibling-discount`),

  payments: (params: Record<string, unknown> = {}) =>
    apiPage<Payment>(`/finance/payments?${qs(params)}`),
  recordPayment: (payload: Record<string, unknown>) =>
    apiClient.post<Payment>('/finance/payments', payload),
  receipt: (id: number) =>
    apiClient.get<Record<string, unknown>>(`/finance/payments/${id}/receipt`),

  refunds: (params: Record<string, unknown> = {}) =>
    apiPage<Refund>(`/finance/refunds?${qs(params)}`),
  refund: (payload: Record<string, unknown>) => apiClient.post('/finance/refunds', payload),

  adjustments: (params: Record<string, unknown> = {}) =>
    apiPage<Adjustment>(`/finance/adjustments?${qs(params)}`),
  adjust: (payload: Record<string, unknown>) => apiClient.post('/finance/adjustments', payload),

  ledger: (params: Record<string, unknown> = {}) =>
    apiPage<LedgerEntry>(`/finance/ledger?${qs(params)}`),
  dueTracking: (studentId: number) =>
    apiClient.get<Record<string, unknown>>(
      `/finance/due-tracking?${qs({ student_id: studentId })}`
    ),
  defaulters: (params: Record<string, unknown>) =>
    apiClient.get<Record<string, unknown>>(`/finance/defaulters?${qs(params)}`),
};

export type { PageMeta };
