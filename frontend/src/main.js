import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import './assets/tailwind.css';
import { initAntiDebug, clearConsolePeriodically } from './utils/antiDebug';

// Initialize anti-debug protection
initAntiDebug();
clearConsolePeriodically();

const app = createApp(App);
app.use(router);
app.mount('#app');
