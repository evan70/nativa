// mark.ts — Mark Admin page entry
import './mark.css';
import './mark-drawer.css';
import { SectionLoader } from '../../core/sections/SectionLoader.ts';

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

function init() {
  SectionLoader.loadSections();
  console.log('[Mark] Page initialized');
}
