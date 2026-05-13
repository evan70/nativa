# Plan: Cardboard Package

**Branch:** feature/cardboard-package
**Created:** 2026-05-13
**Mode:** Full

---

## Settings

- **Testing:** No (TypeScript package structure)
- **Logging:** Minimal (structure setup)
- **Docs:** No (can be added later)

---

## Overview

Create a new TypeScript package `@nativa/cardboard` in `templates/packages/` that will contain the article-editor component and potentially other UI components in the future. This follows the existing package patterns (theme-fire-show, theme-neon).

---

## Tasks

### Phase 1: Package Setup

1. **Create package directory structure**
   - Create: `templates/packages/cardboard/`
   - Files: package.json, tsconfig.json, index.ts
   - Follow pattern of `@nativa/theme-fire-show`

2. **Create package.json**
   - Name: `@nativa/cardboard`
   - Exports: `./src/index.ts`, `./src/article-editor/*`
   - Dependencies: htmx.org (from root package.json)

3. **Create TypeScript configuration**
   - Extend root tsconfig.json
   - Set appropriate outDir and declarations

### Phase 2: Article Editor Integration

4. **Move article-editor to cardboard**
   - Create: `templates/packages/cardboard/src/article-editor/`
   - Move existing files from `templates/packages/article-editor/`
   - Files: controller.ts, render.ts, types.ts, autosave.ts, etc.

5. **Update package exports**
   - Export article-editor components from cardboard
   - Keep backward compatible paths if needed

6. **Update workspace configuration**
   - Add `@nativa/cardboard` to pnpm-workspace.yaml

### Phase 3: Integration

7. **Update root package.json**
   - Add `@nativa/cardboard` as workspace dependency
   - Use in vite build process

8. **Update vite.config.ts**
   - Ensure cardboard package is bundled correctly

9. **Test build**
   - Run `pnpm build` to verify package works

---

## Commit Plan

- **Commit 1:** Create cardboard package structure with package.json and tsconfig
- **Commit 2:** Move article-editor files into cardboard/src/article-editor
- **Commit 3:** Update exports and workspace configuration
- **Commit 4:** Test build and verify integration

---

## Notes

- Article-editor files already exist in `templates/packages/article-editor/` but are not a proper package
- Package naming follows `@nativa/*` pattern like themes
- Future: can add more components to cardboard (not just article-editor)