import { BaseSection } from '../BaseSection.ts';

export class NavbarSection extends BaseSection {
  init(): void {
    this.initMobileMenu();
  }

  private initMobileMenu(): void {
    const toggleBtn = this.element.querySelector('.navbar__toggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        this.element.classList.toggle('navbar--menu-open');
      });
    }
  }
}
