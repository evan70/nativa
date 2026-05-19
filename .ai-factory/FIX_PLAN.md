# Fix Plan: Cardboard Module Database and Admin Section Integration

**Problem:** Cardboard module admin section not loading due to namespace mismatch, and missing database file
**Created:** 2026-05-18 12:00

## Analysis

### Issues Found:

1. **Namespace Mismatch (Critical)**
   - `modules/cardboard/composer.json` defines PSR-4 namespace: `Marko\Cardboard\`
   - `modules/cardboard/src/Admin/CardboardAdminSection.php` uses: `namespace App\Cardboard\Admin;`
   - Result: Class cannot be autoloaded, AdminSectionDiscovery finds the file but cannot instantiate the class

2. **Missing Database File**
   - `config/database.php` maps `'cardboard' => 'cardboard'` database
   - `storage/data/cardboard.db` does not exist
   - Other modules (blog, portfolio, nativa) have their database files

3. **No Database Migrations for Cardboard**
   - No `database/migrations/` for cardboard module
   - No schema definition for cardboard-specific tables

## Root Cause

The cardboard module was created with incorrect namespace in `CardboardAdminSection.php`. The composer.json correctly defines `Marko\Cardboard\` but the file uses `App\Cardboard\Admin`. This prevents the PHP autoloader from finding the class when the AdminSectionDiscovery tries to instantiate it.

The database file is missing because it was never created — the configuration expects it but it doesn't exist.

## Fix Steps

1. [ ] **Fix namespace in CardboardAdminSection.php**
   - Change `namespace App\Cardboard\Admin;` to `namespace Marko\Cardboard\Admin;`
   - Verify all `use` statements are correct
   
2. [ ] **Create cardboard.db database file**
   - Create empty SQLite database: `touch storage/data/cardboard.db`
   - Or run migrations if they exist

3. [ ] **Create database migrations for cardboard module** (Optional but recommended)
   - Create `modules/cardboard/database/migrations/` directory
   - Add initial schema migration for cardboard-specific tables

4. [ ] **Verify AdminSectionDiscovery can find and load the section**
   - Clear any cached discovery results
   - Test that the cardboard admin section appears in admin panel

## Files to Modify

- `modules/cardboard/src/Admin/CardboardAdminSection.php` — Fix namespace
- `storage/data/cardboard.db` — Create empty database file (or create via migration)
- `modules/cardboard/database/migrations/YYYYMMDDHHMMSS_create_cardboard_schema.php` — Optional: Add migrations

## Risks & Considerations

- **Namespace change**: This is a breaking change if any code references `App\Cardboard\Admin\CardboardAdminSection`. Need to check all callers.
- **Database creation**: Empty database is fine for now, but proper schema should be defined via migrations.
- **Autoloader cache**: After namespace fix, run `composer dump-autoload` to rebuild autoloader.
- **Module discovery**: The admin package's discovery mechanism scans for `#[AdminSection` attribute, which is present in the file, so discovery should work once the class is autoloadable.

## Impact Scope

- Cardboard admin section will become accessible in admin panel
- No impact on other modules (namespace is isolated to cardboard)
- Database operations for cardboard module will work once db file exists

## Test Coverage Suggested

- Verify `CardboardAdminSection` can be instantiated via container
- Verify admin panel shows Cardboard section with correct menu items
- Verify database connection for cardboard module works
