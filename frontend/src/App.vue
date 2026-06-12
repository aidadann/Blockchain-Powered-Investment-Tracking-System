<script setup lang="ts">
</script>

<template>
  <div id="app">
    <RouterView />
  </div>
</template>

<style>
/* Global reset and base styles - design system loaded via main.css */
*,
*::before,
*::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--font-family);
  background: var(--color-bg);
  color: var(--color-text);
  min-height: 100vh;
}

#app {
  min-height: 100vh;
}
</style>
