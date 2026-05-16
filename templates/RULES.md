# Project Rules and Guidelines

This document outlines the rules and guidelines for development in the Vanilla Cards project.

## General Principles
- Use pnpm for package management.
- All code should be written in English.
- No frontend frameworks; use pure Vanilla JS and CSS.
- Maintain a modular architecture.

## CSS Rules
- Strictly follow BEM (Block Element Modifier) methodology.
- Use CSS custom properties for styling from the `unified.css` file.
- Do not use deep nesting in CSS.

## JavaScript/TypeScript Rules
- **NO inline code in templates** — All JavaScript must be in TypeScript files. No `<script>` tags (except `View::viteJs()`), no inline event handlers (`onclick`, `onsubmit`, `onchange`, etc.).
- Use TypeScript for all logic.
- Sections must be auto-instantiated via the `SectionLoader` and `data-section` attributes.
- Keep the core bundle small; put page-specific logic in dedicated entry points.
- **Event handling:** Use simple `click` listeners only. Do NOT use `touchstart` + `preventDefault()` — it blocks Android Chrome touch handling. Do NOT use delegated document-level listeners for navbar/theme — put handlers directly in `NavbarSection`.
- **Vite target:** Always use `target: 'es2015'` in `vite.config.ts`. `es2020` generates optional chaining (`?.`), arrow functions, and `class` syntax that older Android Chrome versions (Android 9+) do not support.

## GitHub Workflow
- GitHub Actions is used for CI/CD.
- Every push to the main branch is automatically deployed to GitHub Pages.
- Use Pull Requests for all changes.
- PR descriptions should be clear and follow the provided template.
