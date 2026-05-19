# Plan: Password Reset Flow

> Creation date: 2026-05-18
> Branch: (fast mode — no branch)

## Goal

Implement complete password reset flow for the Marko skeleton app:
1. "Forgot password?" form → generuje token → pošle email (log driver v dev)
2. Reset link → formulár pre nové heslo → zmena hesla
3. Token tabuľka v cardboard.db, bezpečný proces

## Settings

- **Testing:** Yes — PHPUnit tests for forgot + reset flows
- **Logging:** Verbose — log each step of the password reset process
- **Docs:** Minimal — update AGENTS.md

---

## Tasks

### ✅ Phase 1: Database

- [x] **Add `password_resets` table to `database/init/cardboard.sql`**
   - Columns: `email` TEXT NOT NULL, `token` TEXT NOT NULL, `createdAt` TEXT NOT NULL
   - Index on `email` + `token` for lookups
   - Placed before `-- === SCHEMA END ===` marker

### ✅ Phase 2: Mail Config & Helper

- [x] **Create `config/mail.php`**
   - `driver` = `log` (dev — log emails to file)
   - `from.address` = `noreply@marko.local`
   - `from.name` = `Marko App`

- [x] **Create `PasswordResetService`** — `modules/cardboard/src/Service/PasswordResetService.php`
   - `generateToken(string $email): string` — `bin2hex(random_bytes(32))` + DB store
   - `validateToken(string $email, string $token): bool` — 60 minút TTL
   - `deleteToken(string $email): void` — clean up after use
   - `sendResetEmail(string $email, string $token, string $resetUrl): void` — logs to `storage/logs/mail.log`
   - Registered in `modules/cardboard/module.php` via DI factory

### ✅ Phase 3: Controllers

- [x] **Create `ForgotPasswordController`** — `modules/cardboard/src/Controller/ForgotPasswordController.php`
   - `#[Get(path: '/mark/forgot-password')]` — show form (guest only)
   - `#[Post(path: '/mark/forgot-password')]` — validate email → always success (no info leak)

- [x] **Create `ResetPasswordController`** — `modules/cardboard/src/Controller/ResetPasswordController.php`
   - `#[Get(path: '/mark/reset-password/{token}')]` — show form with token in URL
   - `#[Post(path: '/mark/reset-password/{token}')]` — validate token, new password + confirmation, update user, delete token, redirect to `/mark/login?reset=success`

### ✅ Phase 4: Templates

- [x] **Create `templates/pages/auth/forgot-password.php`** — form with email input + success notification + link späť na login
- [x] **Create `templates/pages/auth/reset-password.php`** — form with email + new password + confirm + token error handling
- [x] **Create `templates/emails/reset-password.php`** — HTML email with reset button, dev token display

### ✅ Phase 5: Login Template Update

- [x] **Verify login template** — `$forgotPasswordUrl` default `/mark/forgot-password` už existuje, netreba meniť

### ✅ Phase 6: Testing

- [x] **`tests/Unit/Auth/ForgotPasswordTest.php`** — 5 testov (show form, authenticated redirect, valid email, invalid email, empty email)
- [x] **`tests/Unit/Auth/ResetPasswordTest.php`** — 6 testov (show form, authenticated redirect, valid reset, invalid token, password mismatch, empty fields)

---

## Verification ✅

1. ✅ `GET /mark/forgot-password` → form renders
2. ✅ `POST /mark/forgot-password` with valid email → success message + token logged
3. ✅ `POST /mark/forgot-password` with invalid email → same success message (no info leak)
4. ✅ `GET /mark/reset-password/{valid-token}` → form renders
5. ✅ `POST /mark/reset-password/{valid-token}` with matching passwords → password changed, redirect to login
6. ✅ `POST /mark/reset-password/{invalid-token}` → error shown
7. ✅ `php vendor/bin/phpunit` — **25/25 tests pass**
