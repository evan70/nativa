import { NotificationService } from '@kernel/ui/notification';
import { clearDraft, loadDraft, saveDraft, shouldOfferRestore, showRestorePrompt } from './autosave';
import { addMediaToGallery, appendMediaIdsInput, collectMediaIds, uploadMedia } from './media';
import { configureMarked, togglePreview, updatePreview } from './preview';
import { escapeHtml, renderEditorForm } from './render';
import { queueSuccessAndRedirect, submitArticle, validateSubmission } from './submit';
import type { ArticleEditorConfig, EditorState, UploadedMedia } from './types';

interface ToolbarContext {
  editor: HTMLTextAreaElement;
  preview: HTMLElement;
}

export class ArticleEditor {
  private readonly container: HTMLElement;

  private readonly config: ArticleEditorConfig;

  private readonly state: EditorState = {
    isUploading: false,
    isSubmitting: false,
  };

  private textarea: HTMLTextAreaElement | null = null;

  private preview: HTMLElement | null = null;

  private titleInput: HTMLInputElement | null = null;

  private excerptTextarea: HTMLTextAreaElement | null = null;

  private statusSelect: HTMLSelectElement | null = null;

  private slugInput: HTMLInputElement | null = null;

  private categorySelect: HTMLSelectElement | null = null;

  private debounceTimer: ReturnType<typeof setTimeout> | null = null;

  private autoSaveTimer: ReturnType<typeof setInterval> | null = null;

  private beforeUnloadHandler: ((e: BeforeUnloadEvent) => void) | null = null;

  constructor(container: HTMLElement, config: ArticleEditorConfig) {
    this.container = container;
    this.config = config;
  }

  public init(): void {
    try {
      this.render();
      this.bindEvents();
      this.loadAutoSave();
      this.startAutoSave();
      configureMarked();
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Unknown error';
      this.handleError(errorMessage);
    }
  }

