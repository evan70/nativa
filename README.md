# Marko Skeleton

Application skeleton for the [Marko Framework](https://marko.build).

## Installation

```bash
composer create-project marko/skeleton my-app
cd my-app
```

## What's Included

- `public/index.php` — Web entry point
- `app/` — Your application modules
- `modules/` — Third-party modules
- `config/` — Root configuration
- `storage/` — Logs, cache, sessions
- `.env.example` — Environment template

## Getting Started

1. Copy `.env.example` to `.env`
2. Install dev tools: `composer install`
3. Install and build frontend assets:
   ```bash
   cd templates && pnpm install && pnpm build
   ```
4. Run database migrations: `./marko db:migrate`
5. Start the dev server: `./marko up`
6. Visit http://localhost:8000

## CLI Commands

The `marko` CLI provides several useful commands for development:

### Database
- `./marko db:migrate` — Apply database migrations
- `./marko db:rollback` — Rollback the last batch of migrations
- `./marko db:status` — Show migration status
- `./marko db:diff` — Show differences between entity schema and database
- `./marko db:rebuild` — Reset and re-run all migrations (clean slate)
- `./marko db:seed` — Run database seeders

### System
- `./marko module:list` — Show all modules and their status
- `./marko log:clear` — Clear old log files
- `./marko session:gc` — Run session garbage collection
- `./marko list` — Show all available commands

### Queue
- `./marko queue:work` — Process jobs from the queue
- `./marko queue:status` — Show queue statistics

## Next Steps

Create your first controller inside `app/`:

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

## Documentation

- [Your First Application](https://marko.build/docs/getting-started/first-application/)
- [Project Structure](https://marko.build/docs/getting-started/project-structure/)
- [Full Documentation](https://marko.build/docs/)
