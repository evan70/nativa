[← Getting Started](getting-started.md) · [Back to README](../README.md) · [Deployment →](deployment.md)

# CLI Reference

The `marko` CLI provides several useful commands for development.

## Database Commands

| Command | Description |
|---------|-------------|
| `./marko db:migrate` | Apply database migrations |
| `./marko db:rollback` | Rollback the last batch of migrations |
| `./marko db:status` | Show migration status |
| `./marko db:diff` | Show differences between entity schema and database |
| `./marko db:rebuild` | Reset and re-run all migrations (clean slate) |
| `./marko db:seed` | Run database seeders |

## System Commands

| Command | Description |
|---------|-------------|
| `./marko module:list` | Show all modules and their status |
| `./marko module:bindings` | Show module bindings and groups |
| `./marko module:activate <group>` | Activate a module group |
| `./marko module:unbind <group>` | Unbind a module group |
| `./marko module:evict [group]` | Evict idle modules |
| `./marko log:clear` | Clear old log files |
| `./marko session:gc` | Run session garbage collection |
| `./marko list` | Show all available commands |

## Queue Commands

| Command | Description |
|---------|-------------|
| `./marko queue:work` | Process jobs from the queue |
| `./marko queue:status` | Show queue statistics |

## Server Commands

| Command | Description |
|---------|-------------|
| `./marko up` | Start the development server |
| `./marko serve` | Serve the application |

## Usage Examples

### Apply migrations
```bash
./marko db:migrate
```

### Rollback last migration
```bash
./marko db:rollback
```

### Check migration status
```bash
./marko db:status
```

### Rebuild database (fresh start)
```bash
./marko db:rebuild
```

### List all modules
```bash
./marko module:list
```

### Start dev server
```bash
./marko up
```

## See Also

- [Getting Started](getting-started.md) — Initial setup guide
- [Deployment Guide](deployment.md) — Production deployment