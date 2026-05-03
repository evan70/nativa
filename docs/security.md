[← Deployment](deployment.md) · [Back to README](../README.md)

# Security Policy

## Supported Versions

Currently supported versions with security updates:

| Version | Supported          |
| ------- |------------------|
| 5.1.x   | ✅ Yes |
| 5.0.x   | ❌ No |
| 4.0.x   | ✅ Yes |
| < 4.0   | ❌ No |

## Reporting a Vulnerability

To report a security vulnerability:

1. **Do NOT** open a public GitHub issue
2. Email security details to the maintainers
3. Expect acknowledgment within 48 hours
4. Expect updates every 7 days on progress

## Framework Security Features

The Marko Framework includes built-in security features:

- **CSRF Protection** — Enabled by default for all forms
- **Input Validation** — Sanitizes user input
- **Output Escaping** — Prevents XSS attacks
- **SQL Injection Prevention** — Uses parameterized queries
- **Session Security** — Secure session handling with configurable drivers

## Security Best Practices

1. **Keep PHP updated** — Use PHP 8.5+
2. **Environment variables** — Store secrets in `.env`, never commit it
3. **File permissions** — Restrict `storage/` to web server user only
4. **HTTPS** — Always use HTTPS in production
5. **Log monitoring** — Watch for suspicious activity

## See Also

- [Getting Started](getting-started.md) — Initial setup guide
- [Deployment Guide](deployment.md) — Production deployment