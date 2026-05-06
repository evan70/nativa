// core.ts — Shared layout + components (always loaded)

// Tokens + reset
import './core/tokens/reset.css';
import './core/tokens/layout-grid.css';
import './core/tokens/fonts.css';
import './core/tokens/colors.css';

// Shared component styles
import './core/global/utilities.css';
import './core/components/navbar.css';
import './core/components/button.css';
import './core/components/vanilla-card.css';
import './core/components/card-grid.css';
import './core/components/icon.css';
import './core/components/icon-button.css';
import './core/components/notification.css';
import './core/components/footer.css';

// Shared JS components
import { SectionLoader } from './core/sections/SectionLoader.ts';
import { NotificationManager } from './core/components/NotificationManager.ts';
import { CookieConsent } from './core/components/CookieConsent.ts';

console.log('Core initialized');

// Expose to window for legacy support
(window as any).NotificationManager = NotificationManager;

document.addEventListener('DOMContentLoaded', () => {
  SectionLoader.loadSections();
  CookieConsent.init();
  initThemeSwitcher();
});

// Theme switcher — toggle between dark/light
function initThemeSwitcher() {
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

  const setTheme = (dark: boolean) => {
    document.documentElement.dataset.theme = dark ? 'dark' : 'light';
    localStorage.setItem('nativa-theme', dark ? 'dark' : 'light');
  };

  // Bind all theme toggle buttons
  document.addEventListener('click', (e) => {
    const btn = (e.target as HTMLElement).closest('.theme-toggle');
    if (!btn) return;
    e.preventDefault();
    const isDark = document.documentElement.dataset.theme === 'dark';
    setTheme(!isDark);
  });
}
