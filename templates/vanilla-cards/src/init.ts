// init.ts - Runs before the body is parsed to prevent FOUC (Flash of Unstyled Content)

try {
  const storedTheme = localStorage.getItem('nativa-theme');
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  const themeToSet = storedTheme ? storedTheme : (systemPrefersDark ? 'dark' : 'light');
  
  document.documentElement.dataset.theme = themeToSet;
} catch (e) {
  console.warn('Could not initialize theme', e);
}
