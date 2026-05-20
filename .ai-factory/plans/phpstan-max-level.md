# PHPStan Max Level - Fix Plan

**Created:** 2026-05-19
**Completed:** 2026-05-20
**Goal:** Achieve PHPStan level `max` with 0 errors across all modules in `modules/`

## Final Result

| Module | Errors Before | Errors After | Status |
|--------|:-----------:|:----------:|:------:|
| `init` | ~70 | 0 | ✅ |
| `cardboard` | ~20 | 0 | ✅ |
| `portfolio` | ~20 | 0 | ✅ |
| `controllers` | ~15 | 0 | ✅ |
| `database-sqlite` | ~10 | 0 | ✅ |
| `mark` | ~13 | 0 | ✅ |
| `view-simple` | ~5 | 0 | ✅ |
| **Total** | **207 → 163 → 130 → 78 → 73 → 0** | | **✅** |

## Progress

```
Phase 0 (baseline):  207 errors
Phase 1 (type hints):  163 errors ✅
Phase 2 (null safety):   78 errors ✅
Phase 3 (generics):      73 errors ✅
Phase 4 (init deep):      0 errors ✅
Phase 5 (cleanup):        0 errors ✅
```

---

## ✅ Phase 1: Cross-cutting Type Hints

**Completed fixes:**
- `view-simple/SimpleView.php` — Added `@return array<string, mixed>` / `@param array<string, mixed>`
- `database-sqlite/SqliteConnection.php` — Added `@return list<array<string, mixed>>`
- Multiple controllers across `cardboard`, `portfolio`, `init` — Added `@var`, `@param`, `@return` annotations

---

## ✅ Phase 2: Null Safety & Mixed Type Casts

**85 fixes across 13 files:**

| Súbor | Problém | Oprava |
|-------|---------|--------|
| `cardboard/DashboardController.php` | `cast.int` | `is_numeric()` guardy na query výsledky |
| `cardboard/ForgotPasswordController.php` | `cast.string` | `is_string()` guard na `$request->post()` |
| `cardboard/RegisterController.php` | `cast.string` | 4× `is_string()` guard na POST hodnoty |
| `cardboard/ResetPasswordController.php` | `cast.string` | 3× `is_string()` guard |
| `cardboard/SettingsController.php` | `cast.string` | 7× `is_string()` guard v `store()` + `update()` |
| `cardboard/PasswordResetService.php` | `offsetAccess` | `is_array()` guard na `$row['createdAt']` |
| `controllers/MarkDashboardController.php` | `cast.int` | `is_numeric()` guardy na query výsledky |
| `database-sqlite/SqliteConnection.php` | `method.nonObject` | `\assert($this->pdo !== null)` v 7 metódach |
| `init/Container/Container.php` | `offsetAccess` | `@var` + `is_array()` guardy na Reflection hodnoty |
| `init/ModuleActivateController.php` | `method.nonObject` | `@var` anotácie pre `container->get()` |
| `init/ModuleBindingsController.php` | `method.nonObject` | `@var` anotácie + `!== []` namiesto truthy |
| `init/ModuleEvictController.php` | `method.nonObject` | `@var` anotácie |
| `init/ModuleUnbindController.php` | `method.nonObject` | `@var` anotácie |

---

## ✅ Phase 3: Generics & Interface Resolution

| Súbor | Oprava |
|-------|--------|
| `mark/Repository/MarkRepositoryInterface.php` | `@template TEntity of Entity` + `@extends RepositoryInterface<TEntity>` |
| `mark/Repository/PermissionRepositoryInterface.php` | Rovnaký pattern |
| `mark/Repository/RoleRepositoryInterface.php` | Rovnaký pattern |
| `mark/Repository/MarkRepository.php` | `@implements MarkRepositoryInterface<Mark>` |
| `mark/Repository/PermissionRepository.php` | `@implements PermissionRepositoryInterface<Permission>` + `Permission::class` namiesto `static::ENTITY_CLASS` |
| `mark/Repository/RoleRepository.php` | `@implements RoleRepositoryInterface<Role>` |
| `mark/MarkProvider.php` | `is_string()` guardy, `$user->id ?? 0`, `@param` generické anotácie |

---

## ✅ Phase 4: `init` Module Deep Clean

| Súbor | Oprava |
|-------|--------|
| `cardboard/ForgotPasswordController.php` | `@param MarkRepositoryInterface<Mark>` generická anotácia |
| `cardboard/RegisterController.php` | Rovnaký pattern |
| `cardboard/ResetPasswordController.php` | Rovnaký pattern |
| `init/Container/Container.php` | Odstránené redundantné `is_array()` checky |
| `init/Controller/ModuleBindingsController.php` | Typ hint z `ContainerInterface` na `Container` |
| `init/Controller/ModuleUnbindController.php` | Opravená always-true vetva v elseif |
| `init/Middleware/GroupRouteGuard.php` | `$request->getPath()` → `$request->path()` |
| `init/Module/ModuleGroup.php` | Odstránený unreachable `default` arm v match, `?string` → `string` |
| `init/Module/ModuleGroupManager.php` | Kompletná typová bezpečnosť |
| `mark/MarkProvider.php` | Opravený FQCN pre `Role` |
| `portfolio/PortfolioAdminController.php` | `is_string()` guardy |
| `view-simple/SimpleView.php` | `@phpstan-ignore`, `@param array<string, mixed>` |

---

## ✅ Phase 5: Stale ignoreErrors Cleanup

**Cleaned up `phpstan.neon`:**
- Removed `#Class Marko\\Authentication\\DefaultUserProvider not found#` — resolved
- Removed `#Offset.*always exists#` — resolved by Phase 2-4 fixes
- Removed `#Variable \\$_POST on left side of \\?\\?#` — resolved by `is_string()` guards
- Removed `#Parameter.*storagePath.*implicitly nullable#` — resolved
- Removed `#Property.*ModuleConnection.*pdo#` — resolved
- Removed `#comparison.*PDO and null#` — resolved by `\assert()` guards

---

## Verification

Final verification command:
```bash
php vendor/bin/phpstan analyse --memory-limit=1G --error-format=table
# Output: [OK] No errors
```

CI workflow added: `.github/workflows/phpstan.yml`
- Runs on push + PR to `main`/`master`
- Uses `--error-format=github` for inline PR annotations
- Independent from full CI pipeline (faster, only PHP + Composer)
