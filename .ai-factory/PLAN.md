# Implementation Plan: Unified Page Flow — init → core → page-specific

Branch: none (fast mode)
Created: 2026-05-06
Status: ✅ COMPLETE

## Settings
- Testing: no (CSS/JS refactor, visual verification)
- Logging: verbose
- Docs: no

## Philosophy

**Jeden systém pre všetky page typy.** Nie tri izolované sady (front/dash/auth), ale:

```
init → core → page-specific
```

- `init` — theme FOUC prevention, always first, always loaded
- `core` — shared layout system, shared components, always loaded
- `page/*` — page-specific JS + CSS, loaded only on matching pages

Layout template rozhoduje aký page-specific bundle načítať. Vite build mapuje page entry → output bundle.

## Architecture

### File Structure (target)

```
templates/src/
├── init.ts                         # Theme FOUC prevention (always first)
├── core.ts                         # Shared layout + components (always loaded)
│
├── pages/
│   ├── home/
│   │   ├── home.ts                 # Page JS entry
│   │   └── home.css                # Page CSS
│   ├── dash/
│   │   ├── dash.ts                 # Page JS entry
│   │   └── dash.css                # Page CSS
│   └── auth/
│       ├── auth.ts                 # Page JS entry
│       └── auth.css                # Page CSS
│
├── core/
│   ├── tokens/
│   │   ├── reset.css
│   │   ├── layout-grid.css
│   │   ├── fonts.css
│   │   └── colors.css
│   ├── components/                 # Shared UI components
│   │   ├── navbar.css
│   │   ├── button.css
│   │   ├── vanilla-card.css
│   │   └── ...
│   └── global/
│       └── utilities.css
│
└── cardboard/                      # Admin-specific (legacy → migrate)
    ├── styles/
    │   ├── core.css               # → migrate to pages/dash/dash.css
    │   └── dashboard.css          # → merge into pages/dash/dash.css
    └── components/
        ├── sidebar.js             # → migrate to core/components/
        ├── charts.js              # → migrate to pages/dash/dash.ts
        └── admin-table.js         # → migrate to core/components/
```

### Vite Build (target)

```ts
// Single build, page-based entries
inputs = {
  // Always loaded
  init:    resolve('src/init.ts'),
  core:    resolve('src/core.ts'),

  // Page-specific (loaded by layout template)
  'page-home':  resolve('src/pages/home/home.ts'),
  'page-dash':  resolve('src/pages/dash/dash.ts'),
  'page-auth':  resolve('src/pages/auth/auth.ts'),
}
```

### Layout Template (target)

```php
<!-- Always loaded -->
<?= View::vite('init') ?>
<?= View::vite('core') ?>

<!-- Page-specific -->
<?= View::vite('page-' . $currentPage) ?>
```

## Commit Plan

- **Commit 1** (tasks 1-2): `refactor(tokens): reset + layout-grid + fonts + colors`
- **Commit 2** (tasks 3-4): `refactor(core): single core.ts entry with shared components`
- **Commit 3** (tasks 5-7): `feat(pages): page-specific entries — home, dash, auth`
- **Commit 4** (tasks 8-9): `refactor(layout): unified layout template with page detection`
- **Commit 5** (task 10): `chore(build): single build, verify all pages`

## Tasks

---

### Phase 1: Token Foundation (shared, always loaded)

#### Task 1: Create token files

Create 4 focused token files (replacing monolithic `unified.css`):

**`core/tokens/reset.css`**
```css
*, *::before, *::after { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
* { margin: 0; padding: 0; }
html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }
body { margin: 0; min-height: 100vh; line-height: 1.5; -webkit-font-smoothing: antialiased; }
img, picture, video, canvas, svg { display: block; max-width: 100%; }
input, button, textarea, select { font: inherit; color: inherit; }
a { color: inherit; text-decoration: none; }
ul, ol { list-style: none; }
button { cursor: pointer; background: none; border: none; }
:focus-visible { outline: none; box-shadow: var(--focus-ring); }
:focus:not(:focus-visible) { box-shadow: none; }
```

