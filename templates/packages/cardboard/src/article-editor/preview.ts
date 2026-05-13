import { marked } from 'marked';
import DOMPurify from 'dompurify';

declare const Prism: {
  highlightAllUnder(root: ParentNode): void;
} | undefined;

export function configureMarked(): void {
  marked.setOptions({
    breaks: true,
    gfm: true,
    highlight: (code) => code,
  });
}

export function updatePreview(preview: HTMLElement | null, content: string): void {
  if (!preview) {
    return;
  }

  try {
    const html = marked.parse(content);
    const cleanHtml = DOMPurify.sanitize(html as string);
    preview.innerHTML = cleanHtml;

    if ('undefined' !== typeof Prism) {
      Prism.highlightAllUnder(preview);
    }
  } catch (error) {
    console.error('[ArticleEditor] Preview render error:', error);
    preview.innerHTML = '<p class="article-editor__preview-error">Error rendering preview</p>';
  }
}

export function togglePreview(container: HTMLElement): void {
  const previewPane = container.querySelector('.article-editor__pane--preview');
  const toggleBtn = container.querySelector('.article-editor__toolbar__btn--preview');

  if (!previewPane) {
    return;
  }

  previewPane.classList.toggle('article-editor__pane--hidden');
  const isHidden = previewPane.classList.contains('article-editor__pane--hidden');

  if (toggleBtn) {
    toggleBtn.classList.toggle('active', !isHidden);
  }

  const editorPane = container.querySelector('.article-editor__pane--editor');
  if (editorPane) {
    editorPane.classList.toggle('article-editor__pane--full', isHidden);
  }
}
