# Plan: Vanilla Cards Cardboard Package Development

**Branch:** feature/vanilla-cards-cardboard
**Created:** 2026-05-13
**Mode:** Full

---

## Settings

- **Testing:** Yes, Vitest for TypeScript
- **Logging:** Verbose (INFO for key events, DEBUG for details)
- **Docs:** Yes, README.md in package

---

## Overview

The `@nativa/cardboard` package is a new TypeScript package in the vanilla-cards ecosystem that provides UI components for the Nativa CMS. Currently it contains only the article-editor component. This plan outlines the roadmap for expanding and improving the package.

**Current State:**
- Package created at `templates/packages/cardboard/`
- Contains article-editor with: controller, render, types, autosave, media, preview, normalizers, submit
- Basic package.json with htmx.org dependency
- Not fully integrated with the build system yet

**Goal:** Build a comprehensive UI component library for vanilla-cards using the established architecture:
- **Tokens**: Use design tokens from `templates/src/core/tokens/` (colors, fonts, spacing, layout)
- **Components**: Reusable UI components in `templates/src/core/components/` (button, form, card, etc.)
- **Sections**: Page sections built from components in `templates/src/core/sections/` (hero, navbar, features, etc.)
- **Architecture**: Follow BEM methodology, use CSS custom properties, SectionLoader for auto-instantiation

---

## Tasks

### Phase 1: Package Foundation

0. **Add missing build dependencies to package.json**
   - Add vite, vitest, and TypeScript as devDependencies
   - Update package.json with proper scripts (build, test, typecheck)
   - Set up peerDependencies for htmx.org
   - Files: `templates/packages/cardboard/package.json`

   **Note:** Task #0 (dependencies) must complete before Tasks #1 and #3.

1. **Setup proper build configuration**
   - Configure Vite to build cardboard as a library
   - Add proper entry points (main, module, types)
   - Set up declaration files for TypeScript
   - Fix tsconfig.json to not extend from non-existent base config
   - Files: `templates/packages/cardboard/vite.config.ts`, `templates/packages/cardboard/tsconfig.json`

2. **Update package.json with proper exports**
   - Add `types` field for TypeScript
   - Define clear export paths for each component
   - Add sideEffects: false for tree-shaking
   - File: `templates/packages/cardboard/package.json`

3. **Add testing infrastructure**
   - Install Vitest
   - Create test setup
   - Add test scripts
   - Files: `templates/packages/cardboard/vitest.config.ts`, `tests/`

### Phase 2: Article Editor Improvements

4. **Fix TypeScript compilation errors + NotificationService API compatibility**
   - Fix `NotificationService` import - use `core/components/NotificationManager.ts` instead of @kernel
   - **IMPORTANT:** NotificationManager has different API (`static show()` with options object) vs NotificationService (`static .error()`, `.showConfirm()`, `.queueForNextPage()`). Create a wrapper or adapter file for backward compatibility:
     - `src/utils/NotificationAdapter.ts` - bridges old NotificationService API to NotificationManager
   - Fix type issues in controller.ts
   - Ensure all types are properly exported
   - Use vanilla-cards tokens (colors, fonts) from core/tokens
   - Use existing form component styles from core/components/form.css
   - Files: `templates/packages/cardboard/src/article-editor/controller.ts`, `templates/packages/cardboard/src/utils/NotificationAdapter.ts`

5. **Integrate with vanilla-cards tokens**
   - Use CSS custom properties for theming (colors, spacing, typography)
   - Follow BEM naming convention for editor styles
   - Create `src/article-editor/styles.css` that:
     - Imports `@nativa/tokens/unified.css` (via build step or copy)
     - Defines editor-specific BEM classes: `.article-editor`, `.article-editor__toolbar`, `.article-editor__content`, etc.
     - Uses token variables: `--color-brand`, `--font-sans`, `--space-4`, `--shadow-md`, etc.
     - Extends core form styles from `form.css`
   - Create editor-specific token overrides if needed (e.g., `--editor-toolbar-bg`)
   - Files: `templates/packages/cardboard/src/article-editor/styles.css`

   **Depends on:** Task #4 (must complete first)

6. **Improve markdown preview**
   - Configure marked with proper options
   - Add syntax highlighting for code blocks
   - Support GFM (GitHub Flavored Markdown)
   - Use component styles for code blocks
   - Files: `templates/packages/cardboard/src/article-editor/preview.ts`

7. **Enhance media handling**
   - Add image optimization options
   - Add lazy loading for media gallery
   - Improve drag-and-drop experience
   - Use core/components for image display
   - Files: `templates/packages/cardboard/src/article-editor/media.ts`

