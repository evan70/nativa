# Plan: ProjectPaths Templates Integration

## Overview

Unified path handling using `ProjectPaths` class instead of manual `dirname(__DIR__, N)` calculations. This ensures consistent template resolution across the application.

## Branch
`refactor/path-handling`

## Settings
- Testing: No
- Logging: Standard
- Docs: No

## Tasks

### Task 1: Extend ProjectPaths with templates property
**File:** `packages/core/src/Path/ProjectPaths.php`

Add `templates` property to centralize template path resolution:

```php
public string $templates;

public function __construct(?string $basePath = null) {
    $this->base = $basePath ?? getcwd();
    $this->templates = $this->base . '/templates';
    // ... rest of constructor
}
```

**Logging:** Log template path initialization in Application boot.

---

### Task 2: Update Router.php to use ProjectPaths
**File:** `packages/routing/src/Router.php`

Inject `ProjectPaths` and use it for 404 template resolution instead of `dirname(__DIR__, N)`:

```php
public function __construct(
    RouteCollection $routes,
    ContainerInterface $container,
    array $globalMiddleware = [],
) {
    // ... existing code
}

private function renderNotFoundResponse(Request $request): Response
{
    $template = 'pages/errors/404';
    // Use ProjectPaths instead of hardcoded dirname
    $viewPath = $this->container->get(ProjectPaths::class)->templates . '/' . $template . '.php';
    // ... rest
}
```

**Logging:** Log 404 page render with resolved path (DEBUG level).

---

### Task 3: Update View package TemplateResolver to use ProjectPaths
**Files:** 
- `packages/view/src/ModuleTemplateResolver.php`
- `packages/view/src/TemplateResolverInterface.php`

Ensure all template resolvers consistently use `ProjectPaths` for base paths.

**Logging:** Log template resolution path (DEBUG level).

---

### Task 4: Verify all path calculations use ProjectPaths
**Files:** Search and update any remaining hardcoded path calculations.

Use grep to find remaining `dirname(__DIR__` patterns that reference templates:

```bash
grep -rn "dirname.*templates" --include="*.php" | grep -v vendor
```

---

## Commit Plan

Single commit at the end:
```
refactor: unify path handling with ProjectPaths

- Add templates property to ProjectPaths
- Replace dirname(__DIR__, N) in Router with ProjectPaths injection
- Ensure TemplateResolver uses ProjectPaths consistently
```