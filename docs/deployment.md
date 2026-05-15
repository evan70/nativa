[← CLI Reference](cli.md) · [Back to README](../README.md) · [Security →](security.md)

# Deployment Guide

This guide covers building and deploying the Marko Framework application to production.

## Build Process

The framework uses a build-based deployment strategy to eliminate the Composer setup and `vendor/` directory from production.

### How It Works

1. **Development**: Full source code with `composer.json` files for dependency management.
2. **Build**: Run `php build.php` to generate an optimized production artifact.
3. **Production**: Deploy only the `dist/` folder contents (no Composer toolchain, no `vendor/`, no `.git`).

### Build Script Features

- ✅ Copies only runtime-necessary files
- ✅ Excludes the root `composer.json` and `composer.lock`
- ✅ Boots directly from `packages/`
- ✅ Preserves `app/`, `modules/`, `packages/`, `bootstrap/`
- ✅ Automatically generates `bootstrap/runtime-manifest.php` (replaces `composer.json`)
- ✅ Preserves `.env.production` as the production `.env`
- ✅ Ensures all databases are migrated and seeded within the artifact
- ✅ Strips all development artifacts (tests, dev configs, `vendor/`)

## Environment Configuration

The build script looks for a `.env.production` file in the root directory. If found, it will be copied to `dist/.env`. This is the recommended way to manage production secrets and settings.

Example `.env.production`:
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=info
```

## GitHub Actions Workflow

The CI/CD pipeline automatically:

1. Installs PHP + frontend dependencies
2. Runs composer validation, static analysis, tests, and security audit
3. Builds frontend assets and a vendorless production `dist/`
4. Seeds the database for the generated artifact
5. Smoke tests `/`, `/articles`, and `/portfolio` from the built `dist/`
6. Uploads `dist/` as a deployable artifact

## Deployment Options

### Option A: Manual Deployment

```bash
# 1. Build locally
php build.php

# 2. Upload dist/ contents to your server
rsync -av dist/ user@server:/var/www/marko-app/

# 3. On server, ensure permissions
chmod -R 755 /var/www/marko-app/storage
chown -R www-data:www-data /var/www/marko-app/storage
```

### Option B: Automated Deployment

Use the uploaded artifact from GitHub Actions in your deployment tool (e.g., Deployer, Envoyer, custom script).

## Production Structure

```
/var/www/marko-app/
├── app/                      # Your application code
├── modules/                  # Custom modules
├── packages/                 # Framework/runtime packages
├── bootstrap/                # Bootstrap files
├── config/                   # Configuration
├── routes/                   # Route definitions
└── storage/                # Writable directory (create on server)
```

## Performance Benefits

- **No Composer overhead**: Cannot run `composer install` in production
- **No vendor directory**: Runtime code lives in `packages/`
- **Smaller footprint**: Production artifact contains only runtime code

Note: Root `composer.json` is removed from the artifact. Package metadata is preserved in `bootstrap/runtime-manifest.php`, which Marko uses for autoloading and module discovery in production, eliminating the need for `composer.json` files in the deployed artifact.

## Security Notes

- `.env` file is NOT included in build (create on server)
- Root `composer.json` removed from the artifact
- Test files excluded from production
- Ensure `storage/` directory is writable by web server

## Local Testing

```bash
# Build production artifact
php build.php

# Test the build (simulates a real request)
cd dist
php -r '$_SERVER["REQUEST_METHOD"] = "GET"; $_SERVER["REQUEST_URI"] = "/"; $_SERVER["SCRIPT_NAME"] = "/index.php"; require "public/index.php";'
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Classes not found in production | Ensure all classes are properly namespaced and included in autoload paths |
| Missing dependencies | Run `composer install` before building so the local build environment has dev/runtime dependencies available |
| Permissions error | Set proper ownership on `storage/` and `bootstrap/cache/` |

## See Also

- [Getting Started](getting-started.md) — Initial setup guide
- [CLI Reference](cli.md) — Available CLI commands
- [Security](security.md) — Security policy