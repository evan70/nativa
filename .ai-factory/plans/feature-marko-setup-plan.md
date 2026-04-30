# Implementation Plan: Marko Framework Setup

Branch: feature/marko-setup-plan
Created: 2026-04-30

## Settings
- Testing: yes
- Logging: verbose
- Docs: yes

## Commit Plan
- **Commit1** (after tasks 1-3): "feat: Configure base Marko module and project structure"
- **Commit2** (after tasks 4-6): "feat: Implement core controllers and database setup"
- **Commit3** (after tasks 7-9): "feat: Add tests and update documentation"

## Tasks

### Phase1: Setup
- [x] Task 1: Create Marko module for `app/controllers`
  - Ensure `app/composer.json` defines module with PSR-4 autoloading.
  - Verify `app/src/` directory exists.
- [x] Task 2: Initialize Marko CLI
  - Verify `marko` executable exists and is functional.
- [x] Task 3: Configure Database (SQLite)
  - Ensure `config/database.php` uses SQLite at `storage/data/database.sqlite`.
  - Create `storage/data/database.sqlite` if not exists.

### Phase2: Core Implementation
- [x] Task 4: Create Home Controller
  - Add `app/src/HomeController.php` with `index()` method returning `Hello, Marko!`.
  - Include logging entry point per verbose logging setting.
- [x] Task 5: Set Up CLI Command for migrations
  - Ensure `./marko db:migrate` works (no-op if no migrations).
- [x] Task 6: Configure Logging Verbose Mode
  - Update `.env` to set `LOG_LEVEL=debug`.
  - Verify logs directory `storage/framework/logs/` exists.

### Phase3: Tests & Documentation
- [x] Task 7: Write Unit Tests for HomeController
  - Create `tests/HomeControllerTest.php` using PestPHP.
  - Test that controller returns expected response.
- [x] Task 8: Generate Initial Documentation
  - Update `README.md` with architecture summary and setup instructions.
- [x] Task 9: Final Documentation Update
  - Add reference to `.ai-factory/ARCHITECTURE.md` in README.

## Progress
All tasks completed. Ready for commit and push.
