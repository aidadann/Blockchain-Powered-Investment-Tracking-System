<template>
  <div class="auth-layout">
    <div class="auth-container">
      <div class="brand-panel">
        <div class="brand-content">
          <div class="brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          </div>
          <h1 class="brand-title">Investment Tracker</h1>
          <p class="brand-subtitle">Blockchain-Powered Investment Tracking System</p>
          <div class="brand-features">
            <div class="feature-item">
              <span class="feature-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
              <span>Secure blockchain verification</span>
            </div>
            <div class="feature-item">
              <span class="feature-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg></span>
              <span>Real-time portfolio tracking</span>
            </div>
            <div class="feature-item">
              <span class="feature-icon-wrap"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
              <span>Role-based access control</span>
            </div>
          </div>
        </div>
      </div>
      <div class="form-panel">
        <div class="form-content">
          <h2 class="form-title">Create Account</h2>
          <p class="form-subtitle">Get started with your investment journey</p>
          <form @submit.prevent="handleRegister" class="auth-form">
            <div class="form-group">
              <label for="name">Full Name</label>
              <div class="input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input id="name" type="text" v-model="name" placeholder="John Doe" required />
              </div>
            </div>
            <div class="form-group">
              <label for="email">Email Address</label>
              <div class="input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <input id="email" type="email" v-model="email" placeholder="you@example.com" required />
              </div>
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input id="password" type="password" v-model="password" placeholder="Minimum 8 characters" required />
              </div>
            </div>
            <div class="form-group">
              <label for="role">Register As</label>
              <div class="input-wrapper select-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                <select id="role" v-model="roleId" required>
                  <option value="" disabled>Select your role</option>
                  <option value="1">Investor</option>
                  <option value="3">Auditor</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn-primary" :disabled="isLoading">
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
    setTimeout(() => { router.push('/login') }, 2000)
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
.auth-layout { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); padding: var(--space-md); animation: fadeIn 0.5s ease; }
.auth-container { display: grid; grid-template-columns: 1fr 1fr; max-width: 940px; width: 100%; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-xl); animation: slideUp 0.6s ease; }
.brand-panel { background: linear-gradient(160deg, #6366f1 0%, #4f46e5 40%, #4338ca 100%); color: white; padding: 3rem 2.5rem; display: flex; align-items: center; position: relative; overflow: hidden; }
.brand-panel::before { content: ''; position: absolute; top: -50%; right: -50%; width: 100%; height: 100%; background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%); pointer-events: none; }
.brand-content { width: 100%; position: relative; z-index: 1; }
.brand-icon { width: 64px; height: 64px; background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.1); }
.brand-title { font-size: 1.8rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
.brand-subtitle { font-size: 0.9rem; color: rgba(255,255,255,0.7); margin-bottom: 2rem; line-height: 1.5; }
.brand-features { display: flex; flex-direction: column; gap: 0.85rem; }
.feature-item { display: flex; align-items: center; gap: 0.7rem; font-size: 0.88rem; color: rgba(255,255,255,0.85); }
.feature-icon-wrap { width: 32px; height: 32px; background: rgba(255,255,255,0.12); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: rgba(255,255,255,0.9); }
.form-panel { background: var(--color-surface); padding: 2.5rem; display: flex; align-items: center; }
.form-content { width: 100%; }
.form-title { font-size: 1.6rem; font-weight: 800; color: var(--color-text); margin-bottom: 0.3rem; letter-spacing: -0.02em; }
.form-subtitle { font-size: 0.9rem; color: var(--color-text-secondary); margin-bottom: 1.5rem; }
.auth-form { display: flex; flex-direction: column; gap: 1rem; }
.form-group label { display: block; font-size: 0.82rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.04em; }
.input-wrapper { display: flex; align-items: center; border: 1.5px solid var(--color-border); border-radius: var(--radius-md); padding: 0 0.85rem; transition: border-color 200ms ease, box-shadow 200ms ease; background: var(--color-surface); }
.input-wrapper:focus-within { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08); }
.input-wrapper svg { flex-shrink: 0; color: var(--color-text-muted); }
.input-wrapper input, .input-wrapper select { flex: 1; border: none; outline: none; padding: 0.65rem 0.6rem; font-size: 0.93rem; background: transparent; color: var(--color-text); font-family: inherit; }
.input-wrapper input::placeholder { color: var(--color-text-muted); }
.select-wrapper select { cursor: pointer; appearance: none; }
.btn-primary { width: 100%; padding: 0.78rem; background: var(--color-primary); color: white; border: none; border-radius: var(--radius-md); font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: background 200ms ease, transform 150ms ease; margin-top: 0.3rem; }
.btn-primary:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
.btn-primary:active { transform: translateY(0); }
.btn-primary:disabled { background: var(--color-text-muted); cursor: not-allowed; transform: none; }
.error-msg { color: var(--color-error); font-weight: 600; font-size: 0.88rem; margin-top: 1rem; text-align: center; background: var(--color-error-bg); padding: 0.5rem 1rem; border-radius: var(--radius-sm); }
.success-msg { color: var(--color-success); font-weight: 600; font-size: 0.88rem; margin-top: 1rem; text-align: center; background: var(--color-success-bg); padding: 0.5rem 1rem; border-radius: var(--radius-sm); }
.form-footer { margin-top: 1.5rem; text-align: center; font-size: 0.9rem; color: var(--color-text-secondary); }
.link { color: var(--color-primary); font-weight: 700; text-decoration: none; transition: color 200ms ease; }
.link:hover { color: var(--color-primary-hover); }
@media (max-width: 700px) {
  .auth-container { grid-template-columns: 1fr; }
  .brand-panel { padding: 2rem 1.5rem; }
  .brand-features { display: none; }
  .form-panel { padding: 2rem 1.5rem; }
}
</style>
