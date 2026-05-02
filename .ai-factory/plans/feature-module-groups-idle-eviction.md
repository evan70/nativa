# Module Groups + Idle Eviction

**Branch:** `feature/module-groups-idle-eviction`
**Created:** 2026-05-02

---

## Settings

- **Testing:** Yes
- **Logging:** Verbose (DEBUG level)
- **Documentation:** Yes

---

## Overview

Implement a module grouping system that allows:
1. **Route-based binding** — modules are bound only when their routes are matched
2. **Idle eviction** — after configurable timeout, unbound modules are evicted from container
3. **Core groups protection** — core modules (core, routing, database, config, env, errors) are never evicted

---

## Architecture

```
┌─────────────────────────────────────────────────────┐
│                     CORE                            │
│  (core, routing, database, env, config, errors)    │
│  └─ ALWAYS BOUND                                    │
├─────────────────────────────────────────────────────┤
│              MODULE GROUPS                          │
│  ┌───────────┐ ┌───────────┐ ┌────────────┐        │
│  │  ADMIN    │ │  MAIL     │ │  QUEUE     │        │
│  │  /admin/* │ │  mail::* │ │  queue::*  │        │
│  │  idle:5m  │ │  idle:1h │ │  idle:10m  │        │
│  └───────────┘ └───────────┘ └────────────┘        │
└─────────────────────────────────────────────────────┘
                    ↓
        After idle timeout → unbind & free memory
```

---

## Tasks

### Phase 1: Schema & Manifest Updates

#### Task 1: Add group metadata to composer.json schema

Add new `extra.marko` fields in package templates:
- `group`: string — module group identifier
- `routes`: array — route patterns (e.g., `["/admin/*"]`)
- `idleTimeout`: string — duration when idle (e.g., `"5m"`, `"1h"`)
- `isCore`: boolean — core modules never evicted

**Files:**
- `packages/core/templates/package/composer.json`

---

#### Task 2: Extend ModuleManifest with group fields

Add new fields to `ModuleManifest` value object:
- `group: ?string` — optional group identifier
- `routes: array<string>` — route patterns
- `idleTimeout: ?string` — idle timeout duration
- `isCore: bool` — core module flag

**Files:**
- `packages/core/src/Module/ModuleManifest.php`

---

#### Task 3: Update ManifestParser to extract group config

Parse new fields from `composer.json` → `ModuleManifest`:
```php
$group = $composerData['extra']['marko']['group'] ?? null;
$routes = $composerData['extra']['marko']['routes'] ?? [];
$idleTimeout = $composerData['extra']['marko']['idleTimeout'] ?? null;
$isCore = $composerData['extra']['marko']['isCore'] ?? false;
```

**Files:**
- `packages/core/src/Module/ManifestParser.php`

---

### Phase 2: Module Group Manager

#### Task 4: Create ModuleGroupManager service

Create service to manage module groups and eviction:
```php
interface ModuleGroupManagerInterface {
    public function registerGroup(string $moduleName, ModuleGroup $group): void;
    public function markUsed(string $group): void;
    public function getGroupForRoute(string $path): ?string;
    public function evictIfIdle(string $group, Duration $maxIdle): bool;
    public function evictAllIdle(Duration $maxIdle): array;
    public function isCoreGroup(string $group): bool;
}
```

**New Files:**
- `packages/core/src/Module/ModuleGroupManager.php`
- `packages/core/src/Module/ModuleGroupManagerInterface.php`
- `packages/core/src/Module/ModuleGroup.php` (value object)

---

#### Task 5: Create ModuleGroup value object

```php
readonly class ModuleGroup
{
    public function __construct(
        public string $name,
        public string $moduleName,
        public array $routes,          // ["*.admin/*", "/admin/*"]
        public ?Duration $idleTimeout,
        public bool $isCore,
        public DateTimeImmutable $lastUsed,
    ) {}
}
```

**Files:**
- `packages/core/src/Module/ModuleGroup.php`

---

### Phase 3: Container Integration

#### Task 6: Extend Container with unbind capability

Add methods to remove bindings:
```php
public function unbind(string $id): bool;
public function unbindSingleton(string $id): bool;
public function getBindings(): array<string, string|Closure>;
public function getSingletons(): array<string, bool>;
```

