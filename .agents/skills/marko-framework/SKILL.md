---
name: marko-framework
description: Marko PHP Framework - modular PHP 8.5+ framework. Use when building with Marko, creating modules, configuring DI, implementing routes, commands, or customizing behavior. Based on official documentation.
argument-hint: "[task description]"
allowed-tools: Read Grep Glob Write Bash(php *) Bash(mkdir *) Bash(chmod *)
metadata:
  source: https://marko.build/docs
---

# Marko PHP Framework

A modular PHP 8.5+ framework combining Magento's extensibility with Laravel's developer experience. Loud errors, true modularity, zero magic.

## Core Philosophy

1. **True Modularity** — Every feature is a Composer package. Interface and implementation are split. Swap any piece without touching the rest.
2. **Loud Errors** — No silent failures. Every error includes what went wrong, the context, and a suggestion for how to fix it.
3. **Explicit Over Implicit** — No magic methods, no hidden conventions. Everything is discoverable and type-safe.
4. **Extensibility Built In** — Preferences, Plugins, Events, and Observers — customize any behavior without modifying vendor code.

---

## Module System

### What Is a Module?

A module is any Composer package that Marko recognizes. At minimum, it needs:
- `name`
- PSR-4 `autoload` mapping
- `extra.marko.module: true`

```json
{
    "name": "app/blog",
    "autoload": {
        "psr-4": {
            "App\\Blog\\": "src/"
        }
    },
    "extra": {
        "marko": {
            "module": true
        }
    }
}
```

### Module Discovery

Marko scans three locations (priority order):

| Priority | Location | Description |
|----------|----------|-------------|
| 1 | `app/` | Your application (wins all conflicts) |
| 2 | `modules/` | Third-party (overrides vendor) |
| 3 | `vendor/` | Composer packages (base defaults) |

### The module.php File

Optional configuration at module root for DI wiring:

```php
<?php

declare(strict_types=1);

use App\Blog\Repository\PostRepository;
use App\Blog\Repository\PostRepositoryInterface;

return [
    // Bind interfaces to implementations
    'bindings' => [
        PostRepositoryInterface::class => PostRepository::class,
    ],

    // Register shared instances (created once, reused)
    'singletons' => [
        PostRepository::class,
    ],
];
```

**What module.php declares:**

| Key | Purpose |
|-----|---------|
| `bindings` | Map interfaces to concrete implementations |
| `singletons` | Classes that should only be instantiated once |

---

## Application Bootstrap

```php
use Marko\Core\Application;

$app = Application::boot(dirname(__DIR__));
$app->handleRequest();
```

---

## Dependency Injection

Use constructor injection only:

```php
use Marko\Cache\Contracts\CacheInterface;

readonly class ProductService
{
    public function __construct(
        private CacheInterface $cache,
    ) {}
}
```

**Binding types in module.php:**

```php
// Direct binding
'bindings' => [
    SomeInterface::class => SomeImplementation::class,
],

// Factory binding
'bindings' => [
    SomeInterface::class => function (ContainerInterface $container): SomeInterface {
        return new SomeImplementation(
            $container->get(OtherDep::class),
        );
    },
],
```

---

## HTTP Routing

Use routing attributes:

```php
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Attributes\Route;
use Marko\Routing\Attributes\Middleware;

#[Get('/path')]
#[Middleware(AuthMiddleware::class)]
readonly class MyController
{
    public function __construct(
        private SomeService $service,
    ) {}

    public function handle(): Response
    {
        return response()->json(['status' => 'ok']);
    }
}
```

**Available attributes:**

| Attribute | HTTP Method |
|-----------|-------------|
| `#[Get($path)]` | GET |
| `#[Post($path)]` | POST |
| `#[Put($path)]` | PUT |
| `#[Delete($path)]` | DELETE |
| `#[Patch($path)]` | PATCH |
| `#[Route($methods, $path)]` | Custom |

---

## Commands

Create in `src/Command/<Name>Command.php`:

```php
use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;

#[Command(name: 'my:command', description: 'Does something')]
readonly class MyCommand implements CommandInterface
{
    public function __construct(
        private SomeService $service,
    ) {}

    public function execute(Input $input, Output $output): int
    {
        $output->writeLine('Hello from command');
        return 0;
    }
}
```

---

## Database

### Migrations

```php
use Marko\Database\Migration\Migration;

readonly class CreateUsersTable implements Migration
{
    public function up(ConnectionInterface $connection): void
    {
        $connection->execute('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
    }
}
```

### Seeders

```php
use Marko\Database\Seed\Seeder;

readonly class UserSeeder implements Seeder
{
    public function run(ConnectionInterface $connection): void
    {
        $connection->execute(
            'INSERT INTO users (email, password) VALUES (?, ?)',
            ['user@example.com', password_hash('password', PASSWORD_DEFAULT)]
        );
    }
}
```

---

## Customization

### Preferences

Swap an interface's implementation entirely:

```php
// In your module.php
return [
    'preferences' => [
        OriginalInterface::class => YourReplacement::class,
    ],
];
```

### Plugins

Modify method input/output without replacing the class:

```php
// A plugin intercepts and modifies method calls
return [
    'plugins' => [
        SomeClass::class => [
            'someMethod' => MyPlugin::class,
        ],
    ],
];
```

### Events & Observers

React to things happening in the system:

```php
// Define event
$dispatcher->dispatch(new UserRegisteredEvent($user));

// Observer listens
#[Observer(UserRegisteredEvent::class)]
readonly class SendWelcomeEmail
{
    public function handle(UserRegisteredEvent $event): void
    {
        // Send email
    }
}
```

---

## Code Conventions

1. **Always use strict types:**
   ```php
   declare(strict_types=1);
   ```

2. **Type declarations everywhere:**
   ```php
   public function __construct(
       private SomeType $param,
   ): void {}

   public function method(): ReturnType { ... }
   ```

3. **Use `readonly` classes** for immutability

4. **PSR-4 autoloading** in composer.json

---

## Project Structure

```
project/
├── app/                    # Application modules (highest priority)
│   └── module-name/
│       ├── composer.json
│       ├── module.php
│       └── src/
├── modules/              # Third-party modules
├── packages/             # Local packages
├── vendor/              # Composer packages (base defaults)
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   └── index.php       # Entry point
├── resources/
│   └─��� views/         # Templates
├── storage/
│   └── data/         # Data files
└── tests/              # Tests
```

---

## CLI Commands

```bash
# Start development server
marko up

# Run migrations
marko db:migrate

# Seed database
marko db:seed

# Rebuild database
marko db:rebuild

# List commands
marko list
```

---

## Interface/Implementation Split

Marko packages follow a deliberate pattern: interfaces and implementations are separate packages.

```
marko/cache          → CacheInterface (the contract)
marko/cache-file     → FileCacheDriver (file-based)
marko/cache-redis    → RedisCacheDriver (Redis-based)
```

Switch implementations by changing bindings in module.php — zero application code changes.

---

## Resources

- [Official Docs](https://marko.build/docs)
- [GitHub](https://github.com/markshust/marko)
- [Package Reference](https://marko.build/docs/packages/core/)