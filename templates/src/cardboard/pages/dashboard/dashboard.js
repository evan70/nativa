// Dashboard page-specific logic
export function initDashboard() {
  // Initialize any dashboard-specific functionality
  initWelcomeMessage();
  initStatCards();
  initQuickActions();
}

function initWelcomeMessage() {
  const header = document.querySelector('.dashboard-header');
  if (!header) return;
  
  // Add animation on load
  header.style.opacity = '0';
  header.style.transform = 'translateY(20px)';
  
  requestAnimationFrame(() => {
    header.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    header.style.opacity = '1';
    header.style.transform = 'translateY(0)';
  });
}

function initStatCards() {
  const cards = document.querySelectorAll('.stat-card');
  cards.forEach((card, index) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
      card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, index * 100);
  });
}

function initQuickActions() {
  const buttons = document.querySelectorAll('.quick-action');
  buttons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const action = btn.dataset.action;
      handleQuickAction(action);
    });
  });
}

function handleQuickAction(action) {
  switch(action) {
    case 'add-user':
      window.location.href = '/mark/users/create';
      break;
    case 'create-post':
      window.location.href = '/mark/posts/create';
      break;
    case 'view-reports':
      window.location.href = '/mark/reports';
      break;
    default:
      console.log('Unknown action:', action);
  }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
  initDashboard();
});
