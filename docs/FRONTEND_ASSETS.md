# Frontend Asset Architecture

## Overview

Nativa CMS uses a **unified page-based architecture** where every page has:
- A PHP template in `templates/pages/<name>/`
- A Vite entry in `src/pages/<name>/<name>.ts` + `<name>.css`
- A body class from `PageLayout::bodyClass()`
- Auto-detected layout from `PageLayout::layoutFile()`

## Build System

### Scripts (`templates/package.json`)

```bash
# Development (with HMR)
pnpm dev          # vite dev server on port 5173

# Production
pnpm build        # build all assets → ../public/dist/
```

### Vite config (`templates/vite.config.ts`)

Single build target, page-based entries:

```typescript
rollupOptions.input = {
  init:             'src/init.ts',
  core:             'src/core.ts',
  'page-home':      'src/pages/home/home.ts',
  'page-portfolio': 'src/pages/portfolio/portfolio.ts',
  'page-articles':  'src/pages/articles/articles.ts',
  'page-dash':      'src/pages/dash/dash.ts',
  'page-auth':      'src/pages/auth/auth.ts',
  'page-errors':    'src/pages/errors/errors.ts',
  'page-dev':       'src/dev/theme-switcher.ts',
};
```

| Entry | Type | Loaded on |
|-------|------|-----------|
| `init` | JS | Every page (FOUC prevention — theme) |
| `core` | JS + CSS | Every page (tokens, components, SectionLoader) |
| `page-*` | JS + CSS | Page-specific (auto-detected per route) |
| `page-dev` | JS | Dev only (theme switcher) |

## File Structure

```
templates/
├── pages/                       # PHP templates (flat structure)
│   ├── home/
│   │   └── template.php         # Public home page
│   ├── portfolio/
│   │   ├── index.php            # Portfolio list
│   │   └── show.php             # Portfolio detail
│   ├── articles/
│   │   ├── index.php            # Blog article list
│   │   ├── show.php             # Single article
│   │   ├── create.php           # Article form
│   │   └── not-found.php        # Article not found
│   ├── dash/
│   │   └── index.php            # Admin dashboard
│   ├── auth/
│   │   └── login.php            # Login page
│   └── errors/
│       ├── 404.php              # Not found
│       └── 500.php              # Server error
│
├── layouts/                     # PHP layouts
│   ├── app.php                  # Public layout (navbar + footer)
│   ├── admin.php                # Admin layout (sidebar + navbar)
│   └── auth.php                 # Auth layout (centered card)
│
├── partials/                    # Shared partials
│   ├── sidebar.php
│   ├── navbar.php
│   └── flash.php
│
└── src/                         # Frontend source
    ├── init.ts                  # Theme FOUC prevention
    ├── core.ts                  # Shared core (tokens, components, sections)
    │
    ├── core/
    │   ├── tokens/              # Design tokens (colors, spacing, fonts)
    │   ├── components/          # Shared CSS components
    │   │   ├── button.css
    │   │   ├── vanilla-card.css
    │   │   ├── card-grid.css
    │   │   ├── form.css         # Shared form styles
    │   │   ├── navbar.css
    │   │   ├── notification.css
    │   │   ├── footer.css
    │   │   └── ...
    │   ├── sections/            # Auto-loaded via data-section attribute
    │   │   ├── hero/
    │   │   ├── features/
    │   │   ├── stats/
    │   │   ├── cta/
    │   │   └── navbar/
    │   └── theme/
    │       └── default/
    │
    └── pages/                   # Page-specific entries
        ├── home/
        │   ├── home.ts          # Imports home.css
        │   └── home.css         # @import hero, features, stats, cta
        ├── portfolio/
        │   ├── portfolio.ts
        │   └── portfolio.css    # .tag styles
        ├── articles/
        │   ├── articles.ts
        │   └── articles.css     # @import hero, features, form
        ├── dash/
        │   ├── dash.ts
        │   └── dash.css         # @import sidebar, flash, charts
        ├── auth/
        │   ├── auth.ts
        │   └── auth.css         # @import form
        └── errors/
            ├── errors.ts        # Shared entry for 404 + 500
            └── errors.css
```

## Page Resolution — `PageLayout`

Single source of truth connecting template paths → page names → assets + layout:

```php
// app/src/PageLayout.php

// Maps folder names to page identifiers
PAGE_MAP = [
    'home'       => 'home',
    'portfolio'  => 'portfolio',
    'articles'   => 'articles',
    'dash'       => 'dash',
    'auth'       => 'auth',
    'errors'     => 'errors',
    // Backward compat (old paths)
    'blog'       => 'articles',
    'cardboard'  => 'dash',
    'admin'      => 'dash',
];

// Page → layout file
LAYOUT_MAP = [
    'dash'   => 'layouts/admin',
    'auth'   => 'layouts/auth',
    // default: 'layouts/app'
];

// Page → body class
BODY_CLASS_MAP = [
    'dash'   => 'layout-admin',   // legacy class name
    'errors' => 'page-errors',
    // default: 'page-<name>'
];
```

### Detection algorithm

