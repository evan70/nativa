/**
 * Fire Show Demo Page Entry Point
 * Imports all 12 fire-show theme features
 */

import { initFireShowTheme } from '@nativa/theme-fire-show';

// Initialize fire-show theme features when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initFireShowTheme);
} else {
  initFireShowTheme();
}

// Also load the fire-show CSS
import '../theme/fire-show/theme.css';

console.log('Fire Show Demo loaded');