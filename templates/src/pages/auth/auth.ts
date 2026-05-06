// pages/auth/auth.ts — Auth page specific JS
import './auth.css';

import { initAuthForm } from './auth-form.js';

console.log('Auth page initialized');

document.addEventListener('DOMContentLoaded', () => {
  initAuthForm();
});
