# Refactor: Unified Vanilla-Cards for All Pages

**Problem:** Auth, dash, and home pages use inconsistent naming. Page-specific CSS uses custom class names (`sidebar__*`, `admin-layout__*`, `admin-table`, `stat-card`, `dashboard-header`, `alert--*`) instead of generic vanilla-card tokens. Theme system in `src/theme/` is empty stub.

**Goal:** All pages (home, auth, dash) use the same vanilla-card generic naming from `core/tokens/`, `core/components/`, and `core/sections/`. Page-specific files only add modifiers or thin wrappers. Theme switching via `src/theme/<name>/` works out of the box.

**Created:** 2026-05-07

## Settings
- **Testing:** no
- **Logging:** standard
- **Docs:** no

---

## Phase 1: Audit & Map Current Naming ✅

Map all page-specific class names to vanilla-card equivalents.

### Auth page (`auth.css`, `login.php`)
| Current | Target | Action |
|---------|--------|--------|
| `.auth-page` | `.page-auth` | Rename to generic page prefix |
| `.auth-container` | `.container .container--narrow` | Use layout-grid token |
| `.alert--error` / `.alert--success` | `.notification--error` / `.notification--success` | Use core notification component |
| `.form-group`, `.form-label`, `.form-input` | Keep (page-specific form layout is OK) | No change — forms are not cards |
| `.link` | Keep (generic utility) | No change |
| `.checkbox` | Keep (generic utility) | No change |

### Dash page (`dash.css`, `dashboard/index.php`, `sidebar.php`, `navbar.php`)
| Current | Target | Action |
|---------|--------|--------|
| `.admin-layout` | `.layout-admin` | Generic layout prefix |
| `.admin-layout__sidebar` | `.layout-admin__sidebar` | Match layout prefix |
| `.admin-layout__navbar` | `.layout-admin__navbar` | Match layout prefix |
| `.admin-layout__main` | `.layout-admin__main` | Match layout prefix |
| `.sidebar__*` | Keep inside `.layout-admin__sidebar` | Sidebar is a valid sub-component of admin layout |
| `.admin-table` | `.table` | Generic table in core/components |
| `.chart-container` | `.card__body .chart` | Chart lives inside card |
| `.dashboard-header` | `.page-header` | Generic page header token |
| `.stat-card` | `.card--stat` | Card modifier |
| `.flash-message--*` | `.notification--*` | Use core notification component |

### Layout templates
| Current | Target | Action |
|---------|--------|--------|
| `cardboard/layout/base.php` body class `admin-layout` | `layout-admin` | Match CSS rename |
| `cardboard/auth/base.php` body class `auth-page` | `page-auth` | Match CSS rename |
| `app/layouts/app.php` body class `front-page` | `page-home` | Match CSS rename |

---

## Phase 2: Core Tokens & Components ✅

### Task 2.1: Add missing generic tokens to core ✅

**Files:**
- `templates/src/core/tokens/unified.css` — add page-level tokens
- `templates/src/core/components/table.css` — new, generic table component
- `templates/src/core/components/page-header.css` — new, generic page header

**What:**
1. In `unified.css` add:
   - `.page-auth`, `.page-home`, `.page-dash` — page body classes (minimal, just for scoping)
   - `.layout-admin` — admin grid layout (move from `dash.css`)
   - `.layout-admin__sidebar`, `.layout-admin__navbar`, `.layout-admin__main`
   - `.container--narrow` — narrow container modifier

2. Create `core/components/table.css`:
   - `.table` — generic table (move from `dash.css` `.admin-table`)
   - `.table th`, `.table td`

3. Create `core/components/page-header.css`:
   - `.page-header` — generic page header (move from `dashboard-header`)
   - `.page-header__title`, `.page-header__subtitle`

4. Update `core.ts` to import new files.

### Task 2.2: Add card modifiers to vanilla-card ✅

**Files:**
- `templates/src/core/components/vanilla-card.css`

**What:**
Add card modifiers used by dash:
- `.card--stat` — stat card variant (compact, centered)
- `.card--chart` — chart card variant (no padding on body)

---

## Phase 3: Auth Page Unification ✅

### Task 3.1: Rename auth CSS to generic naming ✅

**Files:**
- `templates/src/pages/auth/auth.css`

**What:**
1. Rename `.auth-page` → `.page-auth`
2. Rename `.auth-container` → `.container .container--narrow`
3. Rename `.alert--error` / `.alert--success` → `.notification--error` / `.notification--success`
4. Remove auth-specific alert styles — rely on core `notification.css`
5. Keep form styles (`.form-group`, `.form-label`, `.form-input`, `.checkbox`, `.link`) — these are page-specific

### Task 3.2: Update auth template ✅

