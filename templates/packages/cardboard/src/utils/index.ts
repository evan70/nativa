/**
 * Cardboard Utilities
 */

// Types
export type {
  ComponentConfig,
  SectionInitOptions,
  ApiResponse,
  Result,
  ThrottleOptions,
  EventHandler,
  Destroyable,
} from './types.ts';

// DOM utilities
export {
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
} from './dom.ts';

// Event utilities
export {
  debounce,
  throttle,
  throttleWithOptions,
  createCustomEvent,
  dispatch,
  once,
  waitFor,
} from './events.ts';

// API utilities
export {
  request,
  get,
  post,
  put,
  patch,
  del,
  submitForm,
  uploadFile,
  buildUrl,
} from './api.ts';
export type { HttpMethod, RequestOptions } from './api.ts';

// Notification adapter (backward compatible)
export {
  NotificationAdapter,
  NotificationService,
} from './NotificationAdapter.ts';
export type { NotificationType, NotificationPosition } from './NotificationAdapter.ts';