import { BaseSection } from '../BaseSection.ts';

/**
 * MarkDrawerSection — manages the right-side sliding drawer for Mark admin.
 *
 * Behaviour:
 * - Toggle button (.mark-drawer__toggle in navbar) opens/closes drawer
 * - Overlay click closes drawer
 * - ESC key closes drawer
 * - On desktop: drawer slides from right as floating panel
 * - On mobile: full-width overlay
 * - URL changes: if drawer is open and a link is clicked, drawer closes
 */
export class MarkDrawerSection extends BaseSection {
  private panel: HTMLElement | null = null;
  private overlay: HTMLElement | null = null;
  private toggleBtn: HTMLElement | null = null;
  private closeBtns: NodeListOf<HTMLElement>;
  private links: NodeListOf<HTMLAnchorElement>;

  init(): void {
    this.panel = this.element.querySelector('.mark-drawer__panel');
    this.overlay = this.element.querySelector('.mark-drawer__overlay');
    this.closeBtns = this.element.querySelectorAll('[data-drawer-close]');
    this.links = this.element.querySelectorAll('.mark-drawer__link');

    if (!this.panel || !this.overlay) {
      console.warn('[MarkDrawer] Panel or overlay not found');
      return;
    }

    // Find toggle button in navbar
    this.toggleBtn = document.querySelector('.mark-drawer__toggle');
    if (!this.toggleBtn) {
      // Maybe there's a navbar__toggle we should listen to instead
      this.toggleBtn = document.querySelector('.navbar__toggle');
    }

    this.bindEvents();
    console.log('[MarkDrawer] Initialized');
  }

  private bindEvents(): void {
    // Toggle button
    if (this.toggleBtn) {
      this.toggleBtn.addEventListener('click', (e: Event) => {
        e.stopPropagation();
        this.toggle();
      });
    }

    // Close buttons (overlay + close button)
    this.closeBtns.forEach((btn) => {
      btn.addEventListener('click', () => this.close());
    });

    // ESC key
    document.addEventListener('keydown', (e: KeyboardEvent) => {
      if (e.key === 'Escape' && this.isOpen()) {
        this.close();
      }
    });

    // Links — close drawer on navigation
    this.links.forEach((link) => {
      link.addEventListener('click', () => {
        // Small delay to let navigation start
        setTimeout(() => this.close(), 100);
      });
    });
  }

  private toggle(): void {
    if (this.isOpen()) {
      this.close();
    } else {
      this.open();
    }
  }

  private open(): void {
    this.element.classList.add('mark-drawer--open');
    document.body.classList.add('mark-drawer-visible');
    // Prevent body scroll while drawer is open
    document.body.style.overflow = 'hidden';

    console.log('[MarkDrawer] Opened');
  }

  private close(): void {
    this.element.classList.remove('mark-drawer--open');
    document.body.classList.remove('mark-drawer-visible');
    document.body.style.overflow = '';

    console.log('[MarkDrawer] Closed');
  }

  private isOpen(): boolean {
    return this.element.classList.contains('mark-drawer--open');
  }
}
