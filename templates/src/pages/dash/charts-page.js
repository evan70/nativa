// Dashboard charts specific to this page
export function initDashboardCharts() {
  // Charts are initialized in charts.js
  // This file can contain dashboard-specific chart logic
  initRealtimeUpdates();
}

function initRealtimeUpdates() {
  // Simulate realtime updates for stats
  setInterval(() => {
    updateRandomStat();
  }, 30000); // Update every 30 seconds
}

function updateRandomStat() {
  const stats = document.querySelectorAll('.card--stat h2');
  if (stats.length === 0) return;  
  
  const randomStat = stats[Math.floor(Math.random() * stats.length)];
  const currentValue = parseFloat(randomStat.textContent.replace(/[^0-9.-]+/g, ''));
  
  if (!isNaN(currentValue)) {
    const change = (Math.random() - 0.5) * 1000;
    const newValue = Math.max(0, currentValue + change);
    randomStat.textContent = formatCurrency(newValue);
    
    // Add flash effect
    randomStat.style.transition = 'color 0.3s ease';
    randomStat.style.color = 'var(--brand-emerald)';
    setTimeout(() => {
      randomStat.style.color = 'var(--color-text)';
    }, 1000);
  }
}

function formatCurrency(value) {
  return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

export { initDashboardCharts };
