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
</style>
