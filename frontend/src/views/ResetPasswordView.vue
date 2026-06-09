<template>
  <div class="auth-layout">
    <div class="auth-container">
      <!-- Left Panel - Branding -->
      <div class="brand-panel">
        <div class="brand-content">
          <div class="brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
          </div>
          <h1 class="brand-title">New Password</h1>
          <p class="brand-subtitle">Choose a strong new password to secure your account.</p>
        </div>
      </div>

      <!-- Right Panel - Form -->
      <div class="form-panel">
        <div class="form-content">
          <h2 class="form-title">Reset Password</h2>
          <p class="form-subtitle">Enter your new password below</p>

          <form @submit.prevent="handleReset" class="auth-form">
            <div class="form-group">
              <label for="email">Email Address</label>
              <div class="input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a0aec0" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <input
                  id="email"
                  type="email"
                  v-model="email"
                  placeholder="you@example.com"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="password">New Password</label>
              <div class="input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a0aec0" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input
                  id="password"
                  type="password"
                  v-model="password"
                  placeholder="Minimum 8 characters"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="password_confirmation">Confirm Password</label>
              <div class="input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a0aec0" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input
                  id="password_confirmation"
                  type="password"
                  v-model="passwordConfirmation"
                  placeholder="Repeat your new password"
                  required
                />
              </div>
            </div>

            <button type="submit" class="btn-submit" :disabled="isLoading">
              {{ isLoading ? 'Resetting...' : 'Reset Password' }}
            </button>
          </form>

          <p v-if="error" class="error-msg">{{ error }}</p>
          <p v-if="successMsg" class="success-msg">{{ successMsg }}</p>

          <div class="form-footer">
            <p><router-link to="/login" class="link">← Back to Sign in</router-link></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'

const route = useRoute()
const router = useRouter()

const email = ref((route.query.email as string) || '')
const password = ref('')
const passwordConfirmation = ref('')
const error = ref('')
const successMsg = ref('')
const isLoading = ref(false)

async function handleReset() {
  error.value = ''
  successMsg.value = ''
  isLoading.value = true

  try {
    const response = await api.post('/auth/reset-password', {
      token: route.query.token,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    successMsg.value = response.data.message + ' Redirecting to login...'
    setTimeout(() => {
      router.push('/login')
    }, 2500)
  } catch (err: any) {
    if (err?.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join('. ')
    } else {
      error.value = err?.response?.data?.message || 'Failed to reset password.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
  padding: 1rem;
}

.auth-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  max-width: 900px;
  width: 100%;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0,0,0,0.12);
}

.brand-panel {
  background: #1a1a1a;
  color: white;
  padding: 3rem 2.5rem;
  display: flex;
  align-items: center;
}

.brand-content { width: 100%; }

.brand-icon {
  width: 64px;
  height: 64px;
  background: rgba(255,255,255,0.1);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.brand-title {
  font-size: 1.8rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
}

.brand-subtitle {
  font-size: 0.9rem;
  color: #a0aec0;
  line-height: 1.5;
}

.form-panel {
  background: white;
  padding: 3rem 2.5rem;
  display: flex;
  align-items: center;
}

.form-content { width: 100%; }

.form-title {
  font-size: 1.6rem;
  font-weight: 800;
  color: #1a202c;
  margin-bottom: 0.3rem;
}

.form-subtitle {
  font-size: 0.9rem;
  color: #a0aec0;
  margin-bottom: 2rem;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 0.4rem;
}

.input-wrapper {
  display: flex;
  align-items: center;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0 0.8rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input-wrapper:focus-within {
  border-color: #1a1a1a;
  box-shadow: 0 0 0 3px rgba(26, 26, 26, 0.08);
}

.input-wrapper svg { flex-shrink: 0; }

.input-wrapper input {
  flex: 1;
  border: none;
  outline: none;
  padding: 0.65rem 0.6rem;
  font-size: 0.95rem;
  background: transparent;
  color: #1a202c;
}

.input-wrapper input::placeholder { color: #cbd5e0; }

.btn-submit {
  width: 100%;
  padding: 0.75rem;
  background: #1a1a1a;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
  margin-top: 0.3rem;
}

.btn-submit:hover { background: #333; }
.btn-submit:disabled { background: #a0aec0; cursor: not-allowed; }

.error-msg {
  color: #e53e3e;
  font-weight: 600;
  font-size: 0.9rem;
  margin-top: 1rem;
  text-align: center;
}

.success-msg {
  color: #38a169;
  font-weight: 600;
  font-size: 0.9rem;
  margin-top: 1rem;
  text-align: center;
}

.form-footer {
  margin-top: 1.5rem;
  text-align: center;
  font-size: 0.9rem;
  color: #718096;
}

.link {
  color: #1a1a1a;
  font-weight: 700;
  text-decoration: none;
}

.link:hover { color: #4a5568; }

@media (max-width: 700px) {
  .auth-container { grid-template-columns: 1fr; }
  .brand-panel { padding: 2rem 1.5rem; }
  .form-panel { padding: 2rem 1.5rem; }
}
</style>
