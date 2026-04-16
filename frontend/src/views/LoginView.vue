<template>
  <div class="login-container">
    <h2>Login</h2>

    <form @submit.prevent="handleLogin">
      <div>
        <label>Email</label>
        <input
          type="email"
          v-model="email"
          required
        />
      </div>

      <div>
        <label>Password</label>
        <input
          type="password"
          v-model="password"
          required
        />
      </div>

      <button type="submit">Login</button>
    </form>

    <p v-if="error" style="color: red;">
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')

async function handleLogin() {
  error.value = ''

  try {
    await auth.login(email.value, password.value)

    const role = auth.userRole

    if (role) {
      await router.push('/' + role.toLowerCase())
    } else {
      error.value = 'User role not found'
    }

  } catch (err: any) {
    error.value =
      err?.response?.data?.message ||
      'Login failed. Please check your credentials.'
  }
}
</script>

<style scoped>
.login-container {
  max-width: 400px;
  margin: 50px auto;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

input {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
}

button {
  margin-top: 10px;
  padding: 10px;
  cursor: pointer;
}
</style>