# marko/mark

Unified Administration module for Marko Framework. Handles both Authentication/Authorization and the Admin Panel UI.

## Features

- **Authentication**: Login/Logout with secure password hashing.
- **Authorization**: Role-based access control (RBAC) with granular permissions.
- **Admin Panel UI**: Dashboard, menu management, and UI registry for other modules.
- **Unified Connection**: Uses a consolidated `mark` database connection.

## Installation

This module is part of the Marko Skeleton and is located in `modules/mark`.

## Usage

### Authentication & Permissions

```php
use Marko\Mark\Attributes\RequiresPermission;
use Marko\Mark\Middleware\MarkMiddleware;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Middleware;

class ProductController
{
    #[Get('/mark/catalog/products')]
    #[Middleware(MarkMiddleware::class)]
    #[RequiresPermission(permission: 'catalog.products.view')]
    public function index(): Response
    {
        // Only users with 'catalog.products.view' permission can access
    }
}
```

### Admin Sections

Register new admin sections using the `#[AdminSection]` attribute:

```php
use Marko\Admin\Attributes\AdminSection;
use Marko\Admin\Contracts\AdminSectionInterface;

#[AdminSection(id: 'catalog', label: 'Catalog', icon: 'package', sortOrder: 50)]
class CatalogAdminSection implements AdminSectionInterface
{
    // ... implement methods
}
```

## Configuration

Configuration is managed via `config/mark.php` and `config/admin.php`.
URLs are prefixed with `/mark` by default.
