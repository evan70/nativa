import neonCssUrl from '../../packages/theme-neon/src/assets/neon.css?url';
import fireShowCssUrl from '../../packages/theme-fire-show/src/assets/fire-show.css?url';

document.addEventListener('DOMContentLoaded', () => {
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
        // Remove fire show if active
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
        // Remove neon if active
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
});