**`core/tokens/layout-grid.css`**
```css
:root {
  /* Grid */
  --grid-cols: 12;
  --grid-gap: var(--space-6);
  --grid-gap-sm: var(--space-4);
  --grid-gap-lg: var(--space-8);

  /* Content */
  --content-max-width: 1200px;
  --content-narrow-width: 720px;

  /* Page spacing */
  --section-padding-y: var(--space-12);
  --section-padding-y-sm: var(--space-8);
  --section-padding-x: var(--space-4);

  /* Components */
  --card-gap: var(--space-6);
  --card-padding: var(--space-6);
  --card-radius: var(--radius-lg);

  /* Dashboard */
  --dash-sidebar-width: 260px;
  --dash-sidebar-collapsed: 64px;
  --dash-header-height: 60px;

  /* Auth */
  --auth-max-width: 420px;

  /* Spacing scale */
  --space-0: 0; --space-1: 0.25rem; --space-2: 0.5rem;
  --space-3: 0.75rem; --space-4: 1rem; --space-5: 1.25rem;
  --space-6: 1.5rem; --space-8: 2rem; --space-10: 2.5rem;
  --space-12: 3rem; --space-16: 4rem;

  /* Radius */
  --radius-sm: 0.25rem; --radius-md: 0.5rem; --radius-lg: 0.75rem;
  --radius-xl: 1rem; --radius-2xl: 1.5rem; --radius-full: 9999px;

  /* Transitions */
  --transition-fast: 150ms ease;
  --transition-base: 250ms ease;
  --transition-slow: 400ms ease;
  --transition-transform: 300ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

**`core/tokens/fonts.css`**
```css
:root {
  --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
  --font-serif: 'Playfair Display', Georgia, serif;
  --font-mono: 'JetBrains Mono', monospace;

  --font-normal: 400; --font-medium: 500; --font-semibold: 600; --font-bold: 700;

  --text-xs: 0.75rem; --text-sm: 0.875rem; --text-base: 1rem;
  --text-lg: 1.125rem; --text-xl: 1.25rem; --text-2xl: 1.5rem;
  --text-3xl: 1.875rem; --text-4xl: 2.25rem; --text-5xl: 3rem;

  --leading-tight: 1.25; --leading-normal: 1.5; --leading-relaxed: 1.75;
  --tracking-tight: -0.025em; --tracking-normal: 0; --tracking-wide: 0.05em;
}

@font-face {
  font-family: 'Inter';
  src: url('../../assets/fonts/inter/Inter-Regular.woff2') format('woff2');
  font-weight: 400; font-style: normal; font-display: swap;
}
@font-face {
  font-family: 'Playfair Display';
  src: url('../../assets/fonts/playfair/PlayfairDisplay-Regular.woff2') format('woff2');
  font-weight: 400; font-style: normal; font-display: swap;
}
@font-face {
  font-family: 'Playfair Display';
  src: url('../../assets/fonts/playfair/PlayfairDisplay-Bold.woff2') format('woff2');
  font-weight: 700; font-style: normal; font-display: swap;
}
```

**`core/tokens/colors.css`**
```css
:root {
  /* Brand */
  --brand-gold: #d4af37; --brand-gold-light: #f4cf57; --brand-gold-dark: #b8941f;
  --brand-emerald: #10b981; --brand-ruby: #ef4444; --brand-sapphire: #0891b2; --brand-amethyst: #7c3aed;

  /* Theme (dark default) */
  --color-bg: #0a0a0a; --color-bg-alt: #1a1a1a; --color-bg-hover: #252525;
  --color-surface: var(--color-bg-alt); --color-border: #2d2d2d;
  --color-text: #ffffff; --color-text-muted: #a1a1aa; --color-text-inverse: #0a0a0a;

  /* Semantic */
  --color-brand: var(--brand-gold); --color-brand-rgb: 212, 175, 55;
  --color-success: var(--brand-emerald); --color-error: var(--brand-ruby);
  --color-info: var(--brand-sapphire); --color-accent: var(--brand-amethyst);

  /* Shadows */
  --shadow-xs: 0 1px 2px rgba(0,0,0,0.05); --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
  --shadow-md: 0 4px 6px rgba(0,0,0,0.1); --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
  --shadow-xl: 0 20px 25px rgba(0,0,0,0.15); --shadow-2xl: 0 25px 50px rgba(0,0,0,0.25);

  /* Focus */
  --focus-ring: 0 0 0 2px var(--color-bg), 0 0 0 4px var(--brand-gold);
}

