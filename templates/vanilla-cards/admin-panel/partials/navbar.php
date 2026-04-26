<!-- NAVBAR SECTION -->
<nav class="navbar" data-section="navbar">
    <div class="navbar__container container--fluid">
        <div class="navbar__left">
            <button class="icon-btn navbar__toggle sidebar-toggle" aria-label="Toggle Sidebar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <a href="/mark" class="navbar__brand">
                <svg class="navbar__logo" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L2 22h20L12 2z"/>
                </svg>
                Marko Admin
            </a>
        </div>
        
        <div class="navbar__actions">
            <button class="icon-btn theme-toggle navbar__theme-toggle" aria-label="Toggle Theme">
                <svg class="theme-toggle__sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                <svg class="theme-toggle__moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
            <div class="navbar__user">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($currentUser?->getName() ?? 'Marko Admin') ?>&background=d4af37&color=fff" alt="User Avatar" class="avatar" style="width: 32px; height: 32px; border-radius: 50%;">
            </div>
        </div>
    </div>
</nav>
