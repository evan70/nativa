import './css.ts';
import { SectionLoader } from './core/sections/SectionLoader.ts';
import { NotificationManager } from './core/components/NotificationManager.ts';
import { CookieConsent } from './core/components/CookieConsent.ts';

// Expose to window for legacy support and easy access
(window as any).NotificationManager = NotificationManager;

document.addEventListener('DOMContentLoaded', () => {
  SectionLoader.loadSections();
  CookieConsent.init();
});
