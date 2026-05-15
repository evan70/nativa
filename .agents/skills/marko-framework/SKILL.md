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
**Flat vendor pattern (Nativa project):** When packages live in `packages/` and are linked via
`vendor/marko -> ../packages` symlink, Marko's `discoverInVendor('vendor/')` finds them as a
two-level structure (`marko/package-name/`), matching upstream expectations. This avoids
modifying ModuleDiscovery.

```bash
# Create the symlink (required for ModuleDiscovery to work):
ln -s ../packages vendor/marko
```


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
| `preferences` | Entirely replace one implementation with another (global override) |
| `boot` | Callback invoked after all modules are loaded (for registration, listeners) |
| `plugins` | Intercept method inputs/outputs on objects |
|
**Extended module metadata (via composer.json `extra.marko`):**

```json
{
    "extra": {
        "marko": {
            "module": true,
            "group": "admin",
            "routes": ["/admin/*"],
            "idleTimeout": "5m",
            "isCore": false
        }
    }
}
```

| Field | Purpose |
|-------|---------|
| `group` | Logical group name for ModuleGroupManager (idle eviction, route activation) |
| `routes` | Route patterns that trigger group activation (fnmatch-style, e.g. `/admin/*`) |
| `idleTimeout` | Inactivity timeout before group is evicted (e.g. `5m`, `1h`) |
| `isCore` | Core groups are never evicted and are active by default |
| `middleware` | (optional) Route middleware class names, applied to all group routes |
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

### Module Groups & Idle Eviction (Nativa project)

The `modules/init` module provides a `ModuleGroupManager` for organizing modules into
groups with idle timeout eviction — useful for admin panels, API backends, and
other non-core features that don't need to stay loaded.

**How groups work:**
- Each module declares its group via `extra.marko.group` in `composer.json`
- Core groups (`isCore: true`) are active by default and never evicted
- Non-core groups start inactive and activate when a matching route is hit
- Idle groups are evicted after their timeout (their bindings are removed from the container)

```php
use App\Init\Module\ModuleGroupManager;
use App\Init\Module\ModuleGroupManagerInterface;
use Marko\Core\Container\ContainerInterface;

// Register a module group
$manager = $container->get(ModuleGroupManagerInterface::class);
$manager->registerGroup($module->manifest); // from Manifest metadata

// Evict idle groups
$manager->evictIfIdle('admin', '5m');

// Check if group is active
if ($manager->isGroupActive('admin')) {
    // ...
}
```

**Config (`config/module.php`):**

```php
return [
    'eviction' => [
        'enabled' => true,
        'default' => '5m',       // default idle timeout
        'check_interval' => '1m', // how often to check
    ],
    'route_guard' => false, // blocks routes if group is not active
    'auto_activate_routes' => [
        '/mark/*',
        '/blog/*',
    ],
];
```

**Container unbind:** The `modules/init` Container extension adds `unbind()`, `unbindGroup()`,
and `unbindAll()` methods used by eviction. These remove bindings and shared instances from
the container, effectively deactivating a module group.

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

## Integration with AI Factory Skills

When working with Marko in this project, combine with these skills:

| Task | Use Skill |
|------|-----------|
| Code review | `/aif-review` — checks for DI patterns, routing attributes |
| Implementation | `/aif-implement` — includes PHPStan verification |
| Planning | `/aif-plan` — includes module setup tasks |
| Bug fixes | `/aif-fix` — includes PHP logging patterns |
| Best practices | `/aif-best-practices` — now references this skill |
| Code review | `/aif-review` — includes PHP/PHPStan checks |

**Run PHPStan before committing:**
```bash
php vendor/bin/phpstan analyze --memory-limit=256M
```

**Verify with Marko CLI:**
```bash
php marko list
```

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