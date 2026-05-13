/**
 * Event utility functions - debounce, throttle, and event helpers
 */

import type { ThrottleOptions } from './types.ts';

/**
 * Debounce function - delays execution until after wait ms of no calls
 * 
 * @param fn - Function to debounce
 * @param wait - Milliseconds to wait
 * @param immediate - If true, call on leading edge
 * @returns Debounced function
 */
export function debounce<T extends (...args: unknown[]) => unknown>(
  fn: T,
  wait: number,
  immediate = false,
): (...args: Parameters<T>) => void {
  let timeout: ReturnType<typeof setTimeout> | null = null;

  return function (this: unknown, ...args: Parameters<T>) {
    const context = this;

    const callNow = immediate && !timeout;

    if (timeout) {
      clearTimeout(timeout);
    }

    timeout = setTimeout(() => {
      timeout = null;
      if (!immediate) {
        fn.apply(context, args);
      }
    }, wait);

    if (callNow) {
      fn.apply(context, args);
    }
  };
}

/**
 * Throttle function - limits execution to once per wait ms
 * 
 * @param fn - Function to throttle
 * @param wait - Minimum ms between executions
 * @returns Throttled function
 */
export function throttle<T extends (...args: unknown[]) => unknown>(
  fn: T,
  wait: number,
): (...args: Parameters<T>) => void {
  let lastTime = 0;
  let timeout: ReturnType<typeof setTimeout> | null = null;

  return function (this: unknown, ...args: Parameters<T>) {
    const context = this;
    const now = Date.now();

    if (now - lastTime >= wait) {
      if (timeout) {
        clearTimeout(timeout);
        timeout = null;
      }
      lastTime = now;
      fn.apply(context, args);
    } else if (!timeout) {
      timeout = setTimeout(() => {
        lastTime = Date.now();
        timeout = null;
        fn.apply(context, args);
      }, wait - (now - lastTime));
    }
  };
}

/**
 * Advanced throttle with options for leading/trailing edge control
 * 
 * @param fn - Function to throttle
 * @param options - Throttle options
 * @returns Throttled function
 */
export function throttleWithOptions<T extends (...args: unknown[]) => unknown>(
  fn: T,
  options: ThrottleOptions,
): (...args: Parameters<T>) => void {
  const { delay, leading = true, trailing = true } = options;
  let lastTime = 0;
  let timeout: ReturnType<typeof setTimeout> | null = null;
  let lastArgs: Parameters<T> | null = null;

  return function (this: unknown, ...args: Parameters<T>) {
    const context = this;
    const now = Date.now();
    const remaining = delay - (now - lastTime);

    lastArgs = args;

    if (remaining <= 0) {
      if (timeout) {
        clearTimeout(timeout);
        timeout = null;
      }
      lastTime = now;
      fn.apply(context, args);
    } else if (trailing && !timeout) {
      timeout = setTimeout(() => {
        lastTime = Date.now();
        timeout = null;
        if (lastArgs) {
          fn.apply(context, lastArgs);
        }
      }, remaining);
    }
  };
}

/**
 * Create a custom event
 * 
 * @param name - Event name
 * @param detail - Event detail data
 * @param options - Event options
 * @returns CustomEvent
 */
export function createCustomEvent<T = unknown>(
  name: string,
  detail: T,
  options: CustomEventInit = { bubbles: true, cancelable: true },
): CustomEvent<T> {
  return new CustomEvent(name, { ...options, detail });
}

/**
 * Dispatch event on element
 * 
 * @param el - Target element
 * @param event - Event name or CustomEvent
 * @returns true if event was not cancelled
 */
export function dispatch<T = unknown>(
  el: EventTarget,
  event: string | CustomEvent<T>,
): boolean {
  if (typeof event === 'string') {
    event = createCustomEvent(event, {} as T);
  }
  return el.dispatchEvent(event);
}

/**
 * Listen to event once, then auto-remove
 * 
 * @param el - Target element
 * @param event - Event name
 * @param handler - Event handler
 * @param options - Event listener options
 */
export function once<T extends Event = Event>(
  el: EventTarget,
  event: string,
  handler: (event: T) => void,
  options: AddEventListenerOptions = {},
): void {
  const onceHandler: typeof handler = (e) => {
    handler(e);
    el.removeEventListener(event, onceHandler, options);
  };
  el.addEventListener(event, onceHandler, options);
}

/**
 * Wait for event to fire once
 * 
 * @param el - Target element
 * @param event - Event name to wait for
 * @returns Promise that resolves when event fires
 */
export function waitFor<T extends Event = Event>(
  el: EventTarget,
  event: string,
): Promise<T> {
  return new Promise((resolve) => {
    once<T>(el, event, resolve as (e: Event) => void);
  });
}