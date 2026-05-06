# Plán: Pokračovanie Front + Dashboard

## Stav (hotové)
- [x] Dual-build Vite (front/dash)
- [x] PHP View/ViewAdapter s manifest resolution
- [x] Dashboard core + page-specific split
- [x] Chart.js integration
- [x] Front home page sections (hero, features, stats, cta)
- [x] Theme FOUC prevention
- [x] Auth layout s vlastnými štýlami

---

## Fáza 1: Hotfixy a konsolidácia

### 1.1 `cardboard.css` → legacy migration
**Problém:** `dashboard.css` importuje `./core.css`, ale Vite duplikuje core CSS v oboch entry pointoch (`style` a `dashboard-style`).

**Riešenie:** Refactor na shared chunk:
```css
/* dashboard.css — už neimportuj core.css */
/* core.css sa načíta len raz cez <link rel="stylesheet"> v layoute */
```

**Úlohy:**
- [ ] Presunúť page-unikátne štýly z `cardboard.css` do `dashboard.css` alebo príslušných page súborov
- [ ] Odstrániť `@import './core.css'` z `dashboard.css` (core sa načíta v layoute)
- [ ] Vymazať `cardboard.css` alebo premenovať na `__legacy.css` označený k vymazaniu

### 1.2 Dashboard CSS vars — pridat custom props fallback
**Problém:** `--color-surface`, `--color-border` atď. sú definované v `core.css` a `css.ts`, ale dashboard nepoužíva `css.ts` import.

**Riešenie:** Zabezpečiť konzistentné CSS custom properties medzi front a dash.
- [ ] Vytvoriť `templates/src/core/tokens/base.css` — základné CSS vars zdieľané front aj dash
- [ ] Importovať v `core.css` cez `@import '../core/tokens/base.css'`

### 1.3 `View::resolve()` vs `View::vite()` nejednotnosť
**Problém:** `resolve()` fallback na base cestu nefunguje správne ak manifest chýba.

**Riešenie:**
- [ ] Logovať warning ak manifest chýba (dev only, nie prod)
- [ ] Unifikovať `findByName()` logiku medzi `resolve()` a `vite()`

---

## Fáza 2: Dashboard pages

### 2.1 Dashboard Home (Stats Overview)
**Súbory:**
- `templates/cardboard/dashboard/index.php` — stat cards + 3 chart containers
- `templates/src/cardboard/pages/dashboard/dashboard.ts` — už existuje

**Potrebné:**
- [ ] Vytvoriť blade/PHP template so stat cards grid
- [ ] Pridať data attributes pre chart IDs (`#revenue-chart`, `#subscriptions-chart`, `#sales-chart`)
- [ ] Dummy data → načítavanie cez fetch z API

### 2.2 Admin Tables (CRUD list)
**Súbory:**
- `templates/cardboard/admin/users/index.php`
- `templates/cardboard/admin/articles/index.php`

**Komponenty:**
- [ ] Vytvoriť reusable `admin-table` template
- [ ] Sorting (klient/server)
- [ ] Pagination
- [ ] Search / filters
- [ ] Bulk actions (checkbox + toolbar)

### 2.3 Form Pages
**Súbory:**
- `templates/cardboard/admin/users/create.php`
- `templates/cardboard/admin/users/edit.php`

**Komponenty:**
- [ ] Form validation (client + server)
- [ ] Upload widget (drag & drop, progress)
- [ ] Reusable form fields (input, select, textarea, toggle)

---

## Fáza 3: Front pokračovanie

### 3.1 Home page — interaktivita
- [ ] Hero carousel / slider (ak je viac ako 1 featured post)
- [ ] Stats counter animation (IntersectionObserver)
- [ ] Smooth scroll navigation
- [ ] Lazy load images (kontrolovať či Vite handluje `loading="lazy"`)

### 3.2 Blog pages
- [ ] Article listing design (grid / masonry)
- [ ] Article detail page (reading time, tags, related posts)
- [ ] Comment section UI

### 3.3 Contact / Newsletter
- [ ] Newsletter subscription form
- [ ] Contact page

---

## Fáza 4: DX a tooling

### 4.1 Dev server auto-reload
- [ ] `pnpm dev` aktuálne beží len pre front — pridať `--mode dash` alebo paralelný server

### 4.2 Build pipeline
- [ ] GitHub Actions pre build a deploy statických assetov
- [ ] `pnpm build:all` ako CI krok
- [ ] Cache invalidation (manifest hash už funguje)

### 4.3 CSS lint / format
- [ ] Pridať Stylelint pre BEM konzistenciu
- [ ] Prefixy pre staršie browsery (Vite autoprefixer?)

---

## Priority

| # | Úloha | Odhad | Blokuje |
|---|-------|-------|---------|
| 1 | CSS var unifikácia + cardboard.css cleanup | 1h | dashboard nové pages |
| 2 | Dashboard Home template (stats + charts) | 2h | — |
| 3 | Admin table komponent | 3h | CRUD pages |
| 4 | Form page templates | 2h | — |
| 5 | Home page interaktivita | 2h | — |
| 6 | Blog listing/detail | 3h | — |
| 7 | DX tooling | 3h | — |

**Odporúčaný start:** Úlohy 1+2 (5h) → máš okamžite funkčný dashboard s dátami.
