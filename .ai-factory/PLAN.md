# Implementation Plan: Performance Optimization

Branch: none (fast mode)
Created: 2026-05-06

## Settings
- Testing: no (performance refactor, visual verification)
- Logging: verbose
- Docs: no

## Lighthouse Findings

### Critical Issues

**1. Cache TTL = None (118 KiB savings)**
All static assets (`Cache-Control: none`) — PHP built-in server doesn't send cache headers. Need to add `Cache-Control` headers for static assets in `public/index.php`.

**2. Render blocking CSS (630ms)**
`core-DLaBKUti.css` (4.3 kB) and `page-home-C8WX4V9m.css` (1.3 kB) block initial render. All CSS is loaded via `<link>` in `<head>`.

**3. No preconnect for self-origin**
Only `res.cloudinary.com` is preconnected. Self-origin (trycloudflare.com) missing.

**4. Font preload missing**
`Inter-Regular.woff2` (109 KiB) loaded late — no `<link rel="preload">` for fonts.

**5. defineProperty.js loaded as separate chunk (611ms critical path)**
Shared chunk loaded separately — should be inlined or bundled with core.

## Commit Plan

- **Commit 1** (tasks 1-2): `perf(server): add cache headers + preconnect self-origin`
- **Commit 2** (tasks 3-4): `perf(assets): preload fonts + inline critical CSS`
- **Commit 3** (task 5): `perf(build): bundle defineProperty into core`

## Tasks

### Phase 1: Server + Head Optimization

#### Task 1: Add cache headers for static assets in `public/index.php`

Add `Cache-Control` header based on file type:
- Fonts (woff2, woff, ttf): 1 year (immutable, hashed filename)
- JS/CSS (hashed): 1 year
- Images: 30 days
- HTML/manifest: no-cache

```php
$cacheRules = [
    'woff2' => 'public, max-age=31536000, immutable',
    'woff'  => 'public, max-age=31536000, immutable',
    'ttf'   => 'public, max-age=31536000, immutable',
    'css'   => 'public, max-age=31536000, immutable',
    'js'    => 'public, max-age=31536000, immutable',
    'json'  => 'public, max-age=31536000, immutable',
    'png'   => 'public, max-age=2592000',
    'jpg'   => 'public, max-age=2592000',
    'jpeg'  => 'public, max-age=2592000',
    'gif'   => 'public, max-age=2592000',
    'svg'   => 'public, max-age=2592000',
    'webp'  => 'public, max-age=2592000',
    'ico'   => 'public, max-age=2592000',
    'map'   => 'public, max-age=31536000, immutable',
];
```

**Files:** `public/index.php`

---

#### Task 2: Add preconnect for self-origin in layout templates

Add `<link rel="preconnect" href="/">` or dynamic origin detection:

```php
$origin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
?>
<link rel="preconnect" href="<?= $origin ?>" crossorigin>
```

**Files:** `templates/app/layouts/app.php`, `templates/cardboard/layout/base.php`, `templates/cardboard/auth/base.php`

---

### Phase 2: Asset Loading Optimization

#### Task 3: Preload critical fonts in layout templates

Add font preload for Inter Regular (used by body text):

```php
<link rel="preload" href="<?= View::resolve('assets/fonts/inter/Inter-Regular.woff2') ?>" as="font" type="font/woff2" crossorigin>
```

**Files:** `templates/app/layouts/app.php`, `templates/cardboard/layout/base.php`, `templates/cardboard/auth/base.php`

---

#### Task 4: Inline critical CSS for above-the-fold content

Extract critical CSS (reset + fonts + colors + navbar) and inline it in `<head>`. Load full `core` CSS asynchronously.

Approach: Create `core/tokens/critical.css` that imports only above-the-fold styles, inline it as `<style>` tag.

```php
// In layout head:
<style><?= file_get_contents($viewsPath . '/core/tokens/critical.css') ?></style>
<?= View::vite('core') ?> // loaded normally but non-blocking via media trick
```

**Files:** `templates/src/core/tokens/critical.css` (new), layout templates

---

### Phase 3: Build Optimization

#### Task 5: Bundle defineProperty chunk into core

`defineProperty-CV_TUbzF.js` (0.62 kB) is a separate chunk loaded on critical path. Configure Vite to inline small chunks or bundle them into core entry.

In `vite.config.ts`, set `manualChunks` to keep shared deps in core:

```ts
rollupOptions: {
  output: {
    manualChunks: undefined, // or inline threshold
  }
}
```

Or use `build.cssCodeSplit: false` to bundle all CSS into one file (already done).

**Files:** `templates/vite.config.ts`

---

## Expected Outcome

| Metric | Before | After |
|--------|--------|-------|
| Cache TTL | None | 1 year (assets), 30 days (images) |
| Preconnect | 1 origin | 2 origins (+ self) |
| Font preload | None | Inter-Regular preloaded |
| Critical CSS | 4.3 kB blocking | Inlined, non-blocking |
| defineProperty chunk | Separate (611ms) | Bundled with core |