**Files:**
- `templates/cardboard/auth/login.php`

**What:**
1. Replace `<div class="auth-container">` → `<div class="container container--narrow">`
2. Replace alert divs to use `notification--error` / `notification--success` classes
3. Card markup already uses `.card` — no change needed

### Task 3.3: Update auth layout ✅

**Files:**
- `templates/cardboard/auth/base.php`

**What:**
1. Change `<body class="auth-page">` → `<body class="page-auth">`
2. Fix asset ordering: move `View::viteJs('init')` before CSS (match other layouts — current order causes FOUC)

---

## Phase 4: Dash Page Unification ✅

### Task 4.1: Rename dash CSS to generic naming ✅

**Files:**
- `templates/src/pages/dash/dash.css`

**What:**
1. Move `.admin-layout` grid styles → `core/tokens/unified.css` as `.layout-admin`
2. Move `.admin-table` → `core/components/table.css` as `.table`
3. Move `.dashboard-header` → `core/components/page-header.css` as `.page-header`
4. In `dash.css` keep only:
   - Sidebar sub-component styles (`.sidebar__*` inside `.layout-admin__sidebar`)
   - Chart container (move to `.card--chart` modifier)
   - Flash message overrides (remove — use core notification)
5. Remove `.stat-card` — replace with `.card--stat` modifier in `vanilla-card.css`

### Task 4.2: Update dashboard template ✅

**Files:**
- `templates/cardboard/dashboard/index.php`

**What:**
1. Replace `class="stat-card"` → `class="card--stat"` on stat cards
2. Replace `class="dashboard-header"` → `class="page-header"`
3. Replace `class="chart-container"` → remove (use `.card--chart` on parent card)
4. Remove inline `style` attributes from flash messages — use core notification classes
5. Replace `class="admin-table"` → `class="table"`

### Task 4.3: Update dash layout ✅

**Files:**
- `templates/cardboard/layout/base.php`

**What:**
1. Change `<body class="admin-layout">` → `<body class="layout-admin">`
2. Update grid area classes to match `.layout-admin__*`

### Task 4.4: Update dash partials ✅

**Files:**
- `templates/cardboard/partials/sidebar.php`
- `templates/cardboard/partials/navbar.php`
- `templates/cardboard/partials/flash.php`

**What:**
1. Sidebar: keep `.sidebar__*` but ensure parent is `.layout-admin__sidebar`
2. Navbar: keep `.navbar__*` — it's already generic core component
3. Flash: remove inline `style` overrides, use `.notification--*` classes from core

---

## Phase 5: Home Page Alignment ✅

### Task 5.1: Rename home body class ✅

**Files:**
- `templates/app/layouts/app.php`

**What:**
1. Change `<body class="front-page">` → `<body class="page-home">`
2. Update `PageLayout::bodyClass()` return value for home → `'page-home'`

---

## Phase 6: Theme System ✅

### Task 6.1: Populate default theme ✅

**Files:**
- `templates/src/core/theme/default/theme.css`

**What:**
Move theme-specific values from `colors.css` into `theme.css`:
1. Brand colors (`--brand-gold`, `--brand-emerald`, etc.) → `theme.css`
2. Light theme overrides → `theme.css`
3. Keep only structural tokens in `colors.css` (shadows, focus ring)

### Task 6.2: Create theme loader ✅

**Files:**
- `templates/src/core.ts`

**What:**
1. Import `theme.css` in `core.ts`
2. Add theme switcher support: read `data-theme` attribute, load corresponding theme from `src/theme/<name>/theme.css`
3. Future: `src/theme/ocean/theme.css`, `src/theme/forest/theme.css` etc.

---

## Phase 7: Cleanup ✅

### Task 7.1: Remove dead CSS ✅

**Files:**
- `templates/src/pages/auth/auth.css`
- `templates/src/pages/dash/dash.css`

**What:**
After migration, remove any empty or near-empty CSS files. If a page CSS has < 10 lines of actual page-specific styles, consider inlining or removing.

### Task 7.2: Verify no visual regression ✅

**What:**
1. Build: `cd templates && npx vite build`
2. Check all 3 pages: home, auth, dash
3. Verify theme switching works (dark ↔ light)
4. Verify card styles are consistent across all pages

---

## Commit Plan

- **After Phase 2:** `feat(core): add generic layout, table, page-header tokens`
- **After Phase 3:** `refactor(auth): use unified vanilla-card naming`
- **After Phase 4:** `refactor(dash): use unified vanilla-card naming`
- **After Phase 5:** `refactor(home): align body class with page-* convention`
- **After Phase 6:** `feat(theme): populate default theme, add theme loader`
- **After Phase 7:** `chore: remove dead CSS, verify no visual regression`
