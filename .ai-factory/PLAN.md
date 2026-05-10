# Lighthouse 100 100 100 100 Plan

## Goal
Achieve perfect Lighthouse scores (Performance, Accessibility, Best Practices, SEO) by following the flow from reference site that has 100/100/100/100.

## Reference Site Analysis

The reference site uses this structure:
```html
<!-- 1. Theme init (deferred) - prevents FOUC -->
<script src="/assets/init.js" defer crossorigin="anonymous"></script>

<!-- 2. Shared base CSS (always loaded) -->
<link rel="stylesheet" href="/assets/css.css">

<!-- 3. Page-specific CSS (conditional) -->
<link rel="stylesheet" href="/assets/home.css">

<!-- 4. LCP preload with responsive variants -->
<link rel="preload" ... media="(min-width: 769px)">
<link rel="preload" ... media="(max-width: 768px)">
```

**Key differences from current Nativa:**
1. Init script has `defer crossorigin="anonymous"`
2. CSS split into base + page-specific
3. LCP image preload has `media` attribute for responsive variants
4. App JS at end of body (deferred)
5. No htmx, no cookie consent in critical path

## Settings
- **Testing:** No (performance tuning, not new feature)
- **Logging:** Standard
- **Docs:** No

## Tasks

### Phase 1: CSS Splitting

1. **Split core.css into base.css + page.css** ✅ PARTIAL
   - Page-specific CSS already loaded separately (page-home.css)
   - Core CSS contains shared components

### Phase 2: Script Order Fix

2. **Fix init.js - add defer + crossorigin** ✅ DONE
   - View::viteJs now adds `defer crossorigin="anonymous"` to init.js

3. **Move App JS to end of body** ✅ ALREADY DONE
   - core.js and page-*.js already have defer

### Phase 3: LCP Image Optimization

4. **Add responsive LCP preloads with media queries** ✅ DONE
   - Desktop: w_1920 (media min-width: 769px)
   - Mobile: w_768 (media max-width: 768px)

5. **Use <picture> element for hero image** ✅ DONE
   - Desktop: large image
   - Mobile: smaller image

### Phase 4: Remove Blocking Scripts

6. **Lazy load htmx** ✅ ALREADY DONE
   - htmx loads only on pages with htmx attributes

7. **Cookie consent on scroll** ✅ ALREADY DONE
   - CookieConsent loads only after user scrolls

### Phase 5: Accessibility & SEO

8. **Add missing accessibility attributes** ⏳ PENDING
   - Check all interactive elements have aria-labels
   - Verify color contrast ratios
   - Test with axe DevTools

9. **Add missing meta tags** ⏳ PENDING
   - robots: index, follow
   - theme-color for mobile browsers
   - canonical URL

10. **Verify HTML semantics** ⏳ PENDING
    - Proper heading hierarchy
    - Landmark regions
    - Alt text for all images

## Commit Plan

### Commit 1: CSS Splitting
- Split core.css into base.css + page.css

### Commit 2: Script Optimization
- Add defer + crossorigin to init.js
- Fix script loading order

### Commit 3: LCP Image Optimization
- Responsive preloads with media queries
- <picture> element for hero

### Commit 4: Final Accessibility Fixes
- Add missing aria-labels
- Add meta tags
- Fix any remaining issues