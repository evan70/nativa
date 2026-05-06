// Critical CSS entry — bundled by Vite into a single CSS file
// Inlined in <head> for first paint (no FOUC)

import './reset.css';
import './layout-grid.css';
import './fonts.css';
import './colors.css';

// Inline critical styles that can't be in imported files
import './critical-inline.css';
