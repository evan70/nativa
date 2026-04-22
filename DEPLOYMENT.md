# Marko Framework - Production Deployment Guide

## 📦 Build Process

The framework uses a build-based deployment strategy to eliminate the root Composer setup and the `vendor/` directory from production.

### How It Works

1. **Development**: Full source code with `composer.json` files for dependency management.
2. **Build**: Run `php build.php` to generate an optimized production artifact.
3. **Production**: Deploy only the `dist/` folder contents (no Composer toolchain, no `vendor/`, no `.git`).

### Build Script Features

- ✅ Copies only runtime-necessary files
- ✅ Excludes the root `composer.json` and `composer.lock`
- ✅ Boots directly from `packages/`
- ✅ Preserves `app/`, `modules/`, `packages/`, `bootstrap/`

## 🚀 GitHub Actions Workflow

The CI/CD pipeline automatically:

1. Installs dependencies for root, packages, and modules
2. Runs tests and static analysis (if configured)
3. Executes `php build.php` to create a vendorless production artifact
4. Uploads `dist/` as a deployable artifact

### Deployment Steps

**Option A: Manual Deployment**
```bash
# 1. Build locally
php build.php

# 2. Upload dist/ contents to your server
rsync -av dist/ user@server:/var/www/marko-app/

# 3. On server, ensure permissions
chmod -R 755 /var/www/marko-app/storage
chown -R www-data:www-data /var/www/marko-app/storage
```

**Option B: Automated Deployment**
Use the uploaded artifact from GitHub Actions in your deployment tool (e.g., Deployer, Envoyer, custom script).

## 📁 Production Structure

```
/var/www/marko-app/
├── app/                      # Your application code
├── modules/                  # Custom modules
├── packages/                 # Framework/runtime packages
├── bootstrap/                # Bootstrap files
├── config/                   # Configuration
├── routes/                   # Route definitions
└── storage/                  # Writable directory (create on server)
```

## ⚡ Performance Benefits

- **No Composer overhead**: Cannot run `composer install` in production
- **No vendor directory**: Runtime code lives in `packages/`
- **Smaller footprint**: Production artifact contains only runtime code

Note: `packages/*/composer.json` files remain in the artifact because Marko uses them as runtime manifests for autoloading and module discovery.

## 🔒 Security Notes

- `.env` file is NOT included in build (create on server)
- Root `composer.json` removed from the artifact
- Test files excluded from production
- Ensure `storage/` directory is writable by web server

## 🛠️ Local Testing

```bash
# Build production artifact
php build.php

# Test the build
cd dist
php -r "require 'bootstrap/autoload.php'; echo 'OK';"

# Run your application
php bootstrap/app.php
```

## 📝 Troubleshooting

**Issue**: Classes not found in production
- **Solution**: Ensure all classes are properly namespaced and included in autoload paths

**Issue**: Missing dependencies
- **Solution**: Run `composer install` before building so the local build environment has dev/runtime dependencies available

**Issue**: Permissions error
- **Solution**: Set proper ownership on `storage/` and `bootstrap/cache/`
