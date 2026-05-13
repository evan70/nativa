/**
 * DOM utility functions
 */

import type { EventHandler } from './types.ts';

/**
 * Query a single element within a context
 */
export function query<T extends HTMLElement = HTMLElement>(
  selector: string,
  context: ParentNode = document,
): T | null {
  return context.querySelector(selector) as T | null;
}

/**
 * Query all elements within a context
 */
export function queryAll<T extends HTMLElement = HTMLElement>(
  selector: string,
  context: ParentNode = document,
): T[] {
  return Array.from(context.querySelectorAll(selector)) as T[];
}

/**
 * Create an element with attributes and children
 */
export function createElement<K extends keyof HTMLElementTagNameMap>(
  tag: K,
  attrs: Record<string, string> = {},
  children: (Node | string)[] = [],
): HTMLElementTagNameMap[K] {
  const el = document.createElement(tag);
  
  Object.entries(attrs).forEach(([key, value]) => {
    if (key === 'class') {
      el.className = value;
    } else if (key.startsWith('data-')) {
      el.setAttribute(key, value);
    } else {
      el.setAttribute(key, value);
    }
  });

  children.forEach(child => {
    if (typeof child === 'string') {
      el.appendChild(document.createTextNode(child));
    } else {
      el.appendChild(child);
    }
  });

  return el;
}

/**
 * Add event listener with automatic cleanup
 */
export function addEventListener(
  el: EventTarget,
  event: string,
  handler: EventHandler,
  options: AddEventListenerOptions = {},
): () => void {
  el.addEventListener(event, handler, options);
  return () => el.removeEventListener(event, handler, options);
}

/**
 * Delegate event handling to parent element
 */
export function delegate(
  parent: EventTarget,
  selector: string,
  event: string,
  handler: (el: HTMLElement, event: Event) => void,
  options: AddEventListenerOptions = {},
): () => void {
  const delegatedHandler = (event: Event) => {
    const target = (event.target as HTMLElement).closest(selector);
    if (target) {
      handler(target as HTMLElement, event);
    }
  };

  parent.addEventListener(event, delegatedHandler, options);
  return () => parent.removeEventListener(event, delegatedHandler, options);
}

/**
 * Toggle class on element
 */
export function toggleClass(el: HTMLElement, className: string, force?: boolean): boolean {
  return el.classList.toggle(className, force);
}

/**
 * Add class(es) to element
 */
export function addClass(el: HTMLElement, ...classes: string[]): void {
  el.classList.add(...classes);
}

/**
 * Remove class(es) from element
 */
export function removeClass(el: HTMLElement, ...classes: string[]): void {
  el.classList.remove(...classes);
}

/**
 * Check if element has class
 */
export function hasClass(el: HTMLElement, className: string): boolean {
  return el.classList.contains(className);
}

/**
 * Show element (remove hidden/display styles)
 */
export function show(el: HTMLElement): void {
  el.style.removeProperty('display');
  if (el.hasAttribute('data-hidden-by')) {
    el.removeAttribute('data-hidden-by');
  }
}

/**
 * Hide element
 */
export function hide(el: HTMLElement): void {
  el.style.display = 'none';
}

/**
 * Set data attribute
 */
export function setData(el: HTMLElement, key: string, value: string): void {
  el.dataset[key] = value;
}

/**
 * Get data attribute
 */
export function getData(el: HTMLElement, key: string): string | undefined {
  return el.dataset[key];
}

/**
 * Remove element from DOM
 */
export function remove(el: HTMLElement): void {
  el.remove();
}

/**
 * Insert element after another
 */
export function insertAfter(newEl: HTMLElement, refEl: HTMLElement): void {
  refEl.parentNode?.insertBefore(newEl, refEl.nextSibling);
}

/**
 * Insert element before another
 */
export function insertBefore(newEl: HTMLElement, refEl: HTMLElement): void {
  refEl.parentNode?.insertBefore(newEl, refEl);
}