[data-theme="light"] {
  --color-bg: #ffffff; --color-bg-alt: #f8fafc; --color-bg-hover: #f1f5f9;
  --color-surface: var(--color-bg-alt); --color-border: #e2e8f0;
  --color-text: #1e293b; --color-text-muted: #475569; --color-text-inverse: #ffffff;
  --brand-gold: #926f00; --brand-gold-dark: #7a5c00; --brand-gold-light: #af8600;
  --color-brand-rgb: 146, 111, 0;
}
```

**Files created:**
- `templates/src/core/tokens/reset.css`
- `templates/src/core/tokens/layout-grid.css`
- `templates/src/core/tokens/fonts.css`
- `templates/src/core/tokens/colors.css`

**Files modified:**
- `templates/src/core/tokens/unified.css` → replace with `@import` chain only

**LOGGING:** Log token count per file. Verify 0 hex colors outside `colors.css`.

---

#### Task 2: Create `core.ts` — single shared entry

Create one shared JS entry that imports all shared CSS + initializes shared components:

```ts
// core.ts — Shared layout + components (always loaded)

// Tokens + reset
import './core/tokens/reset.css';
import './core/tokens/layout-grid.css';
import './core/tokens/fonts.css';
import './core/tokens/colors.css';

// Shared components
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
import { initThemeSwitcher } from './core/components/theme-switcher.js';
import { initNavbar } from './core/components/navbar.js';

console.log('Core initialized');

document.addEventListener('DOMContentLoaded', () => {
  initThemeSwitcher();
  initNavbar();
});
```

**Files created:** `templates/src/core.ts`

**LOGGING:** Verify all shared CSS files exist and are imported.

---

### Phase 2: Page-Specific Entries

#### Task 3: Create `pages/home/home.ts` + `home.css`

```ts
// pages/home/home.ts — Home page specific
import './home.css';

import { initHero } from './hero.js';
import { initFeatures } from './features.js';
import { initStats } from './stats.js';

console.log('Home page initialized');

document.addEventListener('DOMContentLoaded', () => {
  initHero();
  initFeatures();
  initStats();
});
```

```css
/* pages/home/home.css */
@import '../../core/sections/hero/hero.css';
@import '../../core/sections/features/features.css';
@import '../../core/sections/stats/stats.css';
@import '../../core/sections/cta/cta.css';
```

**Files created:**
- `templates/src/pages/home/home.ts`
- `templates/src/pages/home/home.css`

**Files to create (if not exist):**
- `templates/src/pages/home/hero.ts`
- `templates/src/pages/home/features.ts`
- `templates/src/pages/home/stats.ts`

**LOGGING:** Log page entry structure.

---

#### Task 4: Create `pages/dash/dash.ts` + `dash.css`

Migrate from `cardboard/app.ts` + `cardboard/styles/core.css` + `cardboard/styles/dashboard.css`:

```ts
// pages/dash/dash.ts — Dashboard page specific
import './dash.css';

import { initSidebar } from '../../core/components/sidebar.js';
import { initAdminTables } from '../../core/components/admin-table.js';
import { initCharts } from './charts.js';
import { initDashboard } from './dashboard.js';

console.log('Dashboard page initialized');

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initAdminTables();
  initCharts();
  initDashboard();
});
```

```css
/* pages/dash/dash.css */

/* Admin layout grid */
.admin-layout {
  display: grid;
  grid-template-columns: var(--dash-sidebar-width) 1fr;
  grid-template-rows: var(--dash-header-height) 1fr;
  grid-template-areas: "sidebar navbar" "sidebar content";
  min-height: 100vh;
}

.admin-layout__sidebar { grid-area: sidebar; }
.admin-layout__navbar { grid-area: navbar; }
.admin-layout__main { grid-area: content; padding: var(--space-6); }

.admin-layout.is-collapsed {
  grid-template-columns: var(--dash-sidebar-collapsed) 1fr;
  transition: grid-template-columns var(--transition-base);
}

