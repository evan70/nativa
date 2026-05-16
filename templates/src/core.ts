// core.ts — Shared layout + components (always loaded)
// NOTE: htmx is NOT included here - it's loaded only in pages that need it (articles, dashboard, etc.)

// Tokens + reset
import './core/tokens/unified.css';
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
import './core/components/form.css';

// Shared JS components
import { initThemeSwitcher } from './dev/theme-switcher.ts';
import { SectionLoader } from './core/sections/SectionLoader.ts';

// Deferred components (loaded after page is ready)
// These don't block initial render
let deferredInited = false;

function initDeferred() {
    if (deferredInited) return;
    deferredInited = true;
    
    // Lazy load CookieConsent only after user scrolls
    // This reduces initial JS payload significantly
    const loadCookieConsent = async () => {
        const { CookieConsent } = await import('./core/components/CookieConsent.ts');
        CookieConsent.init();
        // NOTE: htmx indicator styles are now in page-specific bundles (articles, dash, auth)
    };
    
    // Trigger on scroll (only once)
    let triggered = false;
    const onScroll = () => {
        if (triggered) return;
        triggered = true;
        window.removeEventListener('scroll', onScroll, { passive: true });
        loadCookieConsent();
    };
    window.addEventListener('scroll', onScroll, { passive: true });
}

console.log('Core initialized');

// Init theme and sections immediately
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

function init() {
  SectionLoader.loadSections();
  initThemeSwitcher();
  
  // Load deferred components after page is interactive
  initDeferred();
}
