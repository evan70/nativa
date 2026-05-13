# Plan: Marko Admin Blog/Article Management

**Created:** 2026-05-13  
**Mode:** Fast

## Overview

Fix and complete the blog article admin management in Marko. The BlogAdminSection and basic controller exist but are incomplete. Need to implement full CRUD functionality with proper templates.

## Implementation Status

### ✅ Completed Tasks

| Task | Status | Notes |
|------|--------|-------|
| **Task 1** | ✅ Done | Added Edit/Delete links to admin-articles.php |
| **Task 2** | ✅ Done | Created article-form.php template |
| **Task 3** | ✅ Done | Fixed create() method to render correct template |
| **Task 4** | ✅ Done | Added edit() method |
| **Task 5** | ✅ Done | Added update() method |
| **Task 6** | ✅ Done | Added delete() method |
| **Task 7** | ✅ Done | Validation in controller (inline, not separate class) |
| **Task 8** | ✅ Done | Auto-generate slug implemented |
| **Task 9** | ⏳ Pending | Flash messages not implemented yet |

### PHPStan Fixes (Level Max)

| File | Status | Notes |
|------|--------|-------|
| **BlogAdminController.php** | ✅ Fixed | Added helper methods with proper type handling for PHPStan max |
| **ModuleDatabaseResolver.php** | ✅ Fixed | Changed `$storagePath = null` to `?string $storagePath = null` |
| **ModuleConnection.php** | ✅ Fixed | Changed `PDO $pdo` to `?PDO $pdo = null` |

### Remaining PHPStan Errors (149 total)

The remaining errors are in pre-existing codebase files:
- app/View.php (many errors - array type handling)
- app/module.php (mixed type handling)
- app/src/Controllers/HomeController.php
- app/src/Controllers/PortfolioController.php
- modules/blog/src/DTO/ArticleDTO.php
- modules/blog/src/Controller/ArticleController.php
- modules/blog/src/Controller/ArticleApiController.php
- modules/blog/src/Validation/ArticleValidator.php
- modules/blog/src/Service/ArticleService.php
- modules/blog/src/Contracts/ArticleServiceInterface.php
- modules/database-modular/src/ModuleDatabaseResolver.php (partially fixed)

## Tasks

### Phase 1: Fix Article List & Basic CRUD

**Task 1: Fix article index template**
- File: `templates/pages/dash/admin-articles.php`
- ✅ DONE - Added action column with Edit/Delete links

**Task 2: Create article form template**
- File: `templates/pages/dash/article-form.php`
- ✅ DONE - Created with all fields (title, slug, excerpt, content, image, status, category, published)

**Task 3: Fix BlogAdminController create() method**
- File: `modules/blog/src/Controller/BlogAdminController.php`
- ✅ DONE - Changed to render 'pages/dash/article-form'

### Phase 2: Complete CRUD Operations

**Task 4: Add edit() method**
- File: `modules/blog/src/Controller/BlogAdminController.php`
- ✅ DONE - Route: GET /mark/articles/{id}/edit

**Task 5: Add update() method**
- File: `modules/blog/src/Controller/BlogAdminController.php`
- ✅ DONE - Route: PUT /mark/articles/{id}

**Task 6: Add delete() method**
- File: `modules/blog/src/Controller/BlogAdminController.php`
- ✅ DONE - Route: DELETE /mark/articles/{id}

### Phase 3: Validation & Polish

**Task 7: Add article validation**
- Files: `modules/blog/src/Controller/BlogAdminController.php`
- ✅ DONE - Validation in store() and update() methods

**Task 8: Auto-generate slug from title**
- File: `modules/blog/src/Controller/BlogAdminController.php`
- ✅ DONE - generateSlug() method implemented

**Task 9: Add flash messages**
- ⏳ NOT DONE - Would require session handling

## Settings

- **Testing:** No tests in plan
- **Logging:** Verbose (INFO for key events, DEBUG for details)
- **Documentation:** No updates in plan

## Notes

- Follow existing patterns in BlogAdminController
- Use Article entity and ArticleRepository
- Admin templates should use consistent styling with dash/index.php
- Consider HTMX for delete (smooth UX)
- Routes should follow /mark/articles prefix for admin
- PHPStan max level requires proper type handling for all mixed types