/* Sidebar */
.sidebar {
  position: sticky; inset-block-start: 0; height: 100vh;
  background: var(--color-surface); border-right: 1px solid var(--color-border);
  display: flex; flex-direction: column; z-index: 100;
}
.sidebar__header { height: var(--dash-header-height); display: flex; align-items: center; padding: 0 var(--space-4); border-bottom: 1px solid var(--color-border); }
.sidebar__brand { font-size: var(--text-lg); font-weight: var(--font-bold); color: var(--color-text); }
.sidebar__nav { flex: 1; padding: var(--space-4); overflow-y: auto; }
.sidebar__section { margin-bottom: var(--space-6); }
.sidebar__section-title { font-size: var(--text-xs); font-weight: var(--font-medium); color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; padding: var(--space-2) var(--space-3); }
.sidebar__link { display: flex; align-items: center; gap: var(--space-3); padding: var(--space-2) var(--space-3); color: var(--color-text); border-radius: var(--radius-md); transition: background-color var(--transition-fast); }
.sidebar__link:hover { background: var(--color-bg-hover); }
.sidebar__link--active { background: var(--color-brand); color: var(--color-text-inverse); }

/* Navbar */
.navbar {
  position: sticky; inset-block-start: 0; height: var(--dash-header-height);
  background: var(--color-surface); border-bottom: 1px solid var(--color-border);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 var(--space-4); z-index: 50;
}

/* Tables */
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: var(--space-3) var(--space-4); text-align: left; border-bottom: 1px solid var(--color-border); }
.admin-table th { font-weight: var(--font-medium); background: var(--color-bg); }

/* Flash */
.flash-messages { margin-bottom: var(--space-4); }
.flash-message { padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-2); }
.flash-message--success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); color: var(--color-success); }
.flash-message--error { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--color-error); }

/* Charts */
.chart-container { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-6); }
```

**Files created:**
- `templates/src/pages/dash/dash.ts`
- `templates/src/pages/dash/dash.css`

**Files migrated from:**
- `templates/src/cardboard/app.ts` → `pages/dash/dash.ts`
- `templates/src/cardboard/styles/core.css` → `pages/dash/dash.css`
- `templates/src/cardboard/styles/dashboard.css` → merged into `pages/dash/dash.css`
- `templates/src/cardboard/components/sidebar.js` → `core/components/sidebar.js`
- `templates/src/cardboard/components/admin-table.js` → `core/components/admin-table.js`
- `templates/src/cardboard/components/charts.js` → `pages/dash/charts.js`
- `templates/src/cardboard/pages/dashboard/dashboard.js` → `pages/dash/dashboard.js`

**LOGGING:** Log each migrated file. Verify no `position: fixed` in dash layout.

---

#### Task 5: Create `pages/auth/auth.ts` + `auth.css`

Migrate from `auth/app.ts` + `auth/styles.css`:

```ts
// pages/auth/auth.ts — Auth page specific
import './auth.css';

import { initAuthForm } from './auth-form.js';

console.log('Auth page initialized');

document.addEventListener('DOMContentLoaded', () => {
  initAuthForm();
});
```

```css
/* pages/auth/auth.css */

.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--color-bg) 0%, var(--color-surface) 100%);
  padding: var(--section-padding-x);
}

.auth-container {
  width: 100%;
  max-width: var(--auth-max-width);
}

.auth-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--card-radius);
  padding: var(--space-8);
  box-shadow: var(--shadow-lg);
}

.auth-card__header {
  text-align: center;
  margin-bottom: var(--space-6);
  position: relative;
}

.auth-card__title {
  font-size: var(--text-xl);
  font-weight: var(--font-bold);
  color: var(--color-text);
  margin-bottom: var(--space-2);
}

.auth-card__subtitle {
  font-size: var(--text-sm);
  color: var(--color-text-muted);
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.form-label {
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--color-text);
}

.form-input {
  padding: var(--space-2) var(--space-3);
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  color: var(--color-text);
  font-size: var(--text-base);
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
}

.form-input:focus {
  outline: none;
  border-color: var(--color-brand);
  box-shadow: 0 0 0 3px rgba(var(--color-brand-rgb), 0.1);
}