8. **Add autosave improvements**
   - Add offline support detection
   - Add conflict resolution
   - Add draft cleanup utilities
   - Use localStorage for draft persistence
   - Files: `templates/packages/cardboard/src/article-editor/autosave.ts`

### Phase 3: New Components (using core tokens & components)

9. **Create shared UI utilities**
   - Create common types and interfaces in `src/utils/types.ts`
   - Add utility functions:
     - `src/utils/dom.ts` - DOM helpers (query, create, events)
     - `src/utils/events.ts` - Event delegation, debounce, throttle
     - `src/utils/api.ts` - HTTP request helpers
   - Create base component class:
     - `src/base/BaseComponent.ts` - base class extending core sections pattern (extends BaseSection)
     - `src/base/index.ts` - exports
   - Follow SectionLoader auto-instantiation pattern
   - Files: `templates/packages/cardboard/src/utils/`, `templates/packages/cardboard/src/base/`

   **Depends on:** Task #5 (tokens must be in place)

10. **Create ArticleEditorSection**
    - Convert ArticleEditor to Section pattern
    - Use data-section attribute for auto-instantiation
    - Use core/components for form, button, notification
    - Use core/tokens for styling
    - Files: `templates/packages/cardboard/src/sections/article-editor/`

11. **Create FormBuilder component**
    - Reusable form field components
    - Validation integration
    - Dynamic form generation
    - Use `src/base/BaseComponent.ts` as base class
    - Use core/components/form.css base styles
    - Use core/tokens for form tokens
    - Files: `templates/packages/cardboard/src/form-builder/`

    **Depends on:** Task #9 (shared utils)

12. **Create Modal component**
    - Accessible modal dialogs
    - Stack management for multiple modals
    - Animation support
    - Use core tokens for modal styling
    - Files: `templates/packages/cardboard/src/modal/`

13. **Create DataTable component**
    - Sortable columns
    - Pagination
    - Row selection
    - Inline actions
    - Use core/components/admin-table.js as base
    - Files: `templates/packages/cardboard/src/data-table/`

### Phase 4: Integration & Polish

14. **Create theme integration**
    - Define CSS custom properties for theming using core/tokens
    - Create base component styles extending core/components
    - Document theming approach
    - Ensure compatibility with theme-fire-show and theme-neon
    - Files: `templates/packages/cardboard/src/styles/`

15. **Add accessibility features**
    - ARIA labels and roles
    - Keyboard navigation
    - Focus management
    - Screen reader support
    - Follow core/components accessibility patterns

    **Depends on:** Task #9 (base components)

16. **Update documentation**
    - Create README.md with usage examples
    - Document all components and their APIs
    - Document token usage (colors, fonts, spacing)
    - Add migration guide from old article-editor
    - File: `templates/packages/cardboard/README.md`

17. **Integration testing**
    - Test with themes (fire-show, neon)
    - Test with different content types
    - Cross-browser testing
    - Verify tokens work across themes
    - File: `templates/packages/`

---

## Commit Plan

- **Commit 1:** Add build dependencies, setup Vite/Vitest config, fix TypeScript errors with NotificationAdapter
- **Commit 2:** Add testing infrastructure and integrate article-editor with vanilla-cards tokens/CSS
- **Commit 3:** Create shared utilities (utils/, base/) and ArticleEditorSection with SectionLoader pattern
- **Commit 4:** Add FormBuilder and Modal components using core tokens/components
- **Commit 5:** Add DataTable component using admin-table.js pattern
- **Commit 6:** Add theme integration and accessibility features
- **Commit 7:** Finalize documentation and README
- **Commit 8:** Integration testing with themes

---

## Notes

- **Follow vanilla-cards architecture**:
  - Use tokens from `templates/src/core/tokens/` (colors, fonts, layout, spacing)
  - Use components from `templates/src/core/components/` (button, form, card, notification)
  - Use sections pattern from `templates/src/core/sections/` (SectionLoader, BaseSection)
  - Follow BEM methodology for CSS
  - Use CSS custom properties for theming
- Use vanilla TypeScript (no framework dependencies except htmx)
- Keep bundle size small (< 50KB gzipped for core)
- Maintain backward compatibility with existing article-editor usage
- Consider tree-shaking for unused components
- Document breaking changes if any
- Use Vite with `target: 'es2015'` for Android compatibility

---

## Dependencies

- htmx.org (already in package.json)
- marked (for markdown preview - currently using via global)
- Optional: highlight.js for code syntax highlighting

---

## Integration Points

- **Tokens**: `templates/src/core/tokens/` - colors.css, fonts.css, layout-grid.css, unified.css
- **Components**: `templates/src/core/components/` - button.css, form.css, card-grid.css, notification.css
- **Sections**: `templates/src/core/sections/` - SectionLoader.ts, BaseSection.ts, hero/, navbar/, features/