# Plan: Unified Template Architecture — Flat Pages Structure

> **Goal:** Restructure templates from scattered `app/`, `cardboard/`, `blog/article/` into a unified flat `templates/pages/` + `templates/layouts/` structure. Frontend `src/pages/` follows the same shape. Every page = one Vite entry (TS+CSS), one body class, auto-detected layout.

**Date:** 2026-05-09
**Mode:** Fast

---

## Target Structure

### PHP Templates (after)

```
templates/
├── pages/
│   ├── home/
│   │   └── template.php        ← was app/home.php
│   ├── portfolio/
│   │   ├── index.php           ← was app/portfolio.php
│   │   └── show.php            ← was app/portfolio-show.php
│   ├── articles/               ← was blog/article/
│   │   ├── index.php
│   │   ├── show.php
│   │   ├── create.php
│   │   └── not-found.php       ← was blog/article/not-found.php
│   ├── dash/
│   │   └── index.php           ← was cardboard/dashboard/index.php
│   ├── auth/
│   │   └── login.php           ← was cardboard/auth/login.php
│   └── errors/
│       ├── 404.php             ← was app/not-found.php
│       └── 500.php             ← NEW
├── layouts/
│   ├── app.php                 ← was app/layouts/app.php
│   ├── admin.php               ← was cardboard/layout/base.php
│   └── auth.php                ← was cardboard/auth/base.php
└── partials/                   ← was cardboard/partials/
    ├── sidebar.php
    ├── navbar.php
    └── flash.php
```

### Frontend (after)

```
templates/src/
├── init.ts                   ← unchanged
├── core.ts                   ← unchanged
├── core/                     ← unchanged (tokens, components, sections)
└── pages/
    ├── home/
    │   ├── home.ts           ← unchanged
    │   └── home.css          ← unchanged
    ├── portfolio/
    │   ├── portfolio.ts      ← was portfolio.ts (empty), now imports CSS
    │   └── portfolio.css     ← NEW (.tag styles + portfolio specifics)
    ├── articles/
    │   ├── articles.ts       ← NEW (replaces blog/article)
    │   └── articles.css      ← NEW (hero + features + form.css)
    ├── dash/
    │   ├── dash.ts           ← unchanged
    │   └── dash.css          ← unchanged
    ├── auth/
    │   ├── auth.ts           ← unchanged
    │   └── auth.css          ← imports form.css
    └── errors/
        ├── errors.ts         ← NEW (shared 404 + 500 entry)
        └── errors.css        ← NEW
```

### Vite entries (after)

```typescript
rollupOptions.input = {
  init:              'src/init.ts',
  core:              'src/core.ts',
  'page-home':       'src/pages/home/home.ts',
  'page-portfolio':  'src/pages/portfolio/portfolio.ts',
  'page-articles':   'src/pages/articles/articles.ts',
  'page-dash':       'src/pages/dash/dash.ts',
  'page-auth':       'src/pages/auth/auth.ts',
  'page-errors':     'src/pages/errors/errors.ts',
  'page-dev':        'src/dev/theme-switcher.ts',
};
```

---

## Settings

- **Testing:** No (structural refactor)
- **Logging:** Standard
- **Docs:** No

---

## Tasks

### Phase 1: Create new PHP template structure

**Task 1: Create `templates/pages/` — move + refactor page templates**