.btn--primary {
  background: var(--color-brand);
  color: var(--color-text-inverse);
}

.btn--primary:hover {
  background: var(--color-brand-dark);
}

.alert--success {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: var(--color-success);
}

.alert--error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 0.3);
  color: var(--color-error);
}
```

**Files created:**
- `templates/src/pages/auth/auth.ts`
- `templates/src/pages/auth/auth.css`
- `templates/src/pages/auth/auth-form.ts`

**Files migrated from:**
- `templates/src/auth/app.ts` → `pages/auth/auth.ts` + `auth-form.ts`
- `templates/src/auth/styles.css` → `pages/auth/auth.css`

**LOGGING:** Verify all hardcoded values replaced with tokens.

---

### Phase 3: Unified Layout Template

#### Task 6: Create unified layout PHP class

Create `app/src/PageLayout.php` — detects current page type and provides layout data:

```php
<?php

declare(strict_types=1);

namespace App;

final class PageLayout
{
    private const PAGE_MAP = [
        // Dashboard pages
        'cardboard' => 'dash',
        'admin'     => 'dash',

        // Auth pages
        'login'     => 'auth',
        'register'  => 'auth',
        'password'  => 'auth',

        // Front pages (default)
        'home'      => 'home',
        'blog'      => 'home',
        'about'     => 'home',
    ];

    public static function detect(string $template): string
    {
        $template = str_replace('.', '/', $template);
        $parts = explode('/', $template);
        $first = $parts[0] ?? '';

        return self::PAGE_MAP[$first] ?? 'home';
    }

    public static function bodyClass(string $page): string
    {
        return match ($page) {
            'dash'  => 'admin-layout',
            'auth'  => 'auth-page',
            default => 'front-page',
        };
    }

    public static function layoutFile(string $page): ?string
    {
        return match ($page) {
            'dash'  => 'cardboard/layout/base',
            'auth'  => 'cardboard/auth/base',
            default => 'app/layouts/app',
        };
    }
}
```

**Files created:** `app/src/PageLayout.php`

**LOGGING:** Log page detection map.

---

#### Task 7: Refactor View + ViewAdapter to use PageLayout

Update `app/src/View.php` — auto-detect page and layout:

```php
public static function render(
    string $template,
    array $data = [],
    ?string $layout = null,  // null = auto-detect
    array $pageAssets = [],
    ?string $lcpImage = null,
): string {
    self::$pageAssets = $pageAssets;
    self::$lcpImage = $lcpImage;
    $template = str_replace('.', '/', $template);
    self::$currentTemplate = $template;

    // Auto-detect page type and layout
    $page = PageLayout::detect($template);
    self::$currentPage = $page;

    if ($layout === null) {
        $layout = PageLayout::layoutFile($page);
    }

    if ($layout) {
        $layout = str_replace('.', '/', $layout);
    }

    $content = self::renderFile($template, $data);

    if ($layout === null) {
        return $content;
    }

    return self::renderFile($layout, [...$data, 'content' => $content, 'currentPage' => $page]);
}
```

Update `app/src/ViewAdapter.php` — pass page detection through:

```php
public function render(string $template, array $data = []): Response
{
    $page = PageLayout::detect($template);
    return Response::html($this->renderToString($template, $data, page: $page));
}
```

**Files modified:** `app/src/View.php`, `app/src/ViewAdapter.php`

**LOGGING:** Verify auto-detection works for sample templates.

---

#### Task 8: Refactor layout templates

**Unified layout pattern** — all layouts load `init` + `core` + page-specific:

**`templates/app/layouts/app.php`** (front):
```php
<?php
use App\View;
$page = $currentPage ?? 'home';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $this->e($title ?? 'Nativa') ?></title>
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />

    <!-- Always loaded -->
    <?= View::vite('init') ?>
    <?= View::vite('core') ?>

    <!-- Page-specific -->
    <?= View::vite('page-' . $page) ?>

    <?= $this->yield('head') ?>
</head>
<body class="front-page">
    <nav class="navbar">...</nav>
    <main>
        <?= $this->yield('content') ?>
    </main>
    <footer class="section section--sm">
        <div class="container">...</div>
    </footer>
    <?= $this->yield('scripts') ?>
