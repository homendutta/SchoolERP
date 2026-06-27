/*
 * Single API client — the only integration point to the Laravel API, shared by
 * every feature and identical to what the Flutter app consumes. Reads the
 * Sanctum bearer token from local storage and applies the standard envelope.
 */

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? '/api/v1';
const TOKEN_KEY = 'asylinx.token';

export interface ApiEnvelope<T> {
  success: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, unknown>;
  meta?: Record<string, unknown>;
  code?: string;
}

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status: number,
    public readonly code?: string,
    public readonly errors?: Record<string, unknown>
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

export const tokenStore = {
  get: () => localStorage.getItem(TOKEN_KEY),
  set: (token: string) => localStorage.setItem(TOKEN_KEY, token),
  clear: () => localStorage.removeItem(TOKEN_KEY),
};

async function request<T>(method: string, path: string, body?: unknown): Promise<T> {
  const token = tokenStore.get();
  const res = await fetch(`${API_BASE_URL}${path}`, {
    method,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });

  let envelope: ApiEnvelope<T> | null = null;
  try {
    envelope = (await res.json()) as ApiEnvelope<T>;
  } catch {
    envelope = null;
  }

  if (!res.ok || envelope?.success === false) {
    throw new ApiError(
      envelope?.message ?? `Request failed (${res.status})`,
      res.status,
      envelope?.code,
      envelope?.errors
    );
  }

  return (envelope?.data ?? (envelope as unknown)) as T;
}

export const apiClient = {
  get: <T>(path: string) => request<T>('GET', path),
  post: <T>(path: string, body?: unknown) => request<T>('POST', path, body),
  put: <T>(path: string, body?: unknown) => request<T>('PUT', path, body),
  patch: <T>(path: string, body?: unknown) => request<T>('PATCH', path, body),
  delete: <T>(path: string) => request<T>('DELETE', path),
};

export interface PageMeta {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
}

/** GET a paginated list, returning both the items and the pagination meta. */
export async function apiPage<T>(path: string): Promise<{ data: T[]; meta: PageMeta }> {
  const token = tokenStore.get();
  const res = await fetch(`${API_BASE_URL}${path}`, {
    headers: { Accept: 'application/json', ...(token ? { Authorization: `Bearer ${token}` } : {}) },
  });
  const env = (await res.json()) as ApiEnvelope<T[]>;
  if (!res.ok || env.success === false) {
    throw new ApiError(env.message ?? `Request failed (${res.status})`, res.status, env.code);
  }
  const meta = (env.meta ?? {}) as Partial<PageMeta>;
  return {
    data: env.data ?? [],
    meta: {
      current_page: meta.current_page ?? 1,
      last_page: meta.last_page ?? 1,
      total: meta.total ?? (env.data?.length ?? 0),
      per_page: meta.per_page ?? 15,
    },
  };
}
