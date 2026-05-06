# Frontend Asset Architecture

## Overview

Projekt Nativa CMS používa **Vite dual-build** pre oddelené assety:
- **`/front/`** — Frontend (publikačná stránka, blog, autentifikácia)
- **`/dash/`** — Admin dashboard (cardboard admin)
- **`/mark/`** — Framework mark assets (fallback)

## Build System

### Scripty (`templates/package.json`)

```bash
# Vývoj (s HMR)
pnpm dev          # vite dev server na porte 5173

# Produkcia
pnpm build        # build front assets → ../public/front/
pnpm build:dash   # build dashboard assets → ../public/dash/
pnpm build:all    # build oboje (front aj dash)
```

### Vite config (`templates/vite.config.ts`)

Konfigurácia detekuje build target cez `BUILD_TARGET=dash` env premennu:

| Build | Base | Out dir | Manifest |
|-------|------|---------|----------|
| `default` (front) | `/front/` | `../public/front` | `vanilla-cards-manifest.json` |
| `BUILD_TARGET=dash` | `/dash/` | `../public/dash` | `dashboard-manifest.json` |

```ts
// Front build (default)
inputs = {
  init:           'src/init.ts',
  'core-app':     'src/app.ts',
  'core-css':     'src/css.ts',
  'theme-switcher': 'src/dev/theme-switcher.ts',
  'auth-app':     'src/auth/app.ts',
  'auth-style':   'src/auth/styles.css',
  'home-css':     'src/home/styles.css',
  // + auto-discovered src/frontend/*.ts,.css
}

// Dashboard build (BUILD_TARGET=dash)
inputs = {
  'core-app':        'src/cardboard/app.ts',
  'style':           'src/cardboard/styles/cardboard.css',
  'dashboard-app':   'src/cardboard/pages/dashboard/dashboard.ts',
  'dashboard-style': 'src/cardboard/styles/dashboard.css',
}
```

## Asset Resolution (PHP)

### `App\View` — Low-level helper

```php
<?= \App\View::vite('core-css') ?>      // Vyrieši cestu cez manifest
<?= \App\View::vite('home-css') ?>      // Page-specific asset
<?= \App\View::forcePrefix('dash') ?>   // Vynúti dash prefix
```

**Prefix detekcia automatická:**
- `cardboard/*` → `dash`
- `*/auth/*` → `front`
- všetko ostatné → `front`

### `App\ViewAdapter` — Framework adapter

```php
use App\ViewAdapter;

return $viewAdapter
    ->withLcpImage($imageUrl)
    ->withAssets('home.index', ['home/home'], ['home/home-css'])
    ->render('home.index', $data);
```

### Layouty

#### Frontend Layout (`templates/app/layouts/app.php`)
- Načítava `core-css` + pageAssets + `init` + `core-app` + `theme-switcher`
- Obsahuje navbar, main, footer
- Theme FOUC prevencia cez `init.ts`

#### Dashboard Layout (`templates/cardboard/layout/base.php`)
```php
<?php \App\View::forcePrefix('dash'); ?>
<?= \App\View::vite('style') ?>
<?= \App\View::vite('core-app') ?>
<!-- Page-specific -->
<?= \App\View::vite('dashboard-style') ?>
<?= \App\View::vite('dashboard-app') ?>
```

#### Auth Layout (`templates/cardboard/auth/base.php`)
- Jednoduchý — iba `auth-style` + `auth-app`

## File Structure

```
templates/
├── src/
│   ├── init.ts                    # Theme FOUC prevention (pre body)
│   ├── app.ts                     # Front core JS
│   ├── css.ts                     # Front core CSS (tokens, components)
│   │
│   ├── auth/
│   │   ├── app.ts
│   │   └── styles.css
│   │
│   ├── home/
│   │   └── styles.css             # @import hero, features, stats, cta
│   │
│   ├── cardboard/
│   │   ├── app.ts                 # Dashboard core JS (sidebar init)
│   │   ├── components/
│   │   │   ├── sidebar.js
│   │   │   ├── admin-table.js
│   │   │   └── charts.js          # Chart.js inicializácia
│   │   ├── pages/
│   │   │   └── dashboard/
│   │   │       ├── dashboard.ts   # Page entry
│   │   │       ├── dashboard.js   # Page logic
│   │   │       └── dashboard-charts.js
│   │   └── styles/
│   │       ├── core.css           # Layout, sidebar, navbar, tables
│   │       ├── cardboard.css      # Legacy (admin tables, charts, modals)
│   │       └── dashboard.css      # @import core.css + page specific
│   │
│   └── core/                      # Design tokens, components, sections
│       ├── tokens/
│       ├── components/
│       └── sections/
│
├── cardboard/
│   ├── layout/
│   │   └── base.php               # Dashboard layout
│   ├── auth/
│   │   └── base.php               # Auth layout
│   └── partials/
│       ├── navbar.php
│       ├── sidebar.php
│       └── flash.php
│
└── package.json
```

## Manifest Format

Vite generuje JSON manifest pre cesty s hashem:

```json
{
  "core-app": {
    "file": "assets/core-app-abc123.js",
    "name": "core-app",
    "css": ["assets/style-abc123.css"]
  }
}
```

PHP `View::vite('core-app')` vyhľadá via `name` property a vygeneruje:
```html
<link rel="stylesheet" href="/dash/style-abc123.css" />
<script type="module" src="/dash/core-app-abc123.js"></script>
```

## Performance

| Asset | Size (prod) | Popis |
|-------|-------------|-------|
| style | 3.34 kB | Dash core CSS |
| core-app | 0.52 kB | Dash core JS |
| dashboard-style | 2.38 kB | Dash page CSS |
| dashboard-app | 208.28 kB | Dash page JS (s chart.js) |
| core-css | ~5 kB | Front core CSS |
| init | ~0.3 kB | Theme init (critical) |

---

*Posledná aktualizácia: 2026-05-06*