</body>
</html>
```

**`templates/cardboard/layout/base.php`** (dash):
```php
<?php
use App\View;
$page = $currentPage ?? 'dash';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $this->e($pageTitle ?? 'Dashboard') ?></title>
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />

    <!-- Always loaded -->
    <?= View::vite('init') ?>
    <?= View::vite('core') ?>

    <!-- Page-specific -->
    <?= View::vite('page-' . $page) ?>

    <?= $this->yield('head') ?>
</head>
<body class="admin-layout">
    <aside class="admin-layout__sidebar sidebar">
        <?= $this->include('cardboard::partials/sidebar', [...]) ?>
    </aside>
    <header class="admin-layout__navbar navbar">
        <?= $this->include('cardboard::partials/navbar', [...]) ?>
    </header>
    <main class="admin-layout__main">
        <?= $this->include('cardboard::partials/flash', [...]) ?>
        <?= $this->yield('content') ?>
    </main>
    <?= $this->yield('scripts') ?>
</body>
</html>
```

**`templates/cardboard/auth/base.php`** (auth):
```php
<?php
use App\View;
$page = $currentPage ?? 'auth';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $this->e($pageTitle ?? 'Login') ?></title>
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />

    <!-- Always loaded -->
    <?= View::vite('init') ?>
    <?= View::vite('core') ?>

    <!-- Page-specific -->
    <?= View::vite('page-' . $page) ?>

    <?= $this->yield('head') ?>
</head>
<body class="auth-page">
    <main class="auth-layout">
        <div class="auth-container">
            <?= $this->yield('content') ?>
        </div>
    </main>
    <?= $this->yield('scripts') ?>
</body>
</html>
```

**Files modified:**
- `templates/app/layouts/app.php`
- `templates/cardboard/layout/base.php`
- `templates/cardboard/auth/base.php`

**LOGGING:** Verify all three layouts follow init → core → page pattern.

---

### Phase 4: Vite Config + Build

#### Task 9: Refactor `vite.config.ts` — single build, page entries

Replace dual-build with single build:

```ts
import { defineConfig } from 'vite';
import { resolve } from 'path';
import fs from 'fs';

function getFrontendInputs(baseDir: string): Record<string, string> {
  const inputs: Record<string, string> = {};
  const frontendDir = resolve(baseDir, 'src/frontend');
  if (!fs.existsSync(frontendDir)) return inputs;

  const walk = (dir: string, prefix = '') => {
    for (const file of fs.readdirSync(dir, { withFileTypes: true })) {
      if (file.isDirectory()) {
        walk(resolve(dir, file.name), resolve(prefix, file.name));
      } else if (file.name.endsWith('.ts') || file.name.endsWith('.css')) {
        const name = resolve(prefix, file.name.replace(/\.(ts|css)$/, '')).replace(/\\/g, '/');
        inputs[file.name.endsWith('.css') ? `${name}-style` : name] = resolve(dir, file.name);
      }
    }
  };
  walk(frontendDir);
  return inputs;
}

