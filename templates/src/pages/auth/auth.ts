// pages/auth/auth.ts — Auth page specific JS
import './auth.css';
import '../../core/components/notification.css';
import { NotificationManager } from '../../core/components/NotificationManager';

import { initAuthForm } from './auth-form.js';

console.log('Auth page initialized');

// Expose for template use
document.addEventListener('DOMContentLoaded', () => {
  initAuthForm();
});
