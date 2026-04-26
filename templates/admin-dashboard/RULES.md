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
- Use TypeScript for all logic.
- Sections must be auto-instantiated via the `SectionLoader` and `data-section` attributes.
- Keep the core bundle small; put page-specific logic in dedicated entry points.

## GitHub Workflow
- GitHub Actions is used for CI/CD.
- Every push to the main branch is automatically deployed to GitHub Pages.
- Use Pull Requests for all changes.
- PR descriptions should be clear and follow the provided template.
