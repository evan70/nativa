// core.ts — Shared layout + components (always loaded)

// Tokens + reset
import './core/tokens/unified.css';
import './core/components/table.css';
import './core/components/page-header.css';

// Theme (brand colors + theme-specific overrides)
import './core/theme/default/theme.css';

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
import { initThemeSwitcher } from './dev/theme-switcher.ts';
import { SectionLoader } from './core/sections/SectionLoader.ts';
import { NotificationManager } from './core/components/NotificationManager.ts';
import { CookieConsent } from './core/components/CookieConsent.ts';

console.log('Core initialized');

// Expose to window for legacy support
(window as any).NotificationManager = NotificationManager;

// Init when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

function init() {
  SectionLoader.loadSections();
  CookieConsent.init();
  initThemeSwitcher();
}
