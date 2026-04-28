import './styles/cardboard.css';

// Import page-specific components
import { initSidebar } from './components/sidebar.js';
import { initAdminTables } from './components/admin-table.js';
import { initCharts } from './components/charts.js';
import { initDashboard } from './pages/dashboard/dashboard.js';

console.log('Cardboard Admin initialized');

// Expose to window for legacy support
(window as any).CardboardAdmin = {
  initSidebar,
  initAdminTables,
  initCharts,
  initDashboard
};

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initAdminTables();
  initCharts();
  initDashboard();
});
