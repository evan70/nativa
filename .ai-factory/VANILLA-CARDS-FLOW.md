# Vanilla Cards Flow - Template Frontend Architecture

## Quick Overview

Nativa uses **Vanilla Cards** - a pure Vanilla JS + CSS architecture with BEM methodology for frontend templates.

## Directory Structure

```
templates/
├── src/                      # Frontend source (TypeScript/CSS)
│   ├── core/                 # Core components, tokens, sections
│   │   ├── components/       # Reusable UI components (button, card, form, etc.)
│   │   ├── sections/         # Page sections with auto-initialization
│   │   ├── tokens/           # CSS variables (colors, spacing, typography)
│   │   └── utilities.css     # Global utilities
│   ├── pages/                # Page-specific entry points
│   │   ├── home/
│   │   ├── dash/
│   │   ├── articles/
│   │   └── ...
│   └── init.ts               # Theme initialization
├── pages/                    # PHP view templates
│   ├── layouts/              # Layout files (admin.php, app.php, auth.php)
│   ├── partials/             # Reusable partials (sidebar, navbar, flash)
│   └── pages/                # Page templates (home, dash, articles...)
├── packages/                 # Additional packages (cardboard, etc.)
└── vite.config.ts           # Build configuration
```

## Flow: Adding a New Page

### 1. Create TypeScript Entry Point

```typescript
// templates/src/pages/about/about.ts
import './about.css';

console.log('About page initialized');

document.addEventListener('DOMContentLoaded', () => {
  // Page-specific logic here
});
```

### 2. Register in Vite Config

```typescript
// templates/vite.config.ts
input: {
  // ...existing entries...
  'page-about': resolve(__dirname, 'src/pages/about/about.ts'),
}
```

### 3. Create PHP View

```php
<?php
// templates/pages/about/index.php
$this->layout('layouts.app');
?>

<?php $this->section('content') ?>
<h1>About</h1>
<p>Welcome to the about page.</p>
<?php $this->endSection() ?>
```

### 4. Build & Deploy

```bash
cd templates && pnpm run build
```

Build output goes to `public/dist/` with cache-busted filenames. A `manifest.json` is generated for PHP asset loading.

## Asset Loading in PHP

The PHP backend uses `AssetHelper` to load assets:

```php
<?php
// In layout or view
echo AssetHelper::entryCssTags('page-about', 'vanilla-cards');
echo AssetHelper::js('page-about', 'vanilla-cards');
?>
```

This reads the manifest and outputs the correct hashed filenames.

## Section Auto-Initialization

Sections use the `SectionLoader` pattern for automatic instantiation:

```html
<div class="hero" data-section="hero">
  <!-- Content -->
</div>
```

```typescript
// templates/src/core/sections/SectionLoader.ts
// Automatically finds all [data-section] elements and initializes them
```

## Key Principles

1. **BEM Methodology**: Block__Element--Modifier
   - `.card`, `.card__title`, `.card--featured`

2. **CSS Custom Properties**: Use tokens from `core/tokens/`
   - `var(--color-brand)`, `var(--space-4)`, `var(--font-sans)`

3. **No Framework Dependencies**: Pure Vanilla JS + CSS

4. **Tree-Shaking**: Each page loads only its specific JS/CSS

5. **Mobile-First**: CSS uses responsive breakpoints (768px, 1024px)

## Useful Commands

```bash
cd templates

# Development server (hot reload)
pnpm run dev

# Production build
pnpm run build

# Preview production build
pnpm run preview
```

## Adding a New Component

1. Create TypeScript class in `src/core/components/`
2. Add CSS with BEM in appropriate token file or page CSS
3. Use in template with `data-section` attribute for auto-init

## Key Files

| File | Purpose |
|------|---------|
| `vite.config.ts` | Build config, entry points |
| `src/core.ts` | Core bundle entry |
| `src/init.ts` | Theme initialization |
| `src/core/tokens/unified.css` | All CSS tokens |
| `src/core/sections/SectionLoader.ts` | Auto-initialization |
| `templates/layouts/admin.php` | Admin layout template |