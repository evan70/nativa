[Back to README](../README.md) · [CLI Reference →](cli.md)

# Getting Started

This guide covers installation, setup, and your first steps with the Marko Framework.

## Prerequisites

- PHP 8.5+
- Composer
- Node.js and pnpm (for frontend assets)

## Installation

```bash
composer create-project marko/skeleton my-app
cd my-app
```

## Initial Setup

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Install composer dependencies:
   ```bash
   composer install
   ```

3. Install and build frontend assets:
   ```bash
   cd templates && pnpm install && pnpm build
   ```

4. Run database migrations:
   ```bash
   ./marko db:migrate
   ```

5. Start the development server:
   ```bash
   ./marko up
   ```

6. Visit http://localhost:8000

## Project Structure

| Directory | Purpose |
|-----------|---------|
| `app/` | Application code (controllers, services, entities) |
| `modules/` | Third-party modules |
| `packages/` | Framework packages |
| `config/` | Configuration files |
| `public/` | Web entry point |
| `storage/` | Logs, cache, sessions |
| `templates/` | Frontend templates and assets |
| `templates/pages/` | PHP page templates (flat structure) |
| `templates/layouts/` | PHP layouts (app, admin, auth) |
| `templates/partials/` | Shared partials (sidebar, navbar, flash) |
| `templates/src/` | Frontend source (Vite entries, CSS, TS) |

## Your First Controller

Create your first controller inside `app/src/`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Marko\Http\Request;
use Marko\Http\Response;

class HomeController
{
    public function index(Request $request): Response
    {
        return new Response('Hello, Marko!');
    }
}
```

## Routing

Map the controller to a route in `config/routes.php`:

```php
use App\Http\Controllers\HomeController;

return [
    '/' => [HomeController::class, 'index'],
];
```

## Next Steps

- [CLI Reference](cli.md) — Full list of available commands
- [Deployment Guide](deployment.md) — Production deployment
- [Security Policy](security.md) — Security details

## See Also

- [CLI Reference](cli.md) — Available CLI commands
- [Deployment Guide](deployment.md) — Production deployment