import './auth/styles.css';

document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('.auth-form');
    
    forms.forEach((form) => {
        const submitBtn = form.querySelector('button[type="submit"]');
        
        form.addEventListener('submit', () => {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Please wait...';
            }
        });
    });
});
