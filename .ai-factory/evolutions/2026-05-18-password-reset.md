# Evolution: 2026-05-18 16:00

## Intelligence Summary
- Patches analyzed: 1 (`2026-05-15-11.55.md`)
- Tech stack: PHP 8.5+, Marko Framework, SQLite, attribute-based routing
- Features completed:
  1. User Registration flow
  2. Module DB init scripts (migrácie → init SQL scripts)
  3. Database restructure: nativa.db → cardboard.db (module-based)
  4. Password Reset flow

## Patterns Identified

| Pattern | Frequency | Description |
|---------|-----------|-------------|
| `#di-binding` | 4+ | Services used in controllers need explicit DI bindings in module.php |
| `#secure-token` | 2 | password_resets uses `bin2hex(random_bytes(32))` for cryptographically secure tokens |
| `#no-info-leak` | 2 | Forgot password always shows success (doesn't reveal if email exists) |
| `#module-db` | 3 | Module-specific databases resolved via ModuleDatabaseResolverInterface |
| `#init-script` | 2 | Dev phase uses SQL init scripts (not migration files) for database setup |
| `#error_log-dev` | 4 | Dev phase uses `error_log()` for verbose logging (LoggerInterface not always available) |
| `#controller-pattern` | 3 | Controllers follow: constructor DI + #[Get]/#[Post] attributes + guest check at top |

## Improvements Applied

### marko-framework
- **DI binding for service classes** — Added guidance in password reset flow: services used by controllers must be registered in `module.php`'s `bindings` array with factory closures. Driven by missing `PasswordResetService` binding during implementation.

### aif-implement
- **Module db init scripts** — When planning dev-phase modules, use `database/init/<db>.sql` with `-- === SCHEMA END ===` marker for seed data separation, not migration files. Init scripts are run via `database/init.php`.

### aif-review
- **DI binding check** — Add to PHP Framework checklist: "Service classes used by controllers registered in module.php's bindings array"
- **Email info leak** — Add to Security checklist: "Password reset forms don't reveal whether an email exists (always show success)"

## Recommendations

1. **Run `/aif-evolve`** after 2-3 more feature implementations
2. **Consider `/aif-skills`** — The password reset + email patterns could form a reusable skill
3. **Document new skill** — `marko-auth` skill covering login, register, password reset for Marko Framework
