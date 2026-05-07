// Admin table functionality
export function initAdminTables() {
  // Make tables interactive
  const tables = document.querySelectorAll('.table');
    
  tables.forEach(table => {
    // Add sort functionality if needed
    const headers = table.querySelectorAll('th[data-sortable]');
    headers.forEach(header => {
      header.style.cursor = 'pointer';
      header.addEventListener('click', () => {
        sortTable(table, Array.from(headers).indexOf(header));
      });
    });
      
    // Add hover effects, row selection, etc.
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
      row.addEventListener('click', () => {
        // Toggle selection
        rows.forEach(r => r.classList.remove('selected'));
        row.classList.add('selected');
      });
    });
  });
}

function sortTable(table, columnIndex) {
  const tbody = table.querySelector('tbody');
  if (!tbody) return;
    
  const rows = Array.from(tbody.querySelectorAll('tr'));
  const isAsc = table.dataset.sortDirection !== 'asc';
    
  rows.sort((a, b) => {
    const aText = a.children[columnIndex]?.textContent || '';
    const bText = b.children[columnIndex]?.textContent || '';
    return isAsc 
      ? aText.localeCompare(bText) 
      : bText.localeCompare(aText);
  });
    
  rows.forEach(row => tbody.appendChild(row));
  table.dataset.sortDirection = isAsc ? 'asc' : 'desc';
}
