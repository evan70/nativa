/**
 * Cardboard UI Components
 * 
 * A comprehensive UI component library for Vanilla Cards,
 * following vanilla-cards architecture with BEM methodology.
 */

// Article Editor
export { ArticleEditor, default } from './article-editor';
export type {
  ArticleEditorConfig,
  EditorArticleModel,
  EditorCategoryOption,
  EditorMediaItem,
  EditorTagOption,
  RawArticleData,
} from './article-editor/types';

export {
  normalizeArticleData,
  normalizeCategories,
  normalizeMediaItem,
  normalizeTags,
} from './article-editor/normalizers';

export {
  queueSuccessAndRedirect,
  submitArticle,
  validateSubmission,
} from './article-editor/submit';

// Base Components
export { BaseComponent, type ComponentOptions } from './base/BaseComponent.ts';

// Utilities
export {
  // DOM
  query,
  queryAll,
  createElement,
  addEventListener,
  delegate,
  toggleClass,
  addClass,
  removeClass,
  hasClass,
  show,
  hide,
  setData,
  getData,
  remove,
  insertAfter,
  insertBefore,
  // Events
  debounce,
  throttle,
  throttleWithOptions,
  createCustomEvent,
  dispatch,
  once,
  waitFor,
  // API
  request,
  get,
  post,
  put,
  patch,
  del,
  submitForm,
  uploadFile,
  buildUrl,
  // Notifications (backward compatible)
  NotificationAdapter,
  NotificationService,
} from './utils/index.ts';

// Re-export types
export type {
  ComponentConfig,
  SectionInitOptions,
  ApiResponse,
  Result,
  ThrottleOptions,
  EventHandler,
  Destroyable,
  HttpMethod,
  RequestOptions,
  NotificationType,
  NotificationPosition,
} from './utils/index.ts';