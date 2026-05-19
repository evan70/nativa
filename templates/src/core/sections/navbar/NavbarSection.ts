import { BaseSection } from '../BaseSection.ts';

export class NavbarSection extends BaseSection {
  init(): void {
    this.initMobileMenu();
    this.initThemeToggle();
  }

  private initMobileMenu(): void {
    const toggleBtn = this.element.querySelector('.navbar__toggle');
    if (!toggleBtn) return;

    // If a mark drawer exists on the page, the drawer handles the toggle
    const hasDrawer = document.querySelector('.mark-drawer') !== null;
    if (hasDrawer) return;

    toggleBtn.addEventListener('click', () => {
      this.element.classList.toggle('navbar--menu-open');
    });
  }

  private initThemeToggle(): void {
    const themeBtn = this.element.querySelector('.navbar__theme-toggle');
    if (!themeBtn) return;

    const htmlEl = document.documentElement;

    themeBtn.addEventListener('click', () => {
      const isLight = htmlEl.dataset.theme === 'light';
      const newTheme = isLight ? 'dark' : 'light';

      htmlEl.dataset.theme = newTheme;
      try { localStorage.setItem('nativa-theme', newTheme); } catch {}
    });
  }
}
