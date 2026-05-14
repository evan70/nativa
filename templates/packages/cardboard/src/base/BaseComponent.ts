/**
 * BaseComponent - Base class for all cardboard components
 * 
 * Extends BaseSection pattern from vanilla-cards core.
 * Provides common functionality for all components.
 */

import { BaseSection } from '../../../../src/core/sections/BaseSection.ts';

export interface ComponentOptions {
  name: string;
  onInit?: () => void;
  onDestroy?: () => void;
}

/**
 * Base component class for cardboard components.
 * Extends BaseSection to follow vanilla-cards architecture.
 */
export abstract class BaseComponent extends BaseSection {
  protected readonly name: string;
  protected readonly options: ComponentOptions;
  private isInitialized = false;

  constructor(element: HTMLElement, options: ComponentOptions) {
    super(element);
    this.name = options.name;
    this.options = options;
  }

  /**
   * Initialize the component.
   * Called by SectionLoader or manually.
   */
  init(): void {
    if (this.isInitialized) {
      console.warn(`[${this.name}] Already initialized`);
      return;
    }

    this.isInitialized = true;
    this.onInit();
    this.options.onInit?.();
  }

  /**
   * Clean up the component.
   * Call when removing the component from DOM.
   */
  destroy(): void {
    if (!this.isInitialized) {
      return;
    }

    this.isInitialized = false;
    this.onDestroy();
    this.options.onDestroy?.();
  }

  /**
   * Check if component is initialized
   */
  isActive(): boolean {
    return this.isInitialized;
  }

  /**
   * Get component name
   */
  getName(): string {
    return this.name;
  }

  /**
   * Hook for component-specific initialization.
   * Override in subclass.
   */
  protected abstract onInit(): void;

  /**
   * Hook for component-specific cleanup.
   * Override in subclass.
   */
  protected abstract onDestroy(): void;

  /**
   * Log debug message with component name prefix
   */
  protected log(message: string, level: 'info' | 'warn' | 'error' = 'info'): void {
    const prefix = `[${this.name}]`;
    switch (level) {
      case 'warn':
        console.warn(prefix, message);
        break;
      case 'error':
        console.error(prefix, message);
        break;
      default:
        console.info(prefix, message);
    }
  }
}