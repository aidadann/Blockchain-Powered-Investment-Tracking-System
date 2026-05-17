<template>
  <div class="auth-layout">
    <div class="auth-container">
      <!-- Left Panel - Branding -->
      <div class="brand-panel">
        <div class="brand-content">
          <div class="brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          </div>
          <h1 class="brand-title">Investment Tracker</h1>
          <p class="brand-subtitle">Blockchain-Powered Investment Tracking System</p>
          <div class="brand-features">
            <div class="feature-item">
              <span class="feature-icon">🔒</span>
              <span>Secure blockchain verification</span>
            </div>
            <div class="feature-item">
              <span class="feature-icon">📊</span>
              <span>Real-time portfolio tracking</span>
            </div>
            <div class="feature-item">
              <span class="feature-icon">🛡️</span>
              <span>Role-based access control</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel - Register Form -->
      <div class="form-panel">
        <div class="form-content">
          <h2 class="form-title">Create Account</h2>
          <p class="form-subtitle">Get started with your investment journey</p>

          <form @submit.prevent="handleRegister" class="auth-form">
            <div class="form-group">
              <label for="name">Full Name</label>
              <div class="input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a0aec0" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input
                  id="name"
                  type="text"
                  v-model="name"
                  placeholder="John Doe"
                  required
                />
              </div>
            </div>

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
              <label for="password">Password</label>
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
              <label for="role">Register As</label>
              <div class="input-wrapper select-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a0aec0" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                <select id="role" v-model="roleId" required>
                  <option value="" disabled>Select your role</option>
                  <option value="1">Investor</option>
                  <option value="2">Admin</option>
                  <option value="3">Auditor</option>
                </select>
              </div>
            </div>

            <button type="submit" class="btn-register" :disabled="isLoading">
              {{ isLoading ? 'Creating account...' : 'Create Account' }}
            </button>
          </form>

          <p v-if="error" class="error-msg">{{ error }}</p>
          <p v-if="successMsg" class="success-msg">{{ successMsg }}</p>

          <div class="form-footer">
            <p>Already have an account? <router-link to="/login" class="link">Sign in</router-link></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const roleId = ref('')
const error = ref('')
const successMsg = ref('')
const isLoading = ref(false)

async function handleRegister() {
  error.value = ''
  successMsg.value = ''
  isLoading.value = true

  try {
    await api.post('/auth/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      role_id: Number(roleId.value),
    })

    successMsg.value = 'Account created successfully! Redirecting to login...'
    setTimeout(() => {
      router.push('/login')
    }, 2000)
  } catch (err: any) {
    if (err?.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join('. ')
    } else {
      error.value = err?.response?.data?.message || 'Registration failed. Please try again.'
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

/* ===== Brand Panel ===== */
.brand-panel {
  background: #1a1a1a;
  color: white;
  padding: 3rem 2.5rem;
  display: flex;
  align-items: center;
}

.brand-content {
  width: 100%;
}

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
  margin-bottom: 2rem;
  line-height: 1.5;
}

.brand-features {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  font-size: 0.9rem;
  color: #cbd5e0;
}

.feature-icon {
  font-size: 1.1rem;
}

/* ===== Form Panel ===== */
.form-panel {
  background: white;
  padding: 2.5rem;
  display: flex;
  align-items: center;
}

.form-content {
  width: 100%;
}

.form-title {
  font-size: 1.6rem;
  font-weight: 800;
  color: #1a202c;
  margin-bottom: 0.3rem;
}

.form-subtitle {
  font-size: 0.9rem;
  color: #a0aec0;
  margin-bottom: 1.5rem;
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

.input-wrapper svg {
  flex-shrink: 0;
}

.input-wrapper input,
.input-wrapper select {
  flex: 1;
  border: none;
  outline: none;
  padding: 0.65rem 0.6rem;
  font-size: 0.95rem;
  background: transparent;
  color: #1a202c;
  font-family: inherit;
}

.input-wrapper input::placeholder {
  color: #cbd5e0;
}

.select-wrapper select {
  cursor: pointer;
  appearance: none;
}

.btn-register {
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

.btn-register:hover { background: #333; }
.btn-register:disabled { background: #a0aec0; cursor: not-allowed; }

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
  transition: color 0.2s;
}

.link:hover { color: #4a5568; }

/* ===== Responsive ===== */
@media (max-width: 700px) {
  .auth-container {
    grid-template-columns: 1fr;
  }

  .brand-panel {
    padding: 2rem 1.5rem;
  }

  .brand-features { display: none; }

  .form-panel {
    padding: 2rem 1.5rem;
  }
}
</style>