Create new files (don't delete old ones yet):

| New file | Content from | Key changes |
|---|---|---|
| `templates/pages/home/template.php` | `app/home.php` | Replace `$this->layout('app.layouts.app')` → `$this->layout('layouts.app')`. Remove `$currentPage` manual set (auto-detected). |
| `templates/pages/portfolio/index.php` | `app/portfolio.php` | Same layout refactoring. Add `$currentPage = 'portfolio'` for safety. |
| `templates/pages/portfolio/show.php` | `app/portfolio-show.php` | Same. Keep `$currentPage = 'portfolio'`. |
| `templates/pages/articles/index.php` | `blog/article/index.php` | `$this->layout('layouts.app')`. Add `$currentPage = 'articles'`. |
| `templates/pages/articles/show.php` | `blog/article/show.php` | Same pattern. |
| `templates/pages/articles/create.php` | `blog/article/create.php` | Same pattern. |
| `templates/pages/articles/not-found.php` | `blog/article/not-found.php` | Same pattern, `$currentPage = 'articles'`. |
| `templates/pages/dash/index.php` | `cardboard/dashboard/index.php` | `$this->layout('layouts.admin')`. |
| `templates/pages/auth/login.php` | `cardboard/auth/login.php` | `$this->layout('layouts.auth')`. |
| `templates/pages/errors/404.php` | `app/not-found.php` | `$this->layout('layouts.app')`. `$currentPage = 'errors'`. |
| `templates/pages/errors/500.php` | NEW | Server error page, same layout, `$currentPage = 'errors'`. |

**Logging:** Each template should log at DEBUG level when rendered.

**Commit checkpoint** after this phase.

### Phase 2: Create new PHP layouts

**Task 2: Create `templates/layouts/app.php`**

- **From:** `app/layouts/app.php`
- **Key changes:**
  - `View::viteCss('page-' . $page)` — unchanged mechanism
  - `<body class="<?= PageLayout::bodyClass($page) ?>">` — use `PageLayout::bodyClass()` instead of raw `page-<?= $page ?>`
  - `$page = $currentPage ?? PageLayout::detect($currentTemplate ?? 'home')` — resilient fallback

**Task 3: Create `templates/layouts/admin.php`**

- **From:** `cardboard/layout/base.php`
- **Key changes:**
  - `<body class="<?= PageLayout::bodyClass($page) ?>">` — was hardcoded `layout-admin`
  - `$page = $currentPage ?? 'dash'` — consistent pattern

**Task 4: Create `templates/layouts/auth.php`**

- **From:** `cardboard/auth/base.php`
- **Key changes:**
  - `<body class="<?= PageLayout::bodyClass($page) ?>">` — was hardcoded `page-auth`
  - `$page = $currentPage ?? 'auth'`

**Task 5: Move partials to `templates/partials/`**

- `cardboard/partials/sidebar.php` → `templates/partials/sidebar.php`
- `cardboard/partials/navbar.php` → `templates/partials/navbar.php`
- `cardboard/partials/flash.php` → `templates/partials/flash.php`
- Update include paths in admin.php layout: `cardboard::partials/sidebar` → `partials/sidebar`

**Commit checkpoint** after this phase.

### Phase 3: Update PHP routing — `PageLayout` + `View`

**Task 6: Rewrite `PageLayout` for new structure**

- **File:** `app/src/PageLayout.php`
- **New PAGE_MAP** (flat structure — first segment after the template root):

```php
private const PAGE_MAP = [
    'home'       => 'home',
    'portfolio'  => 'portfolio',
    'articles'   => 'articles',
    'dash'       => 'dash',
    'auth'       => 'auth',
    'errors'     => 'errors',
];
```

- **`detect()`** — simplified: path is now `pages/<name>/...`, check second segment
- **`layoutFile()`**:
  ```php
  return match ($page) {
      'dash'   => 'layouts/admin',
      'auth'   => 'layouts/auth',
      default  => 'layouts/app',
  };
  ```
- **`bodyClass()`**:
  ```php
  return match ($page) {
      'dash'   => 'layout-admin',
      'errors' => 'page-errors',
      default  => 'page-' . $page,
  };
  ```

**Task 7: Update controllers to use new template paths**

- **File:** `app/src/Controllers/HomeController.php`
  - `'app/home'` → `'pages/home/template'` (or just `'pages/home'` — see note)
- **File:** `app/src/Controllers/PortfolioController.php`
  - `'app.portfolio'` → `'pages/portfolio/index'`
  - `'app.portfolio-show'` → `'pages/portfolio/show'`

**Note:** `View::render()` does `str_replace('.', '/', $template)`, so:
- `'pages/home/template'` → `pages/home/template.php` ✅
- `'pages/portfolio/index'` → `pages/portfolio/index.php` ✅
- `'layouts/app'` → `layouts/app.php` ✅

**Task 8: Update `View::partial()` calls in layouts**

- Layouts use `$this->include('partials/sidebar', ...)` — verify this works with the `partial()` method and the new partials path.

**Commit checkpoint** after this phase.

### Phase 4: Create missing frontend pages

**Task 9: Create `src/pages/articles/articles.ts` + `articles.css`**

- `articles.ts`:
  ```typescript
  import './articles.css';
  console.log('Articles page initialized');
  ```
- `articles.css`:
  ```css
  /* Articles page styles */
  @import '../../core/sections/hero/hero.css';
  @import '../../core/sections/features/features.css';
  @import '../../core/components/form.css';  /* Task 12 */
  ```

**Task 10: Create `src/pages/portfolio/portfolio.ts` + `portfolio.css`**

- `portfolio.ts`:
  ```typescript
  import './portfolio.css';
  console.log('Portfolio page initialized');
  ```
- `portfolio.css`:
  ```css
  /* Portfolio page styles */
  .tag {
    /* Move inline styles from PHP templates here */
  }
  ```

**Task 11: Create `src/pages/errors/errors.ts` + `errors.css`**

- `errors.ts`:
  ```typescript
  import './errors.css';
  console.log('Error page initialized');
  ```
- `errors.css`: minimal — import any error-specific styles

**Task 12: Extract `core/components/form.css` + add to `core`**

- Extract from `auth.css`: `.form-group`, `.form-group--row`, `.form-label`, `.form-input`, `.form-hint`, `.checkbox`, `.link`
- Add to `core.ts` import
- Update `auth.css` to `@import '../form.css'` instead of defining locally
- Update `articles.css` to `@import '../form.css'`

**Commit checkpoint** after this phase.

### Phase 5: Update Vite config

**Task 13: Update `vite.config.ts` rollup inputs**

```typescript
input: {
  init:             resolve(__dirname, 'src/init.ts'),
  core:             resolve(__dirname, 'src/core.ts'),
  'page-home':      resolve(__dirname, 'src/pages/home/home.ts'),
  'page-portfolio': resolve(__dirname, 'src/pages/portfolio/portfolio.ts'),
  'page-articles':  resolve(__dirname, 'src/pages/articles/articles.ts'),
  'page-dash':      resolve(__dirname, 'src/pages/dash/dash.ts'),
  'page-auth':      resolve(__dirname, 'src/pages/auth/auth.ts'),
  'page-errors':    resolve(__dirname, 'src/pages/errors/errors.ts'),
  'page-dev':       resolve(__dirname, 'src/dev/theme-switcher.ts'),
},
```

### Phase 6: Clean up old files

**Task 14: Delete old template directories**

After verifying everything works:

```
rm -rf templates/app/
rm -rf templates/blog/
rm -rf templates/cardboard/
```

(Or keep as backup until first successful build + page render.)

---

## Summary: What Changes Where

| What | Before | After |
|---|---|---|
| Home PHP template | `app/home.php` | `pages/home/template.php` |
| Portfolio PHP templates | `app/portfolio.php` + `app/portfolio-show.php` | `pages/portfolio/index.php` + `show.php` |
| Blog PHP templates | `blog/article/*.php` | `pages/articles/*.php` |
| Admin PHP template | `cardboard/dashboard/index.php` | `pages/dash/index.php` |
| Auth PHP template | `cardboard/auth/login.php` | `pages/auth/login.php` |
| 404 template | `app/not-found.php` | `pages/errors/404.php` |
| 500 template | — | `pages/errors/500.php` |
| App layout | `app/layouts/app.php` | `layouts/app.php` |
| Admin layout | `cardboard/layout/base.php` | `layouts/admin.php` |
| Auth layout | `cardboard/auth/base.php` | `layouts/auth.php` |
| Partials | `cardboard/partials/` | `partials/` |
| Article CSS | — | `src/pages/articles/articles.css` |
| Portfolio CSS | — | `src/pages/portfolio/portfolio.css` |
| Error CSS + TS | — | `src/pages/errors/errors.ts` + `.css` |
| Form CSS |embedded in `auth.css` | `core/components/form.css` (global) |
| `PageLayout` | maps flat keys (`home`, `blog`, `cardboard`...) | maps folder names (`home`, `articles`, `dash`...) |
| Body class | mixed (hardcoded + derived) | always `PageLayout::bodyClass($page)` |
| Vite entry for articles | — | `page-articles` |
| Vite entry for errors | — | `page-errors` |

## Risks

- **Template path changes everywhere** — controllers, layouts, partials all reference paths. Must update consistently. One broken path = runtime error.
- **`View::partials()` path resolution** — `$this->include('partials/sidebar')` resolves through the view engine; verify this works from the new location (may need `'partials/sidebar'` without module prefix).
- **Build required** — after Vite config change, need `pnpm build` to generate new manifest entries before PHP can resolve new assets.
- **`pages/home/template.php` naming** — the word "template" in the filename is slightly awkward. Alternative: `pages/home/index.php` with Vite entry `page-home` pointing to `src/pages/home/home.ts`. Consider the redirect.

## Commit Plan

- **Phase 1** (new template files): commit — "feat: create unified pages/ template structure"
- **Phase 2** (layouts + partials): commit — "feat: create unified layouts/ and partials/"
- **Phase 3** (PageLayout + controllers): commit — "refactor: update PageLayout and controllers for new paths"
- **Phase 4** (frontend pages): commit — "feat: add articles, portfolio, errors page entries + extract form.css"
- **Phase 5** (Vite config): commit — "chore: update vite config with new page entries"
- **Phase 6** (cleanup): commit — "chore: remove old app/, blog/, cardboard/ template dirs"