  public destroy(): void {
    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }
    if (this.autoSaveTimer) {
      clearInterval(this.autoSaveTimer);
    }
    if (this.beforeUnloadHandler) {
      window.removeEventListener('beforeunload', this.beforeUnloadHandler);
      this.beforeUnloadHandler = null;
    }
    this.container.innerHTML = '';
  }

  private render(): void {
    this.container.innerHTML = renderEditorForm(
      this.config.articleData,
      this.config.submitUrl,
      this.config.csrfToken,
      this.config.mode,
      this.config.categories ?? [],
      this.config.availableTags ?? [],
    );

    this.textarea = this.container.querySelector('#article-content');
    this.preview = this.container.querySelector('#article-preview');
    this.titleInput = this.container.querySelector('#article-title');
    this.excerptTextarea = this.container.querySelector('#article-excerpt');
    this.statusSelect = this.container.querySelector('#article-status');
    this.slugInput = this.container.querySelector('#article-slug');
    this.categorySelect = this.container.querySelector('#article-category');

    if (this.textarea && this.preview) {
      updatePreview(this.preview, this.textarea.value);
    }
  }

  private bindEvents(): void {
    this.textarea?.addEventListener('input', (e) => {
      this.onContentChange((e.target as HTMLTextAreaElement).value);
    });

    this.titleInput?.addEventListener('input', () => this.saveToServer());
    this.excerptTextarea?.addEventListener('input', () => this.saveToServer());

    const toolbar = this.container.querySelector('.article-editor__toolbar');
    toolbar?.addEventListener('click', (e) => {
      const button = (e.target as HTMLElement).closest('[data-action]');
      if (!button || !this.textarea || !this.preview) {
        return;
      }

      const action = button.getAttribute('data-action');
      if (action) {
        this.handleToolbarAction(action, {
          editor: this.textarea,
          preview: this.preview,
        });
      }
    });

    this.textarea?.addEventListener('keydown', (e) => this.handleKeyboardShortcuts(e));

    this.textarea?.addEventListener('dragover', (e) => {
      e.preventDefault();
      this.textarea?.style.setProperty('border-color', 'var(--color-primary)');
    });

    this.textarea?.addEventListener('dragleave', () => {
      this.textarea?.style.removeProperty('border-color');
    });

    this.textarea?.addEventListener('drop', (e) => {
      e.preventDefault();
      this.textarea?.style.removeProperty('border-color');
      const files = e.dataTransfer?.files;
      if (files && files.length > 0) {
        void this.handleImageFiles(files);
      }
    });

    this.textarea?.addEventListener('paste', (e) => {
      const items = e.clipboardData?.items;
      if (!items) {
        return;
      }

      const imageItems = Array.from(items).filter((item) => item.type.startsWith('image/'));
      if (0 === imageItems.length) {
        return;
      }

      e.preventDefault();
      const files = imageItems.map((item) => item.getAsFile()).filter(Boolean) as File[];
      void this.handleImageFiles(files);
    });

    const fileInput = this.container.querySelector('#article-image-upload');
    fileInput?.addEventListener('change', (e) => {
      const input = e.target as HTMLInputElement;
      if (input.files && input.files.length > 0) {
        void this.handleImageFiles(input.files);
      }
    });

    this.bindMediaEvents();

    const form = this.container.querySelector('#article-editor-form');
    form?.addEventListener('submit', (e) => void this.handleSubmit(e));

    const toggleButton = this.container.querySelector('#article-preview-toggle');
    toggleButton?.addEventListener('click', () => togglePreview(this.container));

    this.beforeUnloadHandler = (e: BeforeUnloadEvent) => {
      if (this.hasUnsavedChanges()) {
        e.preventDefault();
        e.returnValue = '';
      }
    };
    window.addEventListener('beforeunload', this.beforeUnloadHandler);
  }

  private bindMediaEvents(): void {
    const dropzone = this.container.querySelector('#article-dropzone');
    const mediaInput = this.container.querySelector('#article-media-upload') as HTMLInputElement | null;

    dropzone?.addEventListener('click', () => mediaInput?.click());
    mediaInput?.addEventListener('change', (e) => {
      const input = e.target as HTMLInputElement;
      if (input.files && input.files.length > 0) {
        void this.handleMediaFiles(input.files);
      }
    });

    dropzone?.addEventListener('dragover', (e) => {
      e.preventDefault();
      (dropzone as HTMLElement).classList.add('article-editor__dropzone--active');
    });

    dropzone?.addEventListener('dragleave', () => {
      (dropzone as HTMLElement).classList.remove('article-editor__dropzone--active');
    });

    dropzone?.addEventListener('drop', (e) => {
      e.preventDefault();
      (dropzone as HTMLElement).classList.remove('article-editor__dropzone--active');
      const files = (e as DragEvent).dataTransfer?.files;
      if (files && files.length > 0) {
        void this.handleMediaFiles(files);
      }
    });

    const mediaGrid = this.container.querySelector('#article-media-grid');
    mediaGrid?.addEventListener('click', (e) => {
      const removeBtn = (e.target as HTMLElement).closest('.article-editor__media-item__remove');
      if (removeBtn) {
        removeBtn.closest('.article-editor__media-item')?.remove();
      }
    });
  }

  private handleToolbarAction(action: string, context: ToolbarContext): void {
    switch (action) {
      case 'bold':
        this.insertMarkdown(context.editor, '**', '**', 'bold text');
        break;
      case 'italic':
        this.insertMarkdown(context.editor, '_', '_', 'italic text');
        break;
      case 'heading':
        this.insertMarkdown(context.editor, '## ', '', 'Heading');
        break;
      case 'link': {
        const url = prompt('Enter URL:');
        if (url) {
          const text = prompt('Enter link text:') || 'link text';
          this.insertMarkdown(context.editor, `[${text}](${url})`);
        }
        break;
      }
      case 'image': {
        const fileInput = this.container.querySelector('#article-image-upload') as HTMLInputElement | null;
        fileInput?.click();
        break;
      }
      case 'code':
        this.insertMarkdown(context.editor, '```\n', '\n```', 'code here');
        break;
      case 'quote':
        this.insertMarkdown(context.editor, '> ', '', 'Quote text');
        break;
      case 'list':
        this.insertMarkdown(context.editor, '- ', '', 'List item');
        break;
      case 'preview':
        togglePreview(this.container);
        break;
    }

    updatePreview(context.preview, context.editor.value);
  }

  private insertMarkdown(editor: HTMLTextAreaElement, before: string, after = '', placeholder = ''): void {
    const start = editor.selectionStart;
    const end = editor.selectionEnd;
    const selectedText = editor.value.substring(start, end) || placeholder;
    const newText = editor.value.substring(0, start) + before + selectedText + after + editor.value.substring(end);

    editor.value = newText;
    editor.selectionStart = start + before.length;
    editor.selectionEnd = start + before.length + selectedText.length;
    editor.focus();

    updatePreview(this.preview, newText);
    this.saveToServer();
  }

  private handleKeyboardShortcuts(e: KeyboardEvent): void {
    if (!this.textarea || !this.preview) {
      return;
    }

    const ctrl = e.ctrlKey || e.metaKey;
    if (ctrl && 'b' === e.key) {
      e.preventDefault();
      this.handleToolbarAction('bold', { editor: this.textarea, preview: this.preview });
    } else if (ctrl && 'i' === e.key) {
      e.preventDefault();
      this.handleToolbarAction('italic', { editor: this.textarea, preview: this.preview });
    } else if (ctrl && 'h' === e.key) {
      e.preventDefault();
      this.handleToolbarAction('heading', { editor: this.textarea, preview: this.preview });
    } else if (ctrl && 'k' === e.key) {
      e.preventDefault();
      this.handleToolbarAction('link', { editor: this.textarea, preview: this.preview });
    } else if (ctrl && e.altKey && 'c' === e.key) {
      e.preventDefault();
      this.handleToolbarAction('code', { editor: this.textarea, preview: this.preview });
    } else if (ctrl && e.shiftKey && 'Q' === e.key) {
      e.preventDefault();
      this.handleToolbarAction('quote', { editor: this.textarea, preview: this.preview });
    } else if (ctrl && e.shiftKey && 'L' === e.key) {
      e.preventDefault();
      this.handleToolbarAction('list', { editor: this.textarea, preview: this.preview });
    }
  }

  private async handleImageFiles(files: FileList | File[]): Promise<void> {
    if (this.state.isUploading) {
      return;
    }

    this.state.isUploading = true;
    this.setStatus('Uploading image...');

    try {
      for (const file of Array.from(files)) {
        const result = await this.uploadSingleMedia(file);
        if (result.url && this.textarea) {
          const markdownImage = `\n![${file.name}](${result.url})\n`;
          this.insertMarkdown(this.textarea, markdownImage);
        }
      }

      this.setStatus('Image uploaded successfully!');
      window.setTimeout(() => this.setStatus(''), 3000);
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to upload image';
      this.handleError(errorMessage);
      this.setStatus('Image upload failed');
    } finally {
      this.state.isUploading = false;
    }
  }

  private async handleMediaFiles(files: FileList | File[]): Promise<void> {
    if (this.state.isUploading) {
      return;
    }

    this.state.isUploading = true;
    this.setStatus('Uploading...');

    try {
      for (const file of Array.from(files)) {
        const result = await this.uploadSingleMedia(file);
        if (result.url) {
          addMediaToGallery(this.container, result);
        }
      }
      this.setStatus('Upload complete!');
      window.setTimeout(() => this.setStatus(''), 3000);
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to upload media';
      this.handleError(errorMessage);
    } finally {
      this.state.isUploading = false;
    }
  }

  private async uploadSingleMedia(file: File): Promise<UploadedMedia> {
    return uploadMedia(file, this.config.mediaUploadUrl, this.config.csrfToken);
  }

  private onContentChange(content: string): void {
    if (this.debounceTimer) {
      clearTimeout(this.debounceTimer);
    }

    this.debounceTimer = window.setTimeout(() => {
      updatePreview(this.preview, content);
      this.saveToServer();
    }, 300);
  }

  private saveToServer(): void {
    if (this.state.isSubmitting || !this.textarea || !this.titleInput) {
      return;
    }

    // Fire-and-forget — errors are silent
    saveDraft(
      this.config.mode,
      this.config.articleId,
      {
        title: this.titleInput.value,
        excerpt: this.excerptTextarea?.value ?? '',
        content: this.textarea.value,
        status: this.statusSelect?.value ?? 'draft',
        timestamp: Date.now(),
      },
    ).catch(() => {
      // Silently ignore save failures (offline, etc.)
    });
  }

  private async loadAutoSave(): Promise<void> {
    if (!this.textarea || !this.titleInput) {
      return;
    }

    let draft: ReturnType<typeof loadDraft> extends Promise<infer T> ? T : never;
    try {
      draft = await loadDraft(this.config.mode, this.config.articleId);
    } catch {
      return;
    }

    if (!draft || !shouldOfferRestore(this.config.articleData.content, draft)) {
      return;
    }

    const savedTime = new Date(draft.timestamp).toLocaleString();
    showRestorePrompt(savedTime, () => {
      if (!this.textarea || !this.titleInput) {
        return;
      }

      this.titleInput.value = draft.title;
      if (this.excerptTextarea) {
        this.excerptTextarea.value = draft.excerpt;
      }
      this.textarea.value = draft.content;
      if (this.statusSelect) {
        this.statusSelect.value = draft.status;
      }
      updatePreview(this.preview, draft.content);
    });
  }

  private startAutoSave(): void {
    this.autoSaveTimer = window.setInterval(() => this.saveToServer(), 30000);
  }

  private hasUnsavedChanges(): boolean {
    // Conservative check — assume there are changes if we can't verify
    // Real verification would require async server call, which is impractical here
    return !this.state.isSubmitting && !!this.textarea && !!this.titleInput
      && ('' !== this.textarea.value || '' !== this.titleInput.value);
  }

  private async handleSubmit(e: Event): Promise<void> {
    e.preventDefault();

    this.state.isSubmitting = true;
    if (this.beforeUnloadHandler) {
      window.removeEventListener('beforeunload', this.beforeUnloadHandler);
      this.beforeUnloadHandler = null;
    }
    if (this.autoSaveTimer) {
      clearInterval(this.autoSaveTimer);
      this.autoSaveTimer = null;
    }

    this.setStatus('Saving...');

    const form = e.target as HTMLFormElement;
    // Fire-and-forget — clear draft after successful submit
    void clearDraft(this.config.mode, this.config.articleId);

    const title = this.titleInput?.value.trim() || '';
    const content = this.textarea?.value.trim() || '';
    const validationError = validateSubmission(title, content);

    if (validationError) {
      this.setStatus('');
      this.handleError(validationError);
      ('Title is required' === validationError ? this.titleInput : this.textarea)?.focus();
      return;
    }

    appendMediaIdsInput(form, collectMediaIds(form));

    try {
      const formData = new FormData(form);
      this.config.onSubmit?.(formData);
      const result = await submitArticle(this.config.submitUrl, formData);
      queueSuccessAndRedirect(
        'edit' === this.config.mode ? 'Article updated successfully!' : 'Article created successfully!',
        result.redirectUrl,
      );
    } catch (error) {
      this.setStatus('');
      this.handleError(error instanceof Error ? error.message : 'Failed to save article');
    }
  }

  private setStatus(message: string): void {
    const statusEl = this.container.querySelector('#article-editor-status');
    if (statusEl) {
      statusEl.textContent = message;
    }
  }

  private handleError(message: string): void {
    if (this.config.onError) {
      this.config.onError(message);
      return;
    }

    NotificationService.error(message);
  }
}

export default ArticleEditor;
