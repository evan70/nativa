// pages/dash/dash.ts — Dashboard page specific JS
import './dash.css';

import { initSidebar } from '../../core/components/sidebar.js';
import { initAdminTables } from '../../core/components/admin-table.js';
import { initCharts } from '../../core/components/charts.js';
import { initDashboard } from './dashboard.js';

console.log('Dashboard page initialized');

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initAdminTables();
  initCharts();
  initDashboard();
});
