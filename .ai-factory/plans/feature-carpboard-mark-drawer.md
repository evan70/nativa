# Plan: Cardboard Mark Admin — Drawer Menu + Homepage-based Layout

> Branch: `feature/cardboard-mark-drawer`
> Creation date: 2026-05-19

## Goal

Rebuild the Cardboard admin section from scratch:
- **Remove** current cardboard templates (`templates/pages/dash/`)
- **Create** new mark admin templates based on the homepage pattern (`layouts.app` style hero + sections)
- **Menu** = sliding drawer from the **right** side (not old left sidebar, not navbar inline menu)
- **Content** = mark articles management, tags management, statistics, analytics, quick actions
- **Naming** = `-mark-` prefix, never `-admin-`
- **Reuse** existing vanilla-cards BEM tokens/elements (DRY — card, btn, form, table, page-header, navbar)
- **Preserve** PHP module code (controllers, config, menu builders stay)

## Settings

- Testing: Yes — PHPUnit tests for new layout and drawer
- Logging: Verbose — detailed DEBUG logs for all operations
- Docs: Yes — update AGENTS.md

---

## Tasks

### Phase 1: Cleanup + Template Migration

#### 1.1 Remove old cardboard templates
- Delete `templates/pages/dash/` directory entirely (old admin templates)
- Delete `templates/partials/sidebar.php` (no longer included anywhere)
- Delete `templates/src/pages/dash/dash.css` (will be replaced)
- Remove `templates/src/core/tokens/unified.css` — `.layout-admin` grid (content area padding stays but simplified)
- Keep `modules/cardboard/` PHP code untouched (controllers, config, menu builders)

**Files to delete:**
- `templates/pages/dash/` (entire directory)
- `templates/partials/sidebar.php`
- `templates/src/pages/dash/dash.css`
- `templates/src/pages/dash/dash.ts`

#### 1.2 Create new templates directory structure
```
templates/pages/mark/
  ├── dashboard.php        # Main dashboard (sections: stats, recent, quick actions)
  ├── articles.php         # Article list management
  ├── article-form.php     # Article create/edit form
  ├── tags.php             # Tag list management
  ├── tag-form.php         # Tag create/edit form
  └── settings.php         # Settings page (placeholder)
```

#### 1.3 Create new mark layout (`layouts.mark`)
- Inspired by `layouts.app` homepage pattern
- **Navbar** at top (reuse existing `navbar.php` partial with admin menu items)
- **Right drawer** — sliding panel from right for section navigation
- **Main content** — sections like homepage (hero card, stat cards, card grids)
- Content padding: `var(--space-6)` like current admin
- Template path: `templates/layouts/mark.php`

---

### Phase 2: Right-Side Drawer Menu

#### 2.1 Create drawer HTML partial (`templates/partials/mark-drawer.php`)
- Sliding panel anchored to right edge
- Contains section links: Dashboard, Articles, Tags, Statistics, Analytics, Quick Actions, Settings
- Icons for each section using existing SVG patterns
- Active state highlighting
- Close button (X) for mobile
- Overlay/backdrop for mobile

#### 2.2 Create drawer CSS (`templates/src/pages/mark/mark-drawer.css`)
BEM classes:
- `mark-drawer` — the panel itself, fixed position, right: -100%, transition
- `mark-drawer--open` — slides in (right: 0)
- `mark-drawer__overlay` — backdrop, opaque on mobile
- `mark-drawer__header` — brand + close btn
- `mark-drawer__nav` — list of section links
- `mark-drawer__item` — individual link
- `mark-drawer__item--active` — active state
- `mark-drawer__toggle` — the hamburger/meatball button that opens it
- `mark-drawer__icon` — SVG icon per section

**Visual design:**
- Dark/translucent panel (`var(--color-bg)`, maybe with backdrop-filter)
- Smooth slide transition (`transition: transform var(--transition-slow)`)
- On desktop (>1024px): drawer is wider (320px), partially visible as a peek tab
- On mobile: full-width overlay
- Overlay closes on click outside

**Drawer toggle button:**
- Placed in navbar `navbar__actions` (like the existing hamburger)
- Uses existing `.navbar__toggle` + `.navbar--menu-open` pattern from NavbarSection.ts
- BUT on desktop, instead of showing a dropdown menu, it opens the right drawer
- This means we need to handle both behaviors:
  - On mobile: toggle shows/hides the dropdown menu (existing behavior)
  - On desktop (mark admin only): toggle opens the right drawer

#### 2.3 Create drawer JS (`templates/src/core/sections/mark-drawer/MarkDrawerSection.ts`)
- Extends `BaseSection` like other sections
- Toggle button opens/closes drawer
- Overlay click closes drawer
- ESC key closes drawer
- On desktop, drawer appears as overlay from right
- Active section detection from URL

---

### Phase 3: Create Mark Dashboard Template

#### 3.1 Dashboard template (`templates/pages/mark/dashboard.php`)
- Uses `layouts.mark` layout
- **Hero-like header** with greeting + stats snapshot (like homepage hero but compact)
  - `mark-hero` section with `mark-hero__title`, `mark-hero__subtitle`
- **Stat cards** grid (card-grid--cols-4) — same as current dashboard
- **Recent articles** + **Recent users** side-by-side cards (card-grid--cols-2)
- **Quick actions** section — row of buttons (Create Article, Manage Tags, etc.)
- All using existing `.card`, `.card-grid`, `.btn`, `.page-header` BEM components

