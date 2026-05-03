# Plan: Refactor Blog Module

**Branch:** refactor/blog-module
**Created:** 2026-05-03 09:00
**Description:** Refactor blog module to follow better patterns and add missing features

## Settings

- **Testing:** To be confirmed by user
- **Logging:** Verbose (recommended for AI-generated code)
- **Documentation:** Yes, update docs/ after implementation

## Overview

Current state of the blog module:
- Basic Article entity with repository
- Simple controller with index/show actions
- Uses Marko's repository pattern
- Module system integrated via module.php

Areas for improvement:
1. **Entity structure** — Add proper relationships (Article → Category, Tag)
2. **Repository** — Add query methods (findBySlug, findPublished, findByCategory)
3. **Service layer** — Add ArticleService for business logic
4. **DTOs** — Add request/response DTOs
5. **Error handling** — Add proper exceptions
6. **Validation** — Add input validation

## Tasks

### Phase 1: Infrastructure

- [x] 1.1 — Create DTOs and service interfaces
  - Created `modules/blog/src/DTO/` directory
  - Created `ArticleDTO.php`
  - Created `CreateArticleRequest.php`
  - Created `UpdateArticleRequest.php`
  - Created `ArticleServiceInterface.php`

### Phase 2: Service Layer

- [x] 2.1 — Create ArticleService implementation
  - Implemented in `modules/blog/src/Service/ArticleService.php`
  - Added all required methods with verbose logging

- [x] 2.2 — Update module.php to bind service
  - Updated `modules/blog/module.php`
  - Added ArticleServiceInterface binding

### Phase 3: Controller Improvements

- [x] 3.1 — Create API controller for CRUD operations
  - Created `ArticleApiController.php`
  - POST/PUT/DELETE /api/articles endpoints

- [x] 3.2 — Update existing ArticleController
  - Added pagination, category filtering, slug lookup

### Phase 4: Validation & Error Handling

- [x] 4.1 — Add validation logic
  - Created `ArticleValidator.php`
  - Validates title, content, slug

- [x] 4.2 — Add custom exceptions
  - Created `ArticleNotFoundException.php`
  - Created `ArticleValidationException.php`

### Phase 5: Testing

- [x] 5.1 — Add unit tests for ArticleService
  - Created `ArticleServiceTest.php`

- [x] 5.2 — Add controller tests
  - Created `ArticleControllerTest.php`

## Commit Plan

- **Commit 1:** Add DTOs and service interfaces
- **Commit 2:** Implement ArticleService
- **Commit 3:** Update controllers with new endpoints
- **Commit 4:** Add validation and error handling
- **Commit 5:** Add tests

## Files to Modify

- `modules/blog/module.php` — Add service binding
- `modules/blog/src/Controller/ArticleController.php` — Improve existing
- `modules/blog/src/Repository/ArticleRepository.php` — Add query methods

## Files to Create

- `modules/blog/src/DTO/ArticleDTO.php`
- `modules/blog/src/DTO/CreateArticleRequest.php`
- `modules/blog/src/DTO/UpdateArticleRequest.php`
- `modules/blog/src/Contracts/ArticleServiceInterface.php`
- `modules/blog/src/Service/ArticleService.php`
- `modules/blog/src/Controller/ArticleApiController.php`
- `modules/blog/src/Validation/ArticleValidator.php`
- `modules/blog/src/Exceptions/ArticleNotFoundException.php`
- `modules/blog/src/Exceptions/ArticleValidationException.php`
- `modules/blog/tests/ArticleServiceTest.php`
- `modules/blog/tests/ArticleControllerTest.php`

## Notes

- Follow Marko Framework module patterns from existing code
- Use strict_types=1 in all new PHP files
- Use dependency injection via service container
- Use PSR-4 autoload: App\Blog\* namespace