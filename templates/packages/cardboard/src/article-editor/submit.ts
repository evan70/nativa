import { NotificationAdapter } from '../utils/NotificationAdapter.ts';

export interface SubmitSuccessPayload {
  redirectUrl: string;
}

export function validateSubmission(title: string, content: string): string | null {
  if ('' === title.trim()) {
    return 'Title is required';
  }

  if ('' === content.trim()) {
    return 'Content is required';
  }

  return null;
}

export async function submitArticle(
  submitUrl: string,
  formData: FormData,
): Promise<SubmitSuccessPayload> {
  const response = await fetch(submitUrl, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'same-origin',
  });

  if (response.redirected) {
    return { redirectUrl: response.url };
  }

  const html = await response.text();
  if (!response.ok) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const errorEl = doc.querySelector('[data-error]') || doc.querySelector('.error-message');
    const message = errorEl?.textContent || 'Failed to save article';
    throw new Error(message);
  }

  return { redirectUrl: '/mark/articles' };
}

export function queueSuccessAndRedirect(
  message: string,
  redirectUrl: string,
): void {
  NotificationAdapter.queueForNextPage(message, 'success');
  window.location.href = redirectUrl;
}
