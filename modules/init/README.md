# Module Groups

This module provides lazy loading and idle eviction for Marko modules.

## Setup

Add group configuration to module's `composer.json`:

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

### Configuration Options

| Option | Type | Description |
|--------|------|-------------|
| `group` | string | Unique group identifier |
| `routes` | array | Route patterns that activate this group |
| `idleTimeout` | string | Idle timeout (e.g., "5m", "1h") |
| `isCore` | boolean | Core groups are never evicted |

## Usage

```bash
# List all modules
php marko module:list

# Show bindings and groups
php marko module:bindings

# Activate a group
php marko module:activate mark

# Unbind a group (deactivate)
php marko module:unbind mark

# Unbind and remove from registry
php marko module:unbind mark --force

# Evict idle groups
php marko module:evict
php marko module:evict mark
```

## Configuration

Edit `config/module.php`:

```php
return [
    'eviction' => [
        'enabled' => true,
        'default' => '5m',
        'check_interval' => '1m',
    ],
    
    'route_guard' => false,  // EXPERIMENTAL: block routes if group inactive
    
    'auto_activate_routes' => [
        '/mark/*',
        '/blog/*',
    ],
];
```

## Core Groups

These groups are always active and cannot be evicted:
- core (routing)
- env
- config
- errors
- session

## How It Works

1. **Boot**: ModuleGroupManager registers all groups from module manifests
2. **Activation**: When a group is activated, its bindings are added to container
3. **Idle**: After timeout, group can be evicted (bindings removed)
4. **Re-activation**: If route matches again, group is re-activated

## File Structure

```
modules/init/
├── module.php              # Bootstrap
├── src/
│   ├── Module/
│   │   ├── ModuleGroup.php
│   │   ├── ModuleGroupManager.php
│   │   └── ModuleGroupManagerInterface.php
│   ├── Controller/
│   │   ├── ModuleActivateController.php
│   │   ├── ModuleBindingsController.php
│   │   ├── ModuleEvictController.php
│   │   └── ModuleUnbindController.php
│   ├── Event/
│   │   └── RequestHandledEvent.php
│   └── Middleware/
│       └── GroupRouteGuard.php   # EXPERIMENTAL
└── tests/
```
