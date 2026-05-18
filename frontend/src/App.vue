<script setup lang="ts">
</script>

<template>
  <div id="app">
    <RouterView />
  </div>
</template>

<style>
/* Global reset and base styles */
*,
*::before,
*::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f0f2f5;
  color: #1a202c;
  min-height: 100vh;
}

#app {
  min-height: 100vh;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.explorer-link {
  color: #a0aec0;
  font-size: 0.85rem;
  font-weight: 600;
  text-decoration: none;
  padding: 0.35rem 0.75rem;
  border: 1px solid #444;
  border-radius: 6px;
  transition: color 0.2s, border-color 0.2s;
}

.explorer-link:hover {
  color: white;
  border-color: #a0aec0;
}
</style>