```
detect('pages/portfolio/show')
  → check 'pages' → container, skip
  → check 'portfolio' → specific match → return 'portfolio'

detect('cardboard/auth/login')
  → check 'cardboard' → container match, remember 'dash'
  → check 'auth' → specific match → return 'auth' (overrides container)
```

### Architectural pattern

```
page-name
  → pages/<name>/template.php        (PHP template)
  → src/pages/<name>/<name>.ts       (Vite JS entry)
  → src/pages/<name>/<name>.css      (Vite CSS entry)
  → body class: page-<name>          (from PageLayout::bodyClass)
  → layout: layouts/<layout>.php     (from PageLayout::layoutFile)
  → Vite entry: page-<name>          (from PageLayout::detect)
```

## Asset Resolution (PHP)

### `App\View` — Low-level helper

```php
// In layouts — auto-resolve Vite assets via manifest
<?= View::viteJs('init') ?>                    // <script> for init
<?= View::viteCss('core') ?>                   // <link> for core CSS
<?= View::viteCss('page-' . $page) ?>          // <link> for page CSS
<?= View::viteJs('core') ?>                    // <script> for core JS
<?= View::viteJs('page-' . $page) ?>           // <script> for page JS
```

### `App\ViewAdapter` — Framework adapter

```php
return $this->view
    ->render('pages/home/template', [
        'eyebrow' => 'Nativa',
        'title'   => 'Welcome to Nativa',
        'message' => 'Hello, Marko!',
    ]);
```

### Layouts

All three layouts follow the same pattern:

```php
<?php
use App\View;
use App\PageLayout;
$page = $currentPage ?? PageLayout::detect($currentTemplate ?? 'home');
$bodyClass = PageLayout::bodyClass($page);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <?= View::viteJs('init') ?>
    <?= View::viteCss('core') ?>
    <?= View::viteCss('page-' . $page) ?>
    <?= View::viteJs('core') ?>
    <?= View::viteJs('page-' . $page) ?>
    <?= $this->yield('head') ?>
</head>
<body class="<?= $bodyClass ?>">
    <!-- page-specific layout structure -->
    <?= $this->yield('content') ?>
    <?= $this->yield('scripts') ?>
</body>
</html>
```

| Layout | Body class | Structure |
|--------|------------|-----------|
| `layouts/app.php` | `page-<name>` | Navbar → main → footer |
| `layouts/admin.php` | `layout-admin` | Sidebar + navbar → main |
| `layouts/auth.php` | `page-auth` | Centered card |

## Manifest Format

Vite generates a JSON manifest with hashed filenames:

```json
{
  "page-home": {
    "file": "assets/page-home-abc123.js",
    "name": "page-home",
    "css": ["assets/page-home-abc123.css"]
  }
}
```

`View::viteJs('page-home')` resolves via `name` property:
```html
<script type="module" src="/dist/assets/page-home-abc123.js"></script>
```

## CSS Architecture

### Core (always loaded via `core.ts`)

```
core/tokens/       → reset.css, unified.css, colors.css, fonts.css, layout-grid.css
core/components/   → button.css, vanilla-card.css, card-grid.css, form.css,
                     navbar.css, notification.css, footer.css, table.css,
                     page-header.css, icon.css, icon-button.css
core/sections/     → loaded on demand via page CSS @import
core/theme/        → default/theme.css (brand colors)
core/global/       → utilities.css
```

### Page CSS (loaded per-page)

Each page CSS imports only the sections it needs:

```css
/* pages/home/home.css */
@import '../../core/sections/hero/hero.css';
@import '../../core/sections/features/features.css';
@import '../../core/sections/stats/stats.css';
@import '../../core/sections/cta/cta.css';

/* pages/articles/articles.css */
@import '../../core/sections/hero/hero.css';
@import '../../core/sections/features/features.css';
@import '../../core/components/form.css';

/* pages/auth/auth.css */
@import '../../core/components/form.css';
```

### Section auto-loading (JS)

Sections auto-instantiate via `data-section` attributes + `SectionLoader`:

```html
<section class="hero-section" data-section="hero">
```

```typescript
// SectionLoader maps data-section values to classes
const sectionMap = {
  hero: HeroSection,
  features: FeaturesSection,
  cta: CTASection,
  stats: StatsSection,
  navbar: NavbarSection,
};
```

## Performance

| Asset | Size (prod, gzipped) | Description |
|-------|----------------------|-------------|
| `init` | ~0.18 kB | Theme FOUC prevention (critical) |
| `core` CSS | ~4.56 kB | All tokens + shared components |
| `core` JS | ~20.36 kB | HTMX + SectionLoader + components |
| `page-home` CSS | ~1.09 kB | Hero + features + stats + cta |
| `page-home` JS | ~0.05 kB | Minimal init log |
| `page-articles` CSS | ~1.06 kB | Hero + features + form |
| `page-portfolio` CSS | ~0.15 kB | Tag styles |
| `page-dash` CSS | ~0.48 kB | Sidebar + flash |
| `page-dash` JS | ~71.22 kB | Charts (Chart.js) + dashboard |
| `page-auth` CSS | ~0.52 kB | Form styles |
| `page-errors` CSS | ~0.02 kB | Minimal |

---

*Last updated: 2026-05-09*
