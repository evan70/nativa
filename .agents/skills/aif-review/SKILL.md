---
name: aif-review
description: Perform code review on staged changes or a pull request. Checks for bugs, security issues, performance problems, and best practices. Use when user says "review code", "check my code", "review PR", or "is this code okay".
argument-hint: "[PR number or empty]"
allowed-tools: Bash(git *) Bash(gh *) Read Glob Grep
---

# Code Review Assistant

Perform thorough code reviews focusing on correctness, security, performance, and maintainability.

## Behavior

### Without Arguments (Review Staged Changes)

1. Run `git diff --cached` to get staged changes
2. If nothing staged, run `git diff` for unstaged changes
3. Analyze each file's changes

### With PR Number/URL

1. Use `gh pr view <number> --json` to get PR details
2. Use `gh pr diff <number>` to get the diff
3. Review all changes in the PR

### PHPStan Verification

For PHP projects, also verify static analysis:
```bash
php vendor/bin/phpstan analyze --memory-limit=256M
```
If the project uses a custom config:
```bash
php vendor/bin/phpstan analyze -c phpstan.neon --memory-limit=256M
```

## Review Checklist

### Correctness
- [ ] Logic errors or bugs
- [ ] Edge cases handling
- [ ] Null/undefined checks
- [ ] Error handling completeness
- [ ] Type safety (if applicable)

### PHP-Specific Correctness
- [ ] `declare(strict_types=1);` present in new files
- [ ] Return types declared on all methods
- [ ] Constructor property promotion (`readonly` + `private Type $param`)
- [ ] No implicit nullable types (use `?Type` explicitly)
- [ ] PHPDoc comments only for complex types, not instead of real types

### Security
- [ ] SQL injection vulnerabilities
- [ ] XSS vulnerabilities
- [ ] Command injection
- [ ] Sensitive data exposure
- [ ] Authentication/authorization issues
- [ ] CSRF protection
- [ ] Input validation

### Performance
- [ ] N+1 query problems
- [ ] Unnecessary re-renders (React)
- [ ] Memory leaks
- [ ] Inefficient algorithms
- [ ] Missing indexes (database)
- [ ] Large payload sizes

### Best Practices
- [ ] Code duplication
- [ ] Dead code
- [ ] Magic numbers/strings
- [ ] Proper naming conventions
- [ ] SOLID principles
- [ ] DRY principle

### PHP Framework Best Practices (Marko/Laravel/etc.)
- [ ] Dependency injection via constructor (no service locator)
- [ ] Routing attributes used correctly
- [ ] `readonly` classes for immutable services
- [ ] Custom exceptions for domain errors

### Static Analysis (PHP)
- [ ] PHPStan passes at configured level (project uses level max)
- [ ] No `@var` annotations conflicting with actual types
- [ ] Properties properly initialized (never access before init)

### Testing
- [ ] Test coverage for new code
- [ ] Edge cases tested
- [ ] Mocking appropriateness

## Output Format

```markdown
## Code Review Summary

**Files Reviewed:** [count]
**Risk Level:** 🟢 Low / 🟡 Medium / 🔴 High

### Critical Issues
[Must be fixed before merge]

### Suggestions
[Nice to have improvements]

### Questions
[Clarifications needed]

### Positive Notes
[Good patterns observed]
```

## Review Style

- Be constructive, not critical
- Explain the "why" behind suggestions
- Provide code examples when helpful
- Acknowledge good code
- Prioritize feedback by importance
- Ask questions instead of making assumptions

## Examples

**User:** `/aif-review`
Review staged changes in current repository.

**User:** `/aif-review 123`
Review PR #123 using GitHub CLI.

**User:** `/aif-review https://github.com/org/repo/pull/123`
Review PR from URL.

## Integration

If GitHub MCP is configured, can:
- Post review comments directly to PR
- Request changes or approve
- Add labels based on review outcome

> **Tip:** Context is heavy after code review. Consider `/clear` or `/compact` before continuing with other tasks.
