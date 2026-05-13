---
title: nativa/cardboard
description: TypeScript UI components for Vanilla Cards and Marko Framework.
---

TypeScript UI components for Vanilla Cards and Marko Framework. Cardboard provides high-performance, framework-agnostic components designed for modern web applications.

## Installation

Cardboard is available as a pnpm package.

```bash
pnpm install @nativa/cardboard
```

## Core Architecture

Cardboard components are built on a common foundation that ensures consistency and reliability.

### BaseComponent

All UI components extend the `BaseComponent` class, which provides a standard lifecycle and integration with the Vanilla Cards `BaseSection` pattern.

#### Lifecycle Methods
- `init()`: Public method to initialize the component. It triggers the `onInit()` hook and the `onInit` callback from options.
- `destroy()`: Public method for cleanup. It triggers the `onDestroy()` hook and the `onDestroy` callback.
- `onInit()`: Abstract hook for component-specific setup logic.
- `onDestroy()`: Abstract hook for component-specific cleanup.

#### Configuration Options
All components accept a `ComponentOptions` object:
- `name`: A unique identifier for the component.
- `onInit`: Optional callback triggered after initialization.
- `onDestroy`: Optional callback triggered after destruction.

```typescript
import { BaseComponent, ComponentOptions } from '@nativa/cardboard';

interface MyConfig extends ComponentOptions {
  theme: 'dark' | 'light';
}

class MyComponent extends BaseComponent {
  protected onInit(): void {
    this.log('Component initialized');
  }

  protected onDestroy(): void {
    this.log('Component destroyed');
  }
}
```


## Components

### Article Editor

A comprehensive Markdown editor with real-time preview and advanced features.

#### Features
- **Markdown Support**: Built-in support for Markdown formatting.
- **Real-time Preview**: Live preview of the rendered content.
- **Autosave**: Local storage and server-side draft persistence.
- **Media Management**: Integrated image upload and gallery management.

#### Configuration Options

The `ArticleEditor` requires an `ArticleEditorConfig` object:

| Option | Type | Description |
|--------|------|-------------|
| `mode` | `'create' \| 'edit'` | The operation mode. |
| `articleId` | `string` | Optional ID for edit mode. |
| `csrfToken` | `string` | Security token for API requests. |
| `submitUrl` | `string` | Endpoint for saving the article. |
| `mediaUploadUrl` | `string` | Endpoint for image uploads. |
| `mediaLibraryUrl` | `string` | Endpoint for fetching existing media. |
| `articleData` | `EditorArticleModel` | Initial data for the editor. |
| `categories` | `EditorCategoryOption[]` | List of available categories. |
| `availableTags` | `EditorTagOption[]` | List of available tags. |
| `onError` | `(error: string) => void` | Custom error handler. |
| `onSubmit` | `(data: FormData) => void` | Custom submission handler. |

#### Advanced Features

- **Markdown Toolbar**: Integrated buttons for bold, italic, headings, links, and code blocks.
- **Image Upload**: Supports drag-and-drop and clipboard paste for images.
- **Autosave**: Automatically persists drafts to local storage every 30 seconds.
- **Live Preview**: Real-time rendering of Markdown content.
- **Keyboard Shortcuts**: Common shortcuts like `Ctrl+B` (Bold), `Ctrl+I` (Italic), and `Ctrl+S` (Save).


### Notification Manager

The `NotificationManager` provides a static API for displaying toast-style notifications.

#### Basic Usage

```typescript
import { NotificationManager } from '@nativa/cardboard';

NotificationManager.show({
  title: 'Success',
  message: 'Article published!',
  type: 'success',
  duration: 5000
});
```

#### Notification Options
- `title`: Optional bold heading.
- `message`: The main content of the notification.
- `type`: `'success' | 'error' | 'warning' | 'info'` (default: `'info'`).
- `duration`: Time in ms before auto-hiding. Set to `0` for persistent notifications.
- `position`: `'top-right' | 'bottom-right'`.
- `actions`: Array of buttons/links to display within the notification.

#### Notification Adapter

The `NotificationAdapter` (also aliased as `NotificationService`) provides a simpler, functional API and support for cross-page notifications.

```typescript
import { NotificationService } from '@nativa/cardboard';

// Immediate notifications
NotificationService.success('Done!');
NotificationService.error('Failed to save.');

// Confirmation dialog
NotificationService.showConfirm({
  message: 'Are you sure you want to delete this article?',
  onConfirm: () => deleteArticle(),
  confirmLabel: 'Delete'
});

// Cross-page notifications (uses sessionStorage)
NotificationService.queueForNextPage('Welcome back!', 'info');
```


## Utilities

Cardboard includes a lightweight utility suite for common web development tasks.

### DOM Utility
The `dom` utility provides a set of helpers for cleaner DOM manipulation.

- `query<T>(selector, context)`: Typed `querySelector`.
- `queryAll<T>(selector, context)`: Typed `querySelectorAll` returning an array.
- `createElement(tag, attrs, children)`: Functional element creation.
- `delegate(parent, selector, event, handler)`: Standard event delegation with automatic cleanup.
- `show(el)` / `hide(el)`: Visibility toggles.

### Event Utility
Helpers for managing event flow and custom events.

- `debounce(fn, wait)`: Standard debounce.
- `throttle(fn, wait)`: Standard throttle.
- `dispatch(el, event)`: Typed custom event dispatcher.
- `once(el, event, handler)`: Single-trigger event listener.
- `waitFor(el, event)`: Promise-based event waiting.

### API Utility
A comprehensive wrapper for `fetch` that handles JSON, errors, and FormData consistently.

```typescript
import { api } from '@nativa/cardboard';

// GET with query params
const result = await api.get('/api/users', { active: true });

// POST JSON
const result = await api.post('/api/users', { name: 'John' });

// Upload files
const result = await api.uploadFile('/api/upload', myFile, 'profile_pic');

if (result.ok) {
  console.log(result.value);
} else {
  console.error(result.error);
}
```

## Next Steps

- [View and template rendering](/docs/packages/view/)
- [Admin panel documentation](/docs/packages/admin/)
- [Getting started guide](/docs/getting-started/introduction/)


