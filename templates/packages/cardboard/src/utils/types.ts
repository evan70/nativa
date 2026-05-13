/**
 * Common types and interfaces for cardboard components
 */

/**
 * Generic configuration for a component
 */
export interface ComponentConfig {
  /** Unique identifier for the component instance */
  id?: string;
  /** CSS selector for the root element */
  selector?: string;
  /** Whether component is enabled */
  enabled?: boolean;
}

/**
 * Options for initializing a section
 */
export interface SectionInitOptions {
  /** Section name as registered in SectionLoader */
  section: string;
  /** Root element */
  element: HTMLElement;
  /** Optional configuration */
  config?: Record<string, unknown>;
}

/**
 * API response wrapper
 */
export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}

/**
 * Result type for operations that can fail
 */
export type Result<T, E = Error> = 
  | { ok: true; value: T }
  | { ok: false; error: E };

/**
 * Options for debounced or throttled functions
 */
export interface ThrottleOptions {
  /** Delay in milliseconds */
  delay: number;
  /** If true, call at leading edge; if false, at trailing edge */
  leading?: boolean;
  /** If true, call at trailing edge */
  trailing?: boolean;
}

/**
 * Event handler type
 */
export type EventHandler<T extends Event = Event> = (event: T) => void;

/**
 * Destroyable interface for cleanup
 */
export interface Destroyable {
  destroy(): void;
}