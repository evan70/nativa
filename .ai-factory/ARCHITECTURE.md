# Architecture Decisions

## Frontend Architecture (Vanilla Cards)

### Section-Based Architecture
The frontend uses a section-based architecture where each distinct visual section of a page is encapsulated in a TypeScript class extending `BaseSection`.

#### Key Principles:
1. **BEM Methodology**: All CSS follows Block Element Modifier naming
2. **Vanilla JS/TS**: No frontend frameworks, pure TypeScript
3. **Auto-instantiation**: Sections are automatically loaded via `SectionLoader` using `data-section` attributes
4. **Modularity**: Each section is self-contained with its own styles and logic

#### Section Structure:
- **TS Class**: `/src/core/sections/[section-name]/[SectionName]Section.ts`
- **CSS**: `/src/core/sections/[section-name]/[section-name].css`
- **HTML**: Uses `data-section="[section-name]"` attribute on container element
- **BEM Naming**: 
  - Block: `[section-name]` (e.g., `hero-section`)
  - Element: `[section-name]__[element]` (e.g., `hero-section__title`)
  - Modifier: `[section-name]--[modifier]` (e.g., `hero-section--dark`)

#### Current Sections:
- HeroSection (`hero-section`)
- CTASection (`cta-section`)
- FeaturesSection (`features-section`)
- StatsSection (`stats-section`)
- NavbarSection (`navbar-section`)

### Backend Architecture (Admin Sections)

Admin sections are PHP-based and use attributes for registration.

#### Key Principles:
1. **Attribute-Based Registration**: Uses `#[AdminSection]` attribute
2. **Interface Implementation**: Implements `AdminSectionInterface`
3. **Menu Integration**: Sections automatically appear in admin menu
4. **Permission-Based**: Sections and menu items can require specific permissions

#### Admin Section Structure:
- **PHP Class**: `/modules/[module]/src/Admin/[SectionName]AdminSection.php`
- **Attributes**: 
  - `#[AdminSection(id: '...', label: '...', icon: '...', sortOrder: ...)]`
  - Optional: `#[AdminPermission(id: '...', label: '...')]` for declaring permissions
- **Methods**:
  - `getId(): string`
  - `getLabel(): string`
  - `getIcon(): string`
  - `getSortOrder(): int`
  - `getMenuItems(): array`

### Technology Stack
- **Language**: PHP 8.5+, TypeScript
- **Framework**: Marko Framework
- **Build Tool**: Vite (for frontend assets)
- **Package Manager**: pnpm
- **Styling**: CSS with CSS Custom Properties, BEM methodology