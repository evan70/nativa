import initFireShowTheme from './fire-show';

export default {
  name: 'fire-show',
  cssPath: './assets/fire-show.css',
  jsPath: './fire-show.js',
  init() {
    initFireShowTheme();
  }
};

export { initFireShowTheme };
