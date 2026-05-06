// Dashboard Page-specific JS - charts, tables, atď.
import { initAdminTables } from '../../components/admin-table.js';
import { initCharts } from '../../components/charts.js';
import { initDashboard } from './dashboard.js';

console.log('Dashboard Page initialized');

// Initialize all dashboard components
document.addEventListener('DOMContentLoaded', () => {
    initAdminTables();
    initCharts();
    initDashboard();
});