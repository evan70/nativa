import { BaseSection } from '../BaseSection.ts';

export class NavbarSection extends BaseSection {
  init(): void {
    this.initMobileMenu();
    this.initThemeToggle();
  }

  private initMobileMenu(): void {
    const toggleBtn = this.element.querySelector('.navbar__toggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        this.element.classList.toggle('navbar--menu-open');
      });
    }
  }

  private initThemeToggle(): void {
    const themeBtn = this.element.querySelector('.navbar__theme-toggle');
    if (!themeBtn) return;

    const htmlEl = document.documentElement;

    themeBtn.addEventListener('click', () => {
      const isLight = htmlEl.dataset.theme === 'light';
      const newTheme = isLight ? 'dark' : 'light';
      
      htmlEl.dataset.theme = newTheme;
      localStorage.setItem('nativa-theme', newTheme);
    });
  }
}