#### 3.2 Dashboard CSS (`templates/src/pages/mark/mark-dashboard.css`)
- `mark-hero` — compact hero section with gradient bg
- `mark-hero__title`, `mark-hero__subtitle`
- Reuse existing `.card`, `.card-grid`, `.btn` modifiers
- Stats-specific card styling (`card--stat` moved from old dash.css)

---

### Phase 4: Migrate Admin Templates to mark/

#### 4.1 Articles list (`templates/pages/mark/articles.php`)
- Migrate from `templates/pages/dash/admin-articles.php`
- Change `$this->layout('layouts.mark')`
- Use `.mark-` BEM classes where page-specific
- Keep existing table, filter, search functionality
- Same variables: `$articles`, `$allTags`, `$searchQuery`, `$selectedTagId`

#### 4.2 Article form (`templates/pages/mark/article-form.php`)
- Migrate from `templates/pages/dash/article-form.php`
- Change `$this->layout('layouts.mark')`
- Keep existing form layout, validation, tag selection

#### 4.3 Tags list (`templates/pages/mark/tags.php`)
- Migrate from `templates/pages/dash/admin-tags.php`
- Change `$this->layout('layouts.mark')`
- Keep existing table and actions

#### 4.4 Tag form (`templates/pages/mark/tag-form.php`)
- Created new if missing (tag-form.php may not exist in dash/)

#### 4.5 Settings page (`templates/pages/mark/settings.php`)
- New page, placeholder content

---

### Phase 5: Update Controllers & Routes

#### 5.1 Create MarkDashboardController (`modules/controllers/src/Controller/MarkDashboardController.php`)
- New controller for the mark admin dashboard
- Same logic as current `DashboardController` (stats from DB, sections list)
- Renders `pages/mark/dashboard` template
- Passes `menuItems`, `stats`, `sections`, `recentUsers`

#### 5.2 Update BlogAdminController template paths
- Change `render('pages/dash/admin-articles'` → `render('pages/mark/articles'`
- Change `render('pages/dash/article-form'` → `render('pages/mark/article-form'`

#### 5.3 Update TagAdminController template paths
- Change `render('pages/dash/admin-tags'` → `render('pages/mark/tags'`
- Change `render('pages/dash/tag-form'` → `render('pages/mark/tag-form'`

#### 5.4 Update routes in modules
- If needed, register new routes in `modules/controllers/module.php` or `modules/cardboard/module.php`

---

### Phase 6: Page-Specific CSS

#### 6.1 Create mark pages CSS (`templates/src/pages/mark/mark.css`)
- Vendor-specific BEM for mark admin
- Article admin styles (article-toolbar, article-table-wrap, article-actions, etc.)
- Article form styles (article-form, article-form-row, etc.)
- Tag admin styles (tags-toolbar, tags-actions)
- Settings page styles
- Already responsive at 767px and 480px breakpoints

#### 6.2 Create mark Vite entry (`templates/src/pages/mark/mark.ts`)
- Import sections (MarkDrawerSection, etc.)
- Export for Vite build

#### 6.3 Register mark page in PageLayout (`modules/controllers/src/PageLayout.php`)
- Add `'mark' => 'page-mark'` to bodyClass mapping
- Or similar detection for `currentPage == 'mark'`

---

### Phase 7: Version 4 Entry & Build Config

#### 7.1 Update Vite config if needed (`templates/vite.config.ts`)
- Ensure 'page-mark' entry point exists

#### 7.2 Update NavbarSection.ts
- On desktop mark admin, `navbar__toggle` should open drawer instead of mobile menu
- Add check: if in mark admin (body has `.page-mark` or similar), toggle drawer instead

---

### Phase 8: Testing

#### 8.1 Unit test for MarkDashboardController
- Instantiate controller
- Verify `index()` returns Response with expected template path
- Mock DB connections for predictable stats

#### 8.2 Unit test for mark drawer template
- Verify drawer partial renders with menu items
- Verify active section highlighting

#### 8.3 Integration test for articles admin under new template
- Test that BlogAdminController.render() uses new template path
- Test that tag form renders correctly

#### 8.4 Run full PHPUnit suite
- `php vendor/bin/phpunit` — all tests pass
- Fix regressions

---

### Phase 9: Docs

#### 9.1 Update AGENTS.md
- Add new files (mark templates, drawer, controllers)
- Update template paths

#### 9.2 Update README.md if needed
- Brief mention of new mark admin layout

---

## Commit Plan

- **Commit 1**: Remove old dash templates + cleanup dead files
- **Commit 2**: Create mark layout + right drawer menu (HTML, CSS, JS)
- **Commit 3**: Create mark dashboard template (homepage-inspired)
- **Commit 4**: Migrate articles + tags templates to mark/
- **Commit 5**: Update controllers + routes to use new template paths
- **Commit 6**: Add page CSS + Vite entry
- **Commit 7**: Tests + docs

---

## Verification

After implementation:
1. `GET /mark` renders new mark dashboard with drawer toggle
2. Right drawer opens/closes smoothly on click
3. Desktop: drawer opens as floating panel from right
4. Mobile: drawer opens as full-width overlay
5. `GET /mark/articles` renders article list with new layout
6. `GET /mark/tags` renders tag list with new layout
7. `GET /mark/articles/new` renders article form
8. All CRUD operations still work (create, edit, delete articles/tags)
9. `php vendor/bin/phpunit` — all tests pass
10. No remaining references to `pages/dash/` in the codebase
