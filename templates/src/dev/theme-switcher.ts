import neonCssUrl from '../theme/neon/theme.css?url';
import fireShowCssUrl from '../theme/fire-show/theme.css?url';

export function initThemeSwitcher() {
  const neonSwitcher = document.getElementById('dev-theme-switcher');
  const fireSwitcher = document.getElementById('fire-theme-switcher');

  const removeTheme = (id: string) => {
    const existing = document.getElementById(id);
    if (existing) {
      existing.remove();
    }
  };

  const applyTheme = (id: string, url: string) => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = url;
    link.id = id;
    document.head.appendChild(link);
  };

  if (neonSwitcher) {
    let isNeon = false;
    neonSwitcher.addEventListener('click', () => {
      isNeon = !isNeon;
      if (isNeon) {
        if (fireSwitcher) {
          fireSwitcher.classList.remove('navbar__dev-toggle--active');
          removeTheme('fire-show-theme-stylesheet');
        }
        applyTheme('neon-theme-stylesheet', neonCssUrl);
        neonSwitcher.classList.add('navbar__dev-toggle--active');
      } else {
        removeTheme('neon-theme-stylesheet');
        neonSwitcher.classList.remove('navbar__dev-toggle--active');
      }
    });
  }

  if (fireSwitcher) {
    let isFire = false;
    fireSwitcher.addEventListener('click', () => {
      isFire = !isFire;
      if (isFire) {
        if (neonSwitcher) {
          neonSwitcher.classList.remove('navbar__dev-toggle--active');
          removeTheme('neon-theme-stylesheet');
        }
        applyTheme('fire-show-theme-stylesheet', fireShowCssUrl);
        fireSwitcher.classList.add('navbar__dev-toggle--active');
      } else {
        removeTheme('fire-show-theme-stylesheet');
        fireSwitcher.classList.remove('navbar__dev-toggle--active');
      }
    });
  }
}
