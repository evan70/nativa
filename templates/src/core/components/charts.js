// Charts initialization for Cardboard admin
import Chart from 'chart.js/auto';

let chartInstances = {};

// Helper to resolve CSS custom properties to actual values
function resolveCSSVariable(name) {
  return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}

export function initCharts() {
  // Initialize dashboard charts if elements exist
  initRevenueChart();
  initSubscriptionsChart();
  initSalesChart();
}

function initRevenueChart() {
  const canvas = document.getElementById('revenue-chart');
  if (!canvas) return;
  
  if (typeof Chart === 'undefined') {
    console.warn('Chart.js not loaded. Add it with: pnpm add chart.js');
    return;
  }
  
  const emerald = resolveCSSVariable('--brand-emerald');
  
  chartInstances.revenue = new Chart(canvas, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [{
        label: 'Revenue',
        data: [30000, 35000, 40000, 45231, 48000, 52000],
        borderColor: emerald,
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: {
            color: resolveCSSVariable('--color-text')
          }
        }
      },
      scales: {
        y: {
          ticks: { color: resolveCSSVariable('--color-text-muted') },
          grid: { color: resolveCSSVariable('--color-border') }
        },
        x: {
          ticks: { color: resolveCSSVariable('--color-text-muted') },
          grid: { color: resolveCSSVariable('--color-border') }
        }
      }
    }
  });
}

function initSubscriptionsChart() {
  const canvas = document.getElementById('subscriptions-chart');
  if (!canvas) return;
  
  if (typeof Chart === 'undefined') return;
  
  const gold = resolveCSSVariable('--brand-gold');
  
  chartInstances.subscriptions = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: ['Q1', 'Q2', 'Q3', 'Q4'],
      datasets: [{
        label: 'New Subscriptions',
        data: [1200, 1900, 2100, 2350],
        backgroundColor: gold,
        borderRadius: 8
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: { color: resolveCSSVariable('--color-text') }
        }
      },
      scales: {
        y: {
          ticks: { color: resolveCSSVariable('--color-text-muted') },
          grid: { color: resolveCSSVariable('--color-border') }
        },
        x: {
          ticks: { color: resolveCSSVariable('--color-text-muted') },
          grid: { color: resolveCSSVariable('--color-border') }
        }
      }
    }
  });
}

function initSalesChart() {
  const canvas = document.getElementById('sales-chart');
  if (!canvas) return;
  
  if (typeof Chart === 'undefined') return;
  
  const emerald = resolveCSSVariable('--brand-emerald');
  const gold = resolveCSSVariable('--brand-gold');
  const sapphire = resolveCSSVariable('--brand-sapphire');
  
  chartInstances.sales = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: ['Digital', 'Physical', 'Subscription'],
      datasets: [{
        data: [45, 25, 30],
        backgroundColor: [emerald, gold, sapphire]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: resolveCSSVariable('--color-text') }
        }
      }
    }
  });
}

// Cleanup function
export function destroyCharts() {
  Object.values(chartInstances).forEach(chart => chart.destroy());
  chartInstances = {};
}
