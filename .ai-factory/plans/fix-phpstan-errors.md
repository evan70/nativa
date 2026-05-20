# Plan: Fix PHPStan level max errors (428 errors)

**Date:** 2026-05-20
**Branch:** (no branch — fast mode, apply to current state)

## Overview

Fix 428 PHPStan level-max errors across 14 files. Errors fall into 5 categories:

## Root Cause Analysis

### Category A: `$container->get()` returns `mixed` (85% of errors)
PSR-11 `ContainerInterface::get()` returns `mixed`. When `module.php` closures call `$container->get(SomeInterface::class)`, PHPStan sees `mixed` and cascades to:
- `argument.type` — passing mixed to typed constructor params
- `method.nonObject` — calling methods on mixed
- `offsetAccess.nonOffsetAccessible` — accessing offsets on mixed
- `property.nonObject` — accessing properties on mixed
- `foreach.nonIterable` — iterating over mixed

**Fix:** Add `/** @var Type $var */` annotations before each `$container->get()` call to inform PHPStan of the expected return type.

### Category B: PHPStan `excludePaths` glob not matching nested test files
Current config: `modules/*/tests` — glob `*` doesn't match `/`, so `modules/blog/tests/ArticleControllerTest.php` is NOT excluded. Same for all nested Pest/PHPUnit test files.

**Fix:** Update `excludePaths` patterns to properly exclude recursive test directories.

### Category C: Missing class `DefaultUserProvider`
`modules/controllers/module.php:44` references `Marko\Authentication\DefaultUserProvider` which doesn't exist.

**Fix:** Either remove the preference (mark module already provides `UserProviderInterface` binding via `MarkProvider`), or create the missing class.

### Category D: Too-strict array shape types
`ArticleSeeder.php` lines 169, 177 — `??` and `isset()` on offsets that shape types say always exist.

**Fix:** Widen the array shape annotations so nullable access is valid.

### Category E: Misc type issues
- `ArticleServiceTest.php` — unknown `LoggerInterface` class (wrong namespace)
- `FileCacheTest.php` — `glob()` returning `list<string>|false` not handled
- `ModuleGroupTest.php` — `createModuleJson()` missing `@param` value type

## Tasks

### Phase 1: Fix PHPStan config (fixes ~280 test-file errors)

**Task 1.1:** Update `phpstan.neon` to properly exclude test directories recursively
- Change `modules/*/Seed` → `modules/*/Seed/**`
- Change `modules/*/tests` → `modules/*/tests/**`
- Verify by running `php ./vendor/bin/phpstan analyse --level=max --error-format=raw 2>&1 | grep "Found"`

**Impact:** This eliminates ALL errors from these test files (drops from ~428 to ~100-120 errors).

### Phase 2: Fix `$container->get()` returning mixed in module.php files

**Task 2.1:** Fix `modules/database-sqlite/module.php`
- Lines 18-19: `$config = include ...` returns mixed → add `/** @var array{database: string} $config */`
- Line 21: `$config['database'] ?? ':memory:'` → fixed by above
- Line 25-26: same pattern
- Line 30-31: `$container->get(ConnectionInterface::class)` → add `/** @var SqliteConnection $connection */`

**Task 2.2:** Fix `modules/blog/module.php`
- Line 21: `$container->get(ModuleDatabaseResolverInterface::class)` → add `@var` annotation
- Line 27: `$container->get(BlogConnection::class)->getConnection()` → add `@var BlogConnection`
- Line 41: `$container->get(ArticleRepository::class)` → add `@var` annotation
- Line 49: `$container->get(AdminSectionRegistryInterface::class)` → add `@var`

**Task 2.3:** Fix `modules/portfolio/module.php`
- Line 18: `$container->get(ModuleDatabaseResolverInterface::class)` → add `@var`
- Line 24: `$container->get(PortfolioConnection::class)->getConnection()` → add `@var PortfolioConnection`
- Line 34: `$container->get(AdminSectionRegistryInterface::class)` → add `@var`

**Task 2.4:** Fix `modules/cardboard/module.php`
- Line 21: `$c->get(ModuleDatabaseResolverInterface::class)` → add `@var`
- Line 26: `$c->get(MailerInterface::class)` → add `@var`
- Line 29: `$c->get(CardboardConnection::class)->getConnection()` → add `@var`
- Lines 35-36: `$c->get('notification.channel.mail')` → add `@var ChannelInterface`
- Line 42: `$c->get(NotificationManager::class)` → add `@var`

**Task 2.5:** Fix `modules/database-modular/module.php`
- Line 23: `$config->getArray('database.modules')` → add `/** @var array<string, string> $mapping */` before usage
- Line 34: `$container->get(ModuleDatabaseResolverInterface::class)` → add `@var`

