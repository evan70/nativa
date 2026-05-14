// Sidebar toggle functionality
export function initSidebar() {
  const navbar = document.querySelector('.navbar');
  const toggleBtn = navbar?.querySelector('.sidebar-toggle');
  const sidebar = document.querySelector('.layout-admin__sidebar');
  const body = document.body;
  
  if (!toggleBtn || !sidebar) return;
    
  toggleBtn.addEventListener('click', (e) => {
    e.preventDefault();
    // Toggle collapsed state on body
    body.classList.toggle('is-collapsed');
    // Also toggle open state for mobile overlay
    sidebar.classList.toggle('sidebar--open');
  });
    
  // Close on mobile when clicking outside
  document.addEventListener('click', (e) => {
    if (window.innerWidth < 768 && 
        sidebar.classList.contains('sidebar--open') &&
        !sidebar.contains(e.target) && 
        !toggleBtn.contains(e.target)) {
      sidebar.classList.remove('sidebar--open');
    }
  });
}
