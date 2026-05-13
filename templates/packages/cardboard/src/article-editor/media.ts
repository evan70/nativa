import type { UploadedMedia } from './types';
import { escapeHtml } from './render';

export function addMediaToGallery(
  container: HTMLElement,
  uploaded: UploadedMedia,
): void {
  const grid = container.querySelector('#article-media-grid');
  if (!grid) {
    return;
  }

  const isImage = uploaded.mimeType.startsWith('image/');
  const index = grid.children.length;

  const itemHtml = `
    <div class="article-editor__media-item" data-media-id="${uploaded.id}" data-index="${index}">
      <div class="article-editor__media-item__preview">
        ${isImage
          ? `<img src="${uploaded.url}" alt="${escapeHtml(uploaded.filename)}" loading="lazy">`
          : `<div class="article-editor__media-item__file">${escapeHtml(uploaded.filename)}</div>`
        }
      </div>
      <div class="article-editor__media-item__actions">
        <input type="text" class="article-editor__media-item__caption" value="" placeholder="Caption..." data-index="${index}">
        <button type="button" class="article-editor__media-item__remove" data-index="${index}" title="Remove" aria-label="Remove media">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
      <input type="hidden" name="media_ids[]" value="${uploaded.id}" data-index="${index}">
    </div>
  `;

  grid.insertAdjacentHTML('beforeend', itemHtml);
}

export async function uploadMedia(
  file: File,
  mediaUploadUrl: string,
  csrfToken: string,
): Promise<UploadedMedia> {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('_token', csrfToken);

  const response = await fetch(mediaUploadUrl, {
    method: 'POST',
    body: formData,
  });

  if (!response.ok) {
    throw new Error(`Upload failed: ${response.statusText}`);
  }

  const data = await response.json() as Record<string, unknown>;

  return {
    id: typeof data.id === 'string' ? data.id : '',
    url: typeof data.url === 'string' ? data.url : typeof data.path === 'string' ? data.path : '',
    filename: typeof data.filename === 'string' ? data.filename : file.name,
    mimeType: typeof data.mime_type === 'string' ? data.mime_type : file.type,
  };
}

export function collectMediaIds(form: HTMLFormElement): string[] {
  const mediaIds: string[] = [];
  const mediaElements = form.querySelectorAll('[data-media-id]');

  mediaElements.forEach((element) => {
    const mediaId = element.getAttribute('data-media-id');
    if (mediaId) {
      mediaIds.push(mediaId);
    }
  });

  return mediaIds;
}

export function appendMediaIdsInput(form: HTMLFormElement, mediaIds: string[]): void {
  if (0 === mediaIds.length) {
    return;
  }

  const mediaIdsInput = document.createElement('input');
  mediaIdsInput.type = 'hidden';
  mediaIdsInput.name = 'media_ids[]';
  mediaIdsInput.value = mediaIds.join(',');
  form.appendChild(mediaIdsInput);
}