**Task 2.6:** Fix `modules/init/module.php`
- Line 27: `$container->get(ModuleTemplateResolver::class)` → add `@var`
- Line 28: `$container->get(Application::class)` → add `@var Application`
- Line 39: `$container->get(ConfigRepositoryInterface::class)` → add `@var`
- Lines 42-44: `$config['eviction']` → add `@var` on config result
- Line 47: `new ModuleGroupManager($container, ...)` — ModuleGroupManager expects `Container`, not `ContainerInterface`. Need to also check this.
- Lines 61-62: `$app->modules` and `$manager->registerGroup()` → fix after adding `@var`

**Task 2.7:** Fix `modules/mark/module.php`
- Lines 29-31: All three `$container->get(...)` calls → add `@var` annotations
- Line 31: `$container->get(PasswordHasherInterface::class)` → add `@var`

**Task 2.8:** Fix `modules/controllers/module.php` (existing `@var` annotations already for some, verify completeness)

### Phase 3: Fix missing DefaultUserProvider

**Task 3.1:** Either:
- (a) Remove the `preferences` entry — mark module already registers `UserProviderInterface::class => MarkProvider::class`
- (b) Create `DefaultUserProvider` implementing `UserProviderInterface`
- (c) Change to use `MarkProvider::class`
- Need to investigate which is intended

### Phase 4: Fix ArticleSeeder.php array shapes

**Task 4.1:** Fix `modules/blog/Seed/ArticleSeeder.php`
- Line 169: `$categoryIds[$article['category_slug']] ?? null` — the `$categoryIds` is typed as `array{architecture: int, frontend: int, backend: int, devops: int}` which is too strict. Change to `array<string, int>`.
- Line 177: `isset($tagIds[$tagSlug])` — same issue. Widen `$tagIds` type.

### Phase 5: Fix misc remaining issues

**Task 5.1:** Fix `modules/blog/tests/ArticleServiceTest.php`
- Line 19: `LoggerInterface` not resolved — add proper `use Psr\Log\LoggerInterface;` import
- Lines 51-268: Mock expectations on `ArticleRepository::expects()` — This is a PHPUnit mock pattern but PHPStan doesn't recognize `createMock()->expects()` on the typed return. These will be excluded by Phase 1 since tests directory will be excluded.

**Task 5.2:** Fix `modules/database-modular/tests/FileCacheTest.php`
- Lines 147-148: `$files = glob(...)` followed by `assertCount(1, $files)` and `$files[0]` — `glob()` returns `list<string>|false`. Add `if ($files === false) { $this->fail(); }` guard.
- This will be excluded by Phase 1 if test exclusions work.

**Task 5.3:** Fix `modules/init/tests/Unit/Module/ModuleGroupTest.php`
- Line 32: `createModuleJson()` missing `@param array $marko` value type → add `@param array<string, mixed> $marko`
- This will be excluded by Phase 1.

### Phase 6: Fix ModuleGroupManager container type

**Task 6.1:** Fix `modules/init/module.php` and `modules/init/src/Module/ModuleGroupManager.php`
- `ModuleGroupManager::__construct()` type-hints `Marko\Core\Container\Container` (concrete class), but `init/module.php` passes `ContainerInterface`.
- Either change `ModuleGroupManager` to accept `ContainerInterface`, or add a `@var` annotation in `module.php` to cast the interface.
- Also fix init/module.php lines 48-49: `$config['eviction']['default'] ?? '5m'` → `$config` is `array` not `array{eviction: array{default: string, enabled: bool}}`.

## Verification

**Task 7.1:** After all fixes, run:
```bash
php ./vendor/bin/phpstan analyse --level=max --error-format=raw 2>&1
```
Verify errors drop to 0 (or close to 0, with acceptable remaining suppressed).

**Task 7.2:** Run tests to ensure nothing broke:
```bash
php ./vendor/bin/phpunit 2>&1
```

## Summary of Files to Modify

| File | Category | Priority |
|------|----------|----------|
| `phpstan.neon` | Config | High (fixes ~280 errors) |
| `modules/database-sqlite/module.php` | Container get() mixed | High |
| `modules/blog/module.php` | Container get() mixed | High |
| `modules/portfolio/module.php` | Container get() mixed | High |
| `modules/cardboard/module.php` | Container get() mixed | High |
| `modules/database-modular/module.php` | Container get() mixed | High |
| `modules/init/module.php` | Container get() mixed + type issues | High |
| `modules/mark/module.php` | Container get() mixed | High |
| `modules/controllers/module.php` | Missing class | Medium |
| `modules/blog/Seed/ArticleSeeder.php` | Array shapes | Low |
| `modules/init/src/Module/ModuleGroupManager.php` | Container type | Medium |
