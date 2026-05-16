// pages/articles/articles.ts — Blog article pages JS
import './articles.css';

console.log('Articles page initialized');

// Load htmx for dynamic content (pagination, filtering, etc.)
import('htmx.org').then(({ default: htmx }) => {
    htmx.process(document.body);
    console.log('htmx loaded for articles');
});
