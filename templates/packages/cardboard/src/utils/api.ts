/**
 * API utility functions - HTTP request helpers
 */

import type { ApiResponse, Result } from './types.ts';

/**
 * HTTP method types
 */
export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

/**
 * Request options
 */
export interface RequestOptions {
  method?: HttpMethod;
  headers?: Record<string, string>;
  body?: BodyInit;
  credentials?: RequestCredentials;
}

/**
 * Build URL with query parameters
 * 
 * @param url - Base URL
 * @param params - Query parameters object
 * @returns URL with query string
 */
export function buildUrl(url: string, params: Record<string, string | number | boolean>): string {
  const urlObj = new URL(url, window.location.origin);
  Object.entries(params).forEach(([key, value]) => {
    urlObj.searchParams.set(key, String(value));
  });
  return urlObj.toString();
}

/**
 * Make HTTP request
 * 
 * @param url - Request URL
 * @param options - Request options
 * @returns Promise with response data
 */
export async function request<T>(
  url: string,
  options: RequestOptions = {},
): Promise<Result<T, Error>> {
  const { method = 'GET', headers = {}, body, credentials = 'same-origin' } = options;

  try {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        ...headers,
      },
      body,
      credentials,
    });

    if (!response.ok) {
      const errorText = await response.text().catch(() => 'Unknown error');
      return {
        ok: false,
        error: new Error(`HTTP ${response.status}: ${errorText}`),
      };
    }

    // Try to parse as JSON
    const contentType = response.headers.get('content-type');
    if (contentType?.includes('application/json')) {
      const data = await response.json() as T;
      return { ok: true, value: data };
    }

    // Return raw text if not JSON
    const text = await response.text();
    return { ok: true, value: text as unknown as T };
  } catch (error) {
    return {
      ok: false,
      error: error instanceof Error ? error : new Error(String(error)),
    };
  }
}

/**
 * GET request helper
 */
export async function get<T>(url: string, params?: Record<string, string | number | boolean>): Promise<Result<T, Error>> {
  const urlWithParams = params ? buildUrl(url, params) : url;
  return request<T>(urlWithParams, { method: 'GET' });
}

/**
 * POST request helper
 */
export async function post<T>(url: string, data: unknown): Promise<Result<T, Error>> {
  return request<T>(url, {
    method: 'POST',
    body: JSON.stringify(data),
  });
}

/**
 * PUT request helper
 */
export async function put<T>(url: string, data: unknown): Promise<Result<T, Error>> {
  return request<T>(url, {
    method: 'PUT',
    body: JSON.stringify(data),
  });
}

/**
 * PATCH request helper
 */
export async function patch<T>(url: string, data: unknown): Promise<Result<T, Error>> {
  return request<T>(url, {
    method: 'PATCH',
    body: JSON.stringify(data),
  });
}

/**
 * DELETE request helper
 */
export async function del<T>(url: string): Promise<Result<T, Error>> {
  return request<T>(url, { method: 'DELETE' });
}

/**
 * Submit form data via POST
 */
export async function submitForm<T>(url: string, formData: FormData): Promise<Result<T, Error>> {
  const response = await fetch(url, {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  });

  if (!response.ok) {
    const errorText = await response.text().catch(() => 'Unknown error');
    return {
      ok: false,
      error: new Error(`HTTP ${response.status}: ${errorText}`),
    };
  }

  const contentType = response.headers.get('content-type');
  if (contentType?.includes('application/json')) {
    const data = await response.json() as T;
    return { ok: true, value: data };
  }

  const text = await response.text();
  return { ok: true, value: text as unknown as T };
}

/**
 * Upload file(s) to server
 */
export async function uploadFile<T>(
  url: string,
  files: File | File[],
  fieldName = 'file',
  extraData?: Record<string, string>,
): Promise<Result<T, Error>> {
  const formData = new FormData();
  const fileArray = Array.isArray(files) ? files : [files];

  fileArray.forEach((file, index) => {
    formData.append(`${fieldName}[${index}]`, file);
  });

  if (extraData) {
    Object.entries(extraData).forEach(([key, value]) => {
      formData.append(key, value);
    });
  }

  const response = await fetch(url, {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  });

  if (!response.ok) {
    const errorText = await response.text().catch(() => 'Unknown error');
    return {
      ok: false,
      error: new Error(`HTTP ${response.status}: ${errorText}`),
    };
  }

  const data = await response.json() as T;
  return { ok: true, value: data };
}