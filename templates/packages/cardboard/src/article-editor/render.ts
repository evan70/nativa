import type { EditorArticleModel, EditorCategoryOption, EditorMediaItem, EditorTagOption } from './types';

// Simple inline escapeHtml function
function escapeHtml(str: string): string {
  if (!str) return '';
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

export { escapeHtml };

export function renderCategoryOptions(selectedId: string, categories: EditorCategoryOption[]): string {
  if (0 === categories.length) {
    return '';
  }

  return categories.map((cat) =>
    `<option value="${cat.id}" ${cat.id === selectedId ? 'selected' : ''}>${escapeHtml(cat.name)}</option>`
  ).join('');
}

export function renderTagsCheckboxes(selectedTags: string[], availableTags: EditorTagOption[]): string {
  if (0 === availableTags.length) {
    return '<p class="article-editor__tags-empty">No tags available. <a href="/mark/tags" target="_blank">Create tags first</a></p>';
  }

  return availableTags.map((tag) => {
    const isChecked = selectedTags.includes(tag.name) ? 'checked' : '';

    return `
      <label class="article-editor__tag-checkbox">
        <input 
          type="checkbox" 
          name="tags[]" 
          value="${escapeHtml(tag.id)}" 
          ${isChecked}
          data-tag-name="${escapeHtml(tag.name)}"
        >
        <span>${escapeHtml(tag.name)}</span>
      </label>
    `;
  }).join('');
}

export function renderMediaGallery(media: EditorMediaItem[]): string {
  if (0 === media.length) {
    return '';
  }

  return media.map((item, index) => {
    const caption = item.caption ?? item.filename ?? '';

    return `
      <div class="article-editor__media-item" data-media-id="${item.mediaId}" data-index="${index}">
        <div class="article-editor__media-item__preview">
          ${item.isImage
            ? `<img src="${item.url}" alt="${escapeHtml(caption)}" loading="lazy">`
            : `<div class="article-editor__media-item__file">${escapeHtml(item.filename ?? 'file')}</div>`
          }
        </div>
        <div class="article-editor__media-item__actions">
          <input type="text" class="article-editor__media-item__caption" value="${escapeHtml(caption)}" placeholder="Caption..." data-index="${index}">
          <button type="button" class="article-editor__media-item__remove" data-index="${index}" title="Remove" aria-label="Remove media">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        <input type="hidden" name="media_ids[]" value="${item.mediaId}" data-index="${index}">
      </div>
    `;
  }).join('');
}

export function renderEditorForm(
  article: EditorArticleModel,
  submitUrl: string,
  csrfToken: string,
  mode: 'create' | 'edit',
  categories: EditorCategoryOption[],
  tags: EditorTagOption[],
): string {
  return `
    <form class="article-editor" id="article-editor-form" method="POST" action="${submitUrl}">
      <input type="hidden" name="_token" value="${csrfToken}">
      
      <div class="article-editor__header">
        <div class="article-editor__field">
          <label class="article-editor__label" for="article-title">Title</label>
          <input 
            class="article-editor__input article-editor__input--title" 
            id="article-title" 
            name="title" 
            type="text" 
            value="${escapeHtml(article.title)}"
            placeholder="Enter article title..."
            required
          >
        </div>
        
        <div class="article-editor__field">
          <label class="article-editor__label" for="article-excerpt">Excerpt</label>
          <textarea 
            class="article-editor__textarea article-editor__textarea--excerpt" 
            id="article-excerpt" 
            name="excerpt" 
            rows="2"
            placeholder="Brief summary of the article..."
          >${escapeHtml(article.excerpt)}</textarea>
        </div>
        
        <div class="article-editor__field article-editor__field--inline">
          <div class="article-editor__field-group">
            <label class="article-editor__label" for="article-status">Status</label>
            <select class="article-editor__select" id="article-status" name="status">
              <option value="draft" ${'draft' === article.status ? 'selected' : ''}>Draft</option>
              <option value="published" ${'published' === article.status ? 'selected' : ''}>Published</option>
            </select>
          </div>

          <div class="article-editor__field-group">
            <label class="article-editor__label" for="article-slug">Slug</label>
            <input
              class="article-editor__input"
              id="article-slug"
              name="slug"
              type="text"
              value="${escapeHtml(article.slug)}"
              placeholder="auto-generated"
            >
          </div>
        </div>

        <div class="article-editor__field article-editor__field--inline">
          <div class="article-editor__field-group">
            <label class="article-editor__label" for="article-category">Category</label>
            <select class="article-editor__select" id="article-category" name="category_id">
              <option value="">-- No Category --</option>
              ${renderCategoryOptions(article.categoryId, categories)}
            </select>
          </div>

          <div class="article-editor__field-group article-editor__field-group--tags">
            <label class="article-editor__label">Tags</label>
            <div class="article-editor__tags-checkboxes">
              ${renderTagsCheckboxes(article.tags, tags)}
              <div class="article-editor__tags-hint" style="margin-top: 8px;">
                <a href="/mark/tags" target="_blank" class="article-editor__link">+ Manage Tags</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="article-editor__field">
        <label class="article-editor__label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <circle cx="8.5" cy="8.5" r="1.5"></circle>
            <polyline points="21 15 16 10 5 21"></polyline>
          </svg>
          Media Gallery
        </label>
        <div class="article-editor__media-gallery" id="article-media-gallery">
          <div class="article-editor__dropzone" id="article-dropzone">
            <input type="file" id="article-media-upload" name="media_files[]" accept="image/*" multiple hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="17 8 12 3 7 8"></polyline>
              <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            <p>Drag & drop images here or <span class="article-editor__dropzone__link">browse</span></p>
            <span class="article-editor__dropzone__hint">JPG, PNG, GIF, WebP up to 10MB</span>
          </div>
          <div class="article-editor__media-grid" id="article-media-grid">
            ${renderMediaGallery(article.media)}
          </div>
          <div id="article-media-inputs"></div>
        </div>
      </div>

      <div class="article-editor__seo-section">
        <h3 class="article-editor__seo-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
          Search Engine Optimization (SEO)
        </h3>
        
        <div class="article-editor__seo-grid">
          <div class="article-editor__field">
            <label class="article-editor__label" for="article-meta-title">Meta Title</label>
            <input class="article-editor__input" id="article-meta-title" name="meta_title" type="text" value="${escapeHtml(article.metaTitle)}" placeholder="SEO title (50-60 characters)" maxlength="70">
            <small class="article-editor__hint">Displayed in search results</small>
          </div>

          <div class="article-editor__field article-editor__field--full">
            <label class="article-editor__label" for="article-meta-description">Meta Description</label>
            <textarea class="article-editor__textarea" id="article-meta-description" name="meta_description" rows="3" placeholder="Brief summary for search engines (150-160 characters)" maxlength="170">${escapeHtml(article.metaDescription)}</textarea>
            <small class="article-editor__hint">Concise summary of article content</small>
          </div>

          <div class="article-editor__field">
            <label class="article-editor__label" for="article-meta-keywords">Meta Keywords</label>
            <input class="article-editor__input" id="article-meta-keywords" name="meta_keywords" type="text" value="${escapeHtml(article.metaKeywords)}" placeholder="keyword1, keyword2, keyword3">
            <small class="article-editor__hint">Comma-separated keywords (optional)</small>
          </div>

          <div class="article-editor__field">
            <label class="article-editor__label" for="article-canonical-url">Canonical URL</label>
            <input class="article-editor__input" id="article-canonical-url" name="canonical_url" type="url" value="${escapeHtml(article.canonicalUrl)}" placeholder="https://example.com/article-url">
            <small class="article-editor__hint">Prevent duplicate content issues</small>
          </div>
        </div>

        <h4 class="article-editor__seo-subtitle">Social Media Preview (Open Graph & Twitter Cards)</h4>
        
        <div class="article-editor__seo-grid">
          <div class="article-editor__field">
            <label class="article-editor__label" for="article-og-title">Open Graph Title</label>
            <input class="article-editor__input" id="article-og-title" name="og_title" type="text" value="${escapeHtml(article.ogTitle)}" placeholder="Title for Facebook/LinkedIn">
          </div>

          <div class="article-editor__field">
            <label class="article-editor__label" for="article-og-description">Open Graph Description</label>
            <textarea class="article-editor__textarea" id="article-og-description" name="og_description" rows="2" placeholder="Description for social media">${escapeHtml(article.ogDescription)}</textarea>
          </div>

          <div class="article-editor__field">
            <label class="article-editor__label" for="article-og-image">Open Graph Image URL</label>
            <input class="article-editor__input" id="article-og-image" name="og_image" type="url" value="${escapeHtml(article.ogImage)}" placeholder="https://example.com/image.jpg (1200x630 recommended)">
          </div>

          <div class="article-editor__field">
            <label class="article-editor__label" for="article-twitter-title">Twitter Card Title</label>
            <input class="article-editor__input" id="article-twitter-title" name="twitter_title" type="text" value="${escapeHtml(article.twitterTitle)}" placeholder="Title for Twitter">
          </div>

          <div class="article-editor__field">
            <label class="article-editor__label" for="article-twitter-description">Twitter Card Description</label>
            <textarea class="article-editor__textarea" id="article-twitter-description" name="twitter_description" rows="2" placeholder="Description for Twitter">${escapeHtml(article.twitterDescription)}</textarea>
          </div>

          <div class="article-editor__field">
            <label class="article-editor__label" for="article-twitter-image">Twitter Card Image URL</label>
            <input class="article-editor__input" id="article-twitter-image" name="twitter_image" type="url" value="${escapeHtml(article.twitterImage)}" placeholder="https://example.com/image.jpg (1200x675 recommended)">
          </div>
        </div>

        <div class="article-editor__field article-editor__field--full">
          <label class="article-editor__label" for="article-schema-json">Schema.org JSON-LD</label>
          <textarea class="article-editor__textarea article-editor__textarea--code" id="article-schema-json" name="schema_json" rows="6" placeholder='{"@type": "Article", "headline": "...", ...}'>${escapeHtml(article.schemaJson)}</textarea>
          <small class="article-editor__hint">Structured data for rich search results</small>
        </div>

        <div class="article-editor__field article-editor__field--full">
          <label class="article-editor__label" for="article-scheduled-at">Schedule Publishing</label>
          <input class="article-editor__input" id="article-scheduled-at" name="scheduled_at" type="datetime-local" value="${escapeHtml(article.scheduledAt)}">
          <small class="article-editor__hint">Leave empty to publish immediately</small>
        </div>

        <div class="article-editor__field article-editor__field--full">
          <label class="article-editor__label" for="article-author-name">Author Name Override</label>
          <input class="article-editor__input" id="article-author-name" name="author_name" type="text" value="${escapeHtml(article.authorName)}" placeholder="Leave empty to use logged-in user">
          <small class="article-editor__hint">Override default author (for guest posts, etc.)</small>
        </div>
      </div>

      <div class="article-editor__toolbar" role="toolbar" aria-label="Markdown formatting">
        <button type="button" class="article-editor__toolbar__btn" data-action="bold" title="Bold (Ctrl+B)"><strong>B</strong></button>
        <button type="button" class="article-editor__toolbar__btn" data-action="italic" title="Italic (Ctrl+I)"><em>I</em></button>
        <button type="button" class="article-editor__toolbar__btn" data-action="heading" title="Heading (Ctrl+H)">H</button>
        <button type="button" class="article-editor__toolbar__btn" data-action="link" title="Link (Ctrl+K)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
          </svg>
        </button>
        <button type="button" class="article-editor__toolbar__btn" data-action="image" title="Image Upload">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <circle cx="8.5" cy="8.5" r="1.5"></circle>
            <polyline points="21 15 16 10 5 21"></polyline>
          </svg>
        </button>
        <button type="button" class="article-editor__toolbar__btn" data-action="code" title="Code Block (Ctrl+Alt+C)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <polyline points="16 18 22 12 16 6"></polyline>
            <polyline points="8 6 2 12 8 18"></polyline>
          </svg>
        </button>
        <button type="button" class="article-editor__toolbar__btn" data-action="quote" title="Quote (Ctrl+Shift+Q)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21c0 1 0 1 1 1z"></path>
            <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"></path>
          </svg>
        </button>
        <button type="button" class="article-editor__toolbar__btn" data-action="list" title="List (Ctrl+Shift+L)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
          </svg>
        </button>
        <span class="article-editor__toolbar__separator"></span>
        <button type="button" class="article-editor__toolbar__btn article-editor__toolbar__btn--preview active" data-action="preview" title="Toggle Preview (Ctrl+P)">
          <svg class="article-editor__toolbar__btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
          <span class="article-editor__toolbar__btn-label">Preview</span>
        </button>
      </div>

      <div class="article-editor__panes">
        <div class="article-editor__pane article-editor__pane--editor">
          <label class="article-editor__label" for="article-content">Markdown</label>
          <textarea class="article-editor__textarea article-editor__textarea--content" id="article-content" name="content" placeholder="Write your article in Markdown..." required>${escapeHtml(article.content)}</textarea>
        </div>

        <div class="article-editor__pane article-editor__pane--preview">
          <label class="article-editor__label">Preview</label>
          <div class="article-editor__preview" id="article-preview" aria-live="polite"></div>
        </div>
      </div>

      <div class="article-editor__actions">
        <button type="submit" class="btn btn--primary" id="article-submit">
          ${'edit' === mode ? 'Save Changes' : 'Create Article'}
        </button>
        <button type="button" class="btn btn--outline" id="article-preview-toggle">
          Toggle Preview
        </button>
        <span class="article-editor__status" id="article-editor-status"></span>
      </div>
    </form>

    <input type="file" id="article-image-upload" accept="image/*" style="display: none;">
  `;
}
