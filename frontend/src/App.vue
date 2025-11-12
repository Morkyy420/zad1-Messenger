<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
:root {
  /* Default Purple Dream Theme */
  --color-primary: #8b5cf6;
  --color-primaryDark: #6d28d9;
  --color-primaryLight: #a78bfa;
  --color-primaryLighter: #c4b5fd;
  --color-background1: #1a1a2e;
  --color-background2: #16213e;
  --color-background3: #0f0f1e;
  --color-cardBg: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(0, 0, 0, 0.8) 100%);
  --color-border: rgba(139, 92, 246, 0.3);
  --color-text: #c4b5fd;
  --color-textLight: rgba(196, 181, 253, 0.7);
  --color-success: #86efac;
  --color-error: #fca5a5;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  margin: 0;
  padding: 0;
  background: linear-gradient(135deg, var(--color-background1) 0%, var(--color-background2) 50%, var(--color-background3) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  overflow: hidden;
  transition: all 0.3s ease;
}

/* Text Size Classes */
body.text-size-small {
  font-size: 14px;
}

body.text-size-medium {
  font-size: 16px;
}

body.text-size-large {
  font-size: 18px;
}

body.text-size-small h1 {
  font-size: 28px;
}

body.text-size-medium h1 {
  font-size: 36px;
}

body.text-size-large h1 {
  font-size: 42px;
}

body.text-size-small h2 {
  font-size: 22px;
}

body.text-size-medium h2 {
  font-size: 28px;
}

body.text-size-large h2 {
  font-size: 32px;
}

body.text-size-small h3 {
  font-size: 16px;
}

body.text-size-medium h3 {
  font-size: 20px;
}

body.text-size-large h3 {
  font-size: 24px;
}

#app {
  width: 100%;
  height: 100vh;
}
</style>
