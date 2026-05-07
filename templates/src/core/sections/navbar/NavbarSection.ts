import { BaseSection } from '../BaseSection.ts';

export class NavbarSection extends BaseSection {
  init(): void {
    this.initMobileMenu();
  }

  private initMobileMenu(): void {
    const toggleBtn = this.element.querySelector('.navbar__toggle');
    if (!toggleBtn) return;

    // Use touchstart + click for Android Chrome (300ms click delay fix)
    // touchstart fires immediately on tap, click provides desktop fallback
    const handler = (e: Event) => {
      e.preventDefault();
      this.element.classList.toggle('navbar--menu-open');
    };

    toggleBtn.addEventListener('touchstart', handler, { passive: false });
    toggleBtn.addEventListener('click', handler);
  }
}
