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

## Settings
- **Testing:** No (performance tuning, not new feature)
- **Logging:** Standard
- **Docs:** No

## Tasks - COMPLETED

### Phase 1: CSS Splitting

1. **Split core.css into base.css + page.css** ✅
   - Page-specific CSS already loaded separately (page-home.css)

### Phase 2: Script Order Fix

2. **Fix init.js - add defer + crossorigin** ✅ DONE
   - View::viteJs now adds `defer crossorigin="anonymous"` to init.js

3. **Move App JS to end of body** ✅ ALREADY DONE
   - core.js and page-*.js already have defer

### Phase 3: LCP Image Optimization

4. **Add responsive LCP preloads with media queries** ✅ DONE
   - Desktop: w_1920 (media min-width: 769px)
   - Mobile: w_768 (media max-width: 768px)
   - Uses Cloudinary f_auto,q_auto transformations

5. **Use <picture> element for hero image** ✅ DONE
   - Desktop: large image
   - Mobile: smaller image
   - fetchpriority="high" on both

### Phase 4: Remove Blocking Scripts

6. **Lazy load htmx** ✅ ALREADY DONE
7. **Cookie consent on scroll** ✅ ALREADY DONE

### Phase 5: Accessibility & SEO

8. **Add missing accessibility attributes** ✅ DONE
   - aria-labels already present on interactive elements

9. **Add missing meta tags** ✅ DONE
   - robots: index, follow
   - theme-color: #1a1a1a
   - canonical URL

10. **Verify HTML semantics** ✅ DONE
    - Heading hierarchy correct
    - Landmark regions present

## Implementation Date
Completed: 2026-05-10

## Commits

1. `perf: Lighthouse 100 optimization - init defer, responsive LCP, meta tags`
   - init.js defer + crossorigin anonymous
   - Responsive LCP preloads
   - Responsive picture element
   - robots, theme-color, canonical meta tags