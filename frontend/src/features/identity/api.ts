/* Platform Identity API bindings — read-only directory + QR/barcode rendering.
 * Identity records are owned by the platform; modules never create them here. */
import { apiClient, apiPage, tokenStore, type PageMeta } from '@core/api/client';

export interface Identity {
  id: number;
  uuid: string;
  school_id: number | null;
  identity_number: string;
  identity_type: string;
  public_identifier: string;
  qr_payload: Record<string, unknown> | null;
  barcode_value: string | null;
  status: string;
  owner?: { id: number; name: string | null; type: string };
  owner_type: string;
  created_at: string | null;
}

const qs = (params: Record<string, unknown>) =>
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

/** Fetch an SVG (QR/barcode) with the bearer token — <img> can't send headers. */
async function fetchSvg(path: string): Promise<string> {
  const token = tokenStore.get();
  const res = await fetch(`/api/v1${path}`, {
    headers: { Accept: 'image/svg+xml', ...(token ? { Authorization: `Bearer ${token}` } : {}) },
  });
  return res.ok ? res.text() : '';
}

export const identityApi = {
  search: (params: Record<string, unknown> = {}) =>
    apiPage<Identity>(`/identity/search?${qs(params)}`),
  get: (id: number) => apiClient.get<Identity>(`/identity/${id}`),
  regenerate: (id: number) => apiClient.post<Identity>('/identity/regenerate', { identity_id: id }),
  setStatus: (id: number, status: string) =>
    apiClient.post<Identity>(`/identity/${id}/status`, { status }),
  qrSvg: (id: number) => fetchSvg(`/identity/${id}/qr`),
  barcodeSvg: (id: number) => fetchSvg(`/identity/${id}/barcode`),
};

export type { PageMeta };