export default defineConfig(() => {
  const frontendInputs = getFrontendInputs(__dirname);

  return {
    base: '/',
    publicDir: 'static',
    server: { allowedHosts: true, port: 5173 },
    preview: { allowedHosts: true, port: 4173 },
    build: {
      target: ['chrome67', 'es2015'],
      outDir: '../public/dist',
      emptyOutDir: true,
      manifest: 'manifest.json',
      rollupOptions: {
        input: {
          // Always loaded
          init:     resolve(__dirname, 'src/init.ts'),
          core:     resolve(__dirname, 'src/core.ts'),

          // Page-specific
          'page-home': resolve(__dirname, 'src/pages/home/home.ts'),
          'page-dash': resolve(__dirname, 'src/pages/dash/dash.ts'),
          'page-auth': resolve(__dirname, 'src/pages/auth/auth.ts'),

          // Auto-discovered frontend
          ...frontendInputs,
        },
        output: {
          entryFileNames: 'assets/[name]-[hash].js',
          chunkFileNames: 'assets/[name]-[hash].js',
          assetFileNames: (info) => {
            return info.name?.endsWith('.css')
              ? 'assets/[name]-[hash].css'
              : 'assets/[name]-[hash][extname]';
          },
        },
      },
    },
  };
});
```

Update `templates/package.json` scripts:
```json
{
  "scripts": {
    "dev": "vite",
    "build": "rm -rf ../public/dist && vite build",
    "preview": "vite preview"
  }
}
```

Update `App\View` manifest path:
```php
// Before: two manifests (front/dash)
// After: one manifest
$basePath . '/dist/manifest.json'
```

**Files modified:**
- `templates/vite.config.ts`
- `templates/package.json`
- `app/src/View.php` (manifest path)

**LOGGING:** Log new entry structure. Verify build output.

---

#### Task 10: Build + verify

```bash
cd templates
pnpm build
```

Checks:
1. `public/dist/manifest.json` exists with entries: `init`, `core`, `page-home`, `page-dash`, `page-auth`
2. `core-[hash].css` contains all shared tokens + components
3. `page-home-[hash].css` contains only home-specific styles
4. `page-dash-[hash].css` contains only dash-specific styles (grid layout, sidebar, tables)
5. `page-auth-[hash].css` contains only auth-specific styles
6. 0 `position: fixed` in any layout CSS
7. 0 hardcoded hex colors outside `colors.css`

Visual smoke test:
- **Home:** navbar, hero, features grid, footer — all styled
- **Dashboard:** sidebar + navbar grid, content area, tables — no overlap
- **Auth:** centered card, gradient bg, form — responsive

**Files:** `public/dist/manifest.json`, `public/dist/assets/*`

**LOGGING:** Log build sizes per entry, warnings, visual results.

---

## Final Architecture

```
templates/src/
├── init.ts                    # Theme FOUC prevention (always first)
├── core.ts                    # Shared tokens + components (always loaded)
│
├── pages/
│   ├── home/
│   │   ├── home.ts            # Page JS
│   │   ├── home.css           # Page CSS
│   │   ├── hero.ts
│   │   ├── features.ts
│   │   └── stats.ts
│   ├── dash/
│   │   ├── dash.ts            # Page JS
│   │   ├── dash.css           # Page CSS (grid layout, sidebar, tables)
│   │   ├── charts.js
│   │   └── dashboard.js
│   └── auth/
│       ├── auth.ts            # Page JS
│       ├── auth.css           # Page CSS
│       └── auth-form.ts
│
├── core/
│   ├── tokens/
│   │   ├── reset.css
│   │   ├── layout-grid.css
│   │   ├── fonts.css
│   │   └── colors.css
│   ├── components/
│   │   ├── navbar.css
│   │   ├── button.css
│   │   ├── vanilla-card.css
│   │   ├── card-grid.css
│   │   ├── sidebar.js         # shared (dash uses it)
│   │   ├── admin-table.js     # shared (dash uses it)
│   │   └── theme-switcher.js
│   └── global/
│       └── utilities.css
│
└── frontend/                  # Auto-discovered page sections
    └── ...
```

### Page Load Flow

```
Browser request
    ↓
Layout template (PHP)
    ↓
View::vite('init')     → <script> theme init (inline, blocking)
View::vite('core')     → <link> tokens + components CSS
                         → <script> shared JS (navbar, theme switcher)
View::vite('page-X')   → <link> page CSS
                         → <script> page JS (DOMContentLoaded)
```

### Before vs After

| Aspect | Before | After |
|--------|--------|-------|
| Build targets | 2 (front + dash) | 1 (unified) |
| Token files | 1 monolithic | 4 focused |
| CSS entries | `core-css`, `style`, `dashboard-style`, `auth-style` | `core`, `page-home`, `page-dash`, `page-auth` |
| JS entries | `core-app`, `app`, `dashboard-app`, `auth-app` | `core`, `page-home`, `page-dash`, `page-auth` |
| Layout detection | Manual (`forcePrefix`) | Auto (`PageLayout::detect()`) |
| Manifest | 2 files | 1 file |
| `--color-surface` | ❌ undefined | ✅ defined |
| `--color-brand` | ❌ undefined | ✅ defined |
| Dashboard layout | `position: fixed` hack | CSS Grid areas |
| Page flow | init → core → page | init → core → page ✓ |
