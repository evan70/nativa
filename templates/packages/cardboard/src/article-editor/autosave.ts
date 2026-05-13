/**
 * Article Draft Auto-Save — server-side persistence (replaces localStorage).
 *
 * Uses /mark/api/drafts endpoints for cross-device draft persistence.
 * All functions are async and return Promises.
 */

import { NotificationAdapter } from '../utils/NotificationAdapter.ts';

export interface AutosavePayload {
  title: string;
  excerpt: string;
  content: string;
  status: string;
  timestamp: number;
}

export interface SavedDraftResponse {
  id: string;
  saved_at: string;
}

export interface LoadedDraftResponse {
  id: string;
  title: string;
  excerpt: string;
  content: string;
  media_ids: string[] | null;
  status: string;
  saved_at: string;
}

/**
 * Build API query params from mode and optional articleId.
 */
function buildDraftQuery(mode: 'create' | 'edit', articleId?: string): URLSearchParams {
  const params = new URLSearchParams();
  if ('edit' === mode && articleId) {
    params.set('article_id', articleId);
  } else {
    params.set('mode', 'new');
  }
  return params;
}

/**
 * Get CSRF token from meta tag.
 */
function getCsrfToken(): string {
  return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';
}

/**
 * Save draft to server.
 */
export async function saveDraft(
  mode: 'create' | 'edit',
  articleId: string | undefined,
  payload: AutosavePayload,
): Promise<SavedDraftResponse> {
  const params = buildDraftQuery(mode, articleId);
  const response = await fetch(`/mark/api/drafts?${params}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-Token': getCsrfToken(),
    },
    credentials: 'same-origin',
    body: JSON.stringify({
      article_id: 'edit' === mode && articleId ? articleId : null,
      title: payload.title,
      excerpt: payload.excerpt,
      content: payload.content,
      status: payload.status,
      media_ids: [], // Populated separately by media module
    }),
  });

  if (!response.ok) {
    throw new Error('Failed to save draft');
  }

  const json = (await response.json()) as { success: boolean; data: SavedDraftResponse };
  return json.data;
}

/**
 * Load draft from server.
 */
export async function loadDraft(
  mode: 'create' | 'edit',
  articleId: string | undefined,
): Promise<AutosavePayload | null> {
  const params = buildDraftQuery(mode, articleId);
  const response = await fetch(`/mark/api/drafts?${params}`, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'same-origin',
  });

  if (!response.ok) {
    return null;
  }

  const json = (await response.json()) as { success: boolean; data: LoadedDraftResponse | null };
  if (!json.data) {
    return null;
  }

  return {
    title: json.data.title,
    excerpt: json.data.excerpt,
    content: json.data.content,
    status: json.data.status,
    timestamp: Math.floor(new Date(json.data.saved_at + 'Z').getTime()),
  };
}

/**
 * Delete draft from server.
 */
export async function clearDraft(
  mode: 'create' | 'edit',
  articleId: string | undefined,
): Promise<void> {
  const params = buildDraftQuery(mode, articleId);
  await fetch(`/mark/api/drafts?${params}`, {
    method: 'DELETE',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-Token': getCsrfToken(),
    },
    credentials: 'same-origin',
  });
}

/**
 * Check if draft content should trigger restore prompt.
 */
export function shouldOfferRestore(currentContent: string, draft: AutosavePayload): boolean {
  return '' !== draft.content && ('' === currentContent || draft.timestamp > Date.now() - 86400000);
}

/**
 * Show restore confirmation prompt.
 */
export function showRestorePrompt(
  savedTime: string,
  onConfirm: () => void,
): void {
  NotificationAdapter.showConfirm({
    message: `Auto-saved content from ${savedTime}`,
    confirmLabel: 'Restore',
    cancelLabel: 'Dismiss',
    onConfirm,
  });
}
