/**
 * NotificationAdapter - Bridges old NotificationService API to NotificationManager
 * 
 * Provides backward compatibility for code that expects:
 * - NotificationService.error(message)
 * - NotificationService.showConfirm(options)
 * - NotificationService.queueForNextPage(message, type)
 * 
 * Maps to NotificationManager.show(options) with appropriate type and options.
 */

import { NotificationManager, type NotificationType, type NotificationPosition } from '../components/NotificationManager.ts';

// Re-export types for consumers
export type { NotificationType, NotificationPosition } from '../components/NotificationManager.ts';

interface ConfirmOptions {
  title?: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  onConfirm?: () => void;
  onCancel?: () => void;
  position?: NotificationPosition;
}

interface QueuedNotification {
  message: string;
  type: NotificationType;
}

/**
 * Adapter providing NotificationService-compatible static API
 */
export const NotificationAdapter = {
  /**
   * Show error notification
   * @param message - Error message to display
   * @param duration - How long to show (ms), 0 = persistent
   */
  error(message: string, duration = 5000): void {
    NotificationManager.show({
      message,
      type: 'error',
      duration,
    });
  },

  /**
   * Show success notification
   * @param message - Success message to display
   * @param duration - How long to show (ms), 0 = persistent
   */
  success(message: string, duration = 5000): void {
    NotificationManager.show({
      message,
      type: 'success',
      duration,
    });
  },

  /**
   * Show warning notification
   * @param message - Warning message to display
   * @param duration - How long to show (ms), 0 = persistent
   */
  warning(message: string, duration = 5000): void {
    NotificationManager.show({
      message,
      type: 'warning',
      duration,
    });
  },

  /**
   * Show info notification
   * @param message - Info message to display
   * @param duration - How long to show (ms), 0 = persistent
   */
  info(message: string, duration = 5000): void {
    NotificationManager.show({
      message,
      type: 'info',
      duration,
    });
  },

  /**
   * Show confirmation dialog with action buttons
   * @param options - Confirm dialog options
   */
  showConfirm(options: ConfirmOptions): void {
    const {
      title = 'Confirm',
      message,
      confirmLabel = 'Confirm',
      cancelLabel = 'Cancel',
      onConfirm,
      onCancel,
      position = 'top-right',
    } = options;

    NotificationManager.show({
      title,
      message,
      type: 'warning',
      duration: 0, // Persistent until action taken
      position,
      actions: [
        {
          label: cancelLabel,
          callback: onCancel,
          close: true,
          primary: false,
        },
        {
          label: confirmLabel,
          callback: onConfirm,
          close: true,
          primary: true,
        },
      ],
    });
  },

  /**
   * Queue notification for display on next page load
   * @param message - Message to display
   * @param type - Notification type ('success', 'error', 'warning', 'info')
   */
  queueForNextPage(message: string, type: NotificationType = 'info'): void {
    const key = '_cardboard_notifications';
    const queued: QueuedNotification[] = JSON.parse(sessionStorage.getItem(key) || '[]');
    queued.push({ message, type });
    sessionStorage.setItem(key, JSON.stringify(queued));
  },

  /**
   * Flush queued notifications (call on page load)
   * Displays all queued notifications and clears the queue
   */
  flushQueue(): void {
    const key = '_cardboard_notifications';
    const queued: QueuedNotification[] = JSON.parse(sessionStorage.getItem(key) || '[]');
    sessionStorage.removeItem(key);

    queued.forEach(({ message, type }, index) => {
      // Stagger notifications slightly for visual effect
      setTimeout(() => {
        NotificationManager.show({
          message,
          type,
          duration: 5000,
        });
      }, index * 200);
    });
  },
};

// Legacy alias for existing code
export const NotificationService = NotificationAdapter;

// Flush queued notifications on DOMContentLoaded
if (typeof document !== 'undefined') {
  document.addEventListener('DOMContentLoaded', () => {
    NotificationAdapter.flushQueue();
  });
}