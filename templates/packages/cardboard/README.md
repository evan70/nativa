# Cardboard

UI components for Vanilla Cards.

Cardboard is a collection of high-performance, framework-agnostic UI components built with TypeScript and designed to work seamlessly with the Vanilla Cards design system.

## Features

- **Article Editor**: A powerful Markdown-based editor with real-time preview, autosave, and media management.
- **Notification Manager**: Toast-style notifications for user feedback.
- **Base Component**: An architectural foundation for building consistent, lifecycle-aware UI components.
- **Utility Suite**: Lightweight helpers for DOM manipulation, events, and API interactions.

## Quick Start

### Installation

Cardboard is designed to be used as a package within the Marko Framework ecosystem.

```bash
pnpm install @nativa/cardboard
```

### Usage: Article Editor

```typescript
import { ArticleEditor } from '@nativa/cardboard';

const editor = new ArticleEditor(container, config);
editor.init();
```

### Usage: Notifications

```typescript
import { NotificationService } from '@nativa/cardboard';

// Simple toast
NotificationService.success('Article saved!');

// Confirm dialog
NotificationService.showConfirm({
  message: 'Discard changes?',
  onConfirm: () => resetForm()
});
```

### Usage: Utilities

```typescript
import { dom, api, events } from '@nativa/cardboard';

// DOM
const btn = dom.query('.save-btn');
dom.addClass(btn, 'is-loading');

// API
const result = await api.get('/api/articles');

// Events
const debouncedSearch = events.debounce(search, 300);
```


## Architecture

Cardboard components follow a consistent pattern by extending the `BaseComponent` class:

1.  **Standardized Lifecycle**: Components use `init()` and `destroy()` methods to manage their lifecycle.
2.  **Hooks**: Subclasses implement `onInit()` and `onDestroy()` abstract hooks for component-specific logic.
3.  **Encapsulation**: Components manage their own state and DOM interactions within a container element.
4.  **Logging**: Built-in `log()` helper for consistent console output with component prefixes.


## Documentation

Detailed documentation for each component and utility can be found in the [official documentation](https://nativa.dev/docs/packages/cardboard).

- [Article Editor](https://nativa.dev/docs/packages/cardboard#article-editor)
- [Notifications](https://nativa.dev/docs/packages/cardboard#notifications)
- [Utilities](https://nativa.dev/docs/packages/cardboard#utilities)

## License

MIT