**Files:**
- `packages/core/src/Container/Container.php`
- `packages/core/src/Container/ContainerInterface.php`

---

#### Task 7: Integrate group manager in Application bootstrap

Modify `Application::initialize()` to:
1. Create `ModuleGroupManager` after modules are discovered
2. Register group metadata from each module
3. Bind `ModuleGroupManagerInterface` in container

**Files:**
- `packages/core/src/Application.php`

---

### Phase 4: Route-Based Binding

#### Task 8: Route matching for group activation

Add route pattern matching to determine which groups to activate:
```php
public function getGroupForRoute(string $path): ?string {
    foreach ($this->groups as $group) {
        foreach ($group->routes as $pattern) {
            if (fnmatch($pattern, $path)) {
                return $group->name;
            }
        }
    }
    return null;
}
```

**Files:**
- `packages/core/src/Module/ModuleGroupManager.php`

---

#### Task 9: Auto-bind group on first route match

In route dispatcher, before handling request:
1. Match route path to module groups
2. If group not bound yet → bind its bindings
3. Update last-used timestamp

**Files:**
- `packages/routing/src/Router.php` (or add middleware)

---

### Phase 5: Idle Eviction System

#### Task 10: Add RequestHandled event

Create event dispatched after each request:
```php
readonly class RequestHandledEvent
{
    public function __construct(
        public Request $request,
        public Response $response,
    ) {}
}
```

**Files:**
- `packages/core/src/Event/RequestHandledEvent.php`

---

#### Task 11: Implement idle eviction cleanup

Add mechanism to evict idle modules:
1. On `RequestHandled` event → update last-used for activated groups
2. On shutdown or timer → call `evictAllIdle()`
3. Unbind module bindings and clear singleton instances

```php
public function evictIfIdle(string $group, Duration $maxIdle): bool {
    if ($this->isCoreGroup($group)) {
        return false;
    }
    
    $lastUsed = $this->groups[$group]->lastUsed;
    $idle = $lastUsed->diff(new DateTimeImmutable());
    
    if ($idle >= $maxIdle) {
        $this->evictGroup($group);
        return true;
    }
    
    return false;
}
```

**Files:**
- `packages/core/src/Module/ModuleGroupManager.php`

---

### Phase 6: Configuration

#### Task 12: Define core groups

Hardcode core groups that are never evicted:
```php
const CORE_GROUPS = ['core', 'routing', 'database', 'config', 'env', 'errors'];
```

OR auto-detect from module names:
- `marko/core` → core
- `marko/routing` → routing  
- `marko/database*` → database
- `marko/config` → config
- `marko/env` → env
- `marko/errors*` → errors

**Files:**
- `packages/core/src/Module/ModuleGroupManager.php`

---

#### Task 13: Add global config for eviction

Add config options:
```php
// config/module.php
return [
    'eviction' => [
        'enabled' => true,
        'checkInterval' => '1m',      // how often to check
        'defaultIdleTimeout' => '5m', // default for groups without idleTimeout
    ],
];
```

**Files:**
- `config/module.php`

---

### Phase 7: Documentation

#### Task 14: Document module grouping

Add documentation for:
- How to configure module groups in composer.json
- How idle eviction works
- Example configurations

**Files:**
- `.ai-factory/DESCRIPTION.md` (update)
- `packages/core/docs/module-groups.md` (new)

---

## Commit Plan

| Commit | Tasks | Message |
|--------|-------|---------|
| 1 | 1-3 | feat(core): add group metadata schema to ModuleManifest |
| 2 | 4-5 | feat(core): create ModuleGroup value object and manager |
| 3 | 6-7 | feat(core): extend Container with unbind and integrate manager |
| 4 | 8-9 | feat(core): implement route-based module binding |
| 5 | 10-13 | feat(core): implement idle eviction system |
| 6 | 14 | docs: document module grouping configuration |

---

## Notes

- Default idle timeout: 5 minutes (configurable per group)
- Core modules: always bound, never evicted
- On-demand re-bind: if route matches after eviction, re-bind automatically
- Memory savings estimate: ~1MB for unused groups