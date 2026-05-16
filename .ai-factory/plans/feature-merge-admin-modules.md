# Plan: Merge Cardboard into Mark Module

Merge the `cardboard` (UI) module into the existing `mark` (Auth) module to create a unified administration module under the name `mark`.

**Branch:** `feature/merge-admin-modules`
**Date:** 2026-05-17

## Settings
- **Testing:** Yes, write tests
- **Logging:** Verbose (Recommended)
- **Documentation:** Yes, update docs
- **Constraints:** Keep `/mark` URLs, Use `mark` as the module name (word "admin" is restricted)

## Tasks

### Phase 1: Preparation
- [x] Ensure `modules/mark` has subdirectories for UI: `src/Controller`, `src/Menu`, `src/Admin` (UI registry)
- [x] Update `modules/mark/composer.json` to include any missing dependencies from `cardboard`

### Phase 2: Migration from Cardboard
- [x] Move `modules/cardboard/src/Controller/*` to `modules/mark/src/Controller/` and update namespaces to `Marko\Mark\Controller`
- [x] Move `modules/cardboard/src/Admin/*` (e.g. `CardboardAdminSection`) to `modules/mark/src/Admin/` and rename/refactor as needed
- [x] Move `modules/cardboard/src/Menu/*` to `modules/mark/src/Menu/` and update namespaces
- [x] Move `modules/cardboard/src/Config/*` to `modules/mark/src/Config/` and update namespaces

### Phase 3: Database & Binding Consolidation
- [x] Implement a clean `MarkConnection` in `modules/mark/src/Database/MarkConnection.php` if not already present, ensuring it uses the `mark` database identifier
- [x] Update `modules/mark/module.php` to include bindings for all migrated controllers and services
- [x] Update `config/database.php` to remove the `cardboard` mapping (everything now uses `mark`)
- [x] Update root `composer.json` autoloading (remove `Marko\Cardboard`) and run `composer dump-autoload`

### Phase 4: Global Refactoring
- [x] Update all references to `Marko\Cardboard` to `Marko\Mark` across the entire project
- [x] Ensure `DashboardController` in its new location uses the consolidated `MarkConnection`

### Phase 5: Verification & Cleanup
- [x] Run PHPStan and fix any type errors or missing references
- [x] Run tests to verify that login and dashboard still work correctly
- [x] Delete the `modules/cardboard` directory
- [x] Update documentation to reflect that the `mark` module now handles both Auth and Admin UI

## Commit Plan
1. **Move files**: Relocate all source code from `cardboard` to `mark`.
2. **Refactor namespaces**: Update all `Marko\Cardboard` namespaces to `Marko\Mark`.
3. **Bindings & Database**: Update `module.php`, `config/database.php` and root `composer.json`.
4. **Global Cleanup**: Update references in other modules and delete `cardboard` directory.
5. **Finalization**: Verify with PHPStan and update docs.
