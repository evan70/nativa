# Marko + HTMX Integration

## Status: ✅ Completed

## Architecture

```
Browser → HTTP Request → HtmxMiddleware → Controller → View → Browser
                           ↓
                    add Vary header
```

**Principle:** Controller uses `HtmxContext::fromRequest()` for HTMX detection. Middleware adds Vary header only.

---

## Implemented Components

### 1. HtmxMiddleware
**Location:** `modules/htmx/src/Middleware/HtmxMiddleware.php`

Adds `Vary: HX-Request` header to all HTMX responses for proper caching.

### 2. HtmxContext
**Location:** `modules/htmx/src/HtmxContext.php`

Factory pattern - only entry point is `fromRequest()`:

```php
readonly class HtmxContext
{
    private function __construct(private Request $request) {}

    public static function fromRequest(Request $request): ?self
    {
        if ($request->header('HX-Request') !== 'true') {
            return null;
        }
        return new self($request);
    }

    public function target(): ?string { ... }
    public function trigger(): ?string { ... }
    public function triggerName(): ?string { ... }
    public function currentUrl(): ?string { ... }
    public function isSwap(): bool { ... }
}
```

### 3. Module Registration
**Location:** `modules/htmx/module.php`

```php
return [
    'middleware' => [HtmxMiddleware::class],
];
```

### 4. Response::withHeader()
**Location:** `packages/routing/src/Http/Response.php`

Immutable header addition for readonly Response:

```php
public function withHeader(string $name, string $value): self
{
    $headers = $this->headers;
    $headers[$name] = $value;
    return new self($this->body, $this->statusCode, $headers);
}
```

### 5. ModuleMiddleware Field
**Location:** `packages/core/src/Module/ModuleManifest.php`

Modules can declare global middleware:

```php
public function __construct(
    // ... existing fields
    public array $middleware = [],
) {}
```

### 6. ModuleDiscovery Fix
**Location:** `packages/core/src/Module/ModuleDiscovery.php`

Added `middleware` to `withPathAndSource()` method.

### 7. Application Refactor
**Location:** `packages/core/src/Application.php`

`discoverGlobalMiddleware()` now collects from modules instead of hardcoded constants.

### 8. HTMX JS Asset
**Location:** `templates/src/core.ts`

```typescript
import 'htmx.org';
```

---

## Usage in Controllers

```php
use App\Htmx\HtmxContext;
use Marko\Routing\Http\Request;

readonly class ArticleController
{
    public function list(Request $request): Response
    {
        $htmx = HtmxContext::fromRequest($request);
        $articles = $this->repository->all();

        if ($htmx !== null && $htmx->isSwap()) {
            // Return partial for HTMX swap
            return Response::html(
                $this->view->renderPartial('articles/_list', ['articles' => $articles])
            );
        }

        // Return full page
        return $this->view->render('articles/index', ['articles' => $articles]);
    }
}
```

---

## HTMX Attributes Reference

| Attribute | PHP Method | Description |
|-----------|-----------|-------------|
| `hx-get` | GET | Fetch URL |
| `hx-post` | POST | Submit to URL |
| `hx-target` | `target()` | CSS selector for content |
| `hx-trigger` | `trigger()` | Event that triggers |
| `hx-trigger-name` | `triggerName()` | Named trigger |
| `hx-swap` | - | Swap style (innerHTML, outerHTML, etc.) |
| `hx-push-url` | `shouldPushUrl()` | Push new URL |
| `hx-current-url` | `currentUrl()` | Current URL |
| `hx-boost` | `getBoosted()` | Boost normal links |
| `hx-history-elt` | - | Element to use for history |

---

## Files Created/Modified

### Created
- `modules/htmx/src/HtmxContext.php`
- `modules/htmx/src/Middleware/HtmxMiddleware.php`
- `modules/htmx/module.php`
- `modules/htmx/composer.json`

### Modified
- `packages/routing/src/Http/Response.php` - added `withHeader()`
- `packages/core/src/Module/ModuleManifest.php` - added `middleware` field
- `packages/core/src/Module/ModuleDiscovery.php` - fixed `withPathAndSource()`
- `packages/core/src/Application.php` - refactored `discoverGlobalMiddleware()`
- `templates/src/core.ts` - added HTMX import

---

## Testing

```bash
# Check middleware is registered
php marko module:bindings | grep Htmx

# Check global middleware
php -r "
\$app = \Marko\Core\Application::boot('/home/evan/dev/05/nativa');
\$reflection = new ReflectionClass(\$app);
\$method = \$reflection->getMethod('discoverGlobalMiddleware');
\$method->setAccessible(true);
print_r(\$method->invoke(\$app));
"

# Test Vary header
curl -sI -H "HX-Request: true" http://localhost/
# Should include: Vary: HX-Request
```

---

## What's NOT Implemented (Future)

- **HtmxView Decorator** - auto partial detection in View layer
- **Module-level middleware** - middleware for specific routes only
- **CSRF validation for HTMX**
- **Rate limiting for HTMX**
- **OOB swap support in response helpers**

These can be added as separate tasks when needed.

---

## Dependencies

- `marko/routing` - for Response, Request, MiddlewareInterface
- Module must be registered in `composer.json` autoload:
```json
"App\\Htmx\\": "modules/htmx/src/"
```

---

Priority: Completed