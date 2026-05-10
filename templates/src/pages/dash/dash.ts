// pages/dash/dash.ts — Dashboard page specific JS
import './dash.css';
import '../../core/components/table.css';
import '../../core/components/notification.css';
import { NotificationManager } from '../../core/components/NotificationManager';

import { initSidebar } from '../../core/components/sidebar.js';
import { initAdminTables } from '../../core/components/admin-table.js';
import { initCharts } from '../../core/components/charts.js';
import { initDashboard } from './dashboard.js';

console.log('Dashboard page initialized');

// Expose for template use
document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initAdminTables();
  initCharts();
  initDashboard();
});
