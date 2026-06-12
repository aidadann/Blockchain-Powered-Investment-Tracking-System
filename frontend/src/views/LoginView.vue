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
          <h2 class="form-title">Welcome back</h2>
          <p class="form-subtitle">Sign in to your account to continue</p>
          <form @submit.prevent="handleLogin" class="auth-form">
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
                <input id="password" type="password" v-model="password" placeholder="Enter your password" required />
              </div>
            </div>
            <button type="submit" class="btn-primary" :disabled="isLoading">
              {{ isLoading ? 'Signing in...' : 'Sign In' }}
            </button>
            <div class="forgot-link-row">
              <router-link to="/forgot-password" class="forgot-link">Forgot password?</router-link>
            </div>
          </form>
          <div v-if="needsVerification" class="verify-banner">
            <p>Your email is not verified. Please check your inbox.</p>
            <button class="btn-resend" @click="handleResendVerification" :disabled="isResending">
              {{ isResending ? 'Sending...' : 'Resend Verification Email' }}
            </button>
            <p v-if="resendMsg" class="resend-msg">{{ resendMsg }}</p>
          </div>
          <div class="divider"><span>or</span></div>
          <button class="btn-metamask" @click="handleConnectWallet" :disabled="isConnecting">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0H5m14 0l2 0M5 21l-2 0M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>
            {{ isConnecting ? 'Connecting...' : walletAddress ? walletAddress.slice(0, 6) + '...' + walletAddress.slice(-4) : 'Connect MetaMask' }}
          </button>
          <p v-if="walletAddress" class="wallet-connected">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            Wallet connected
          </p>
          <p v-if="error" class="error-msg">{{ error }}</p>
          <div class="form-footer">
            <p>Don't have an account? <router-link to="/register" class="link">Sign up</router-link></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { connectWallet } from '../services/blockchain'
import api from '../api'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const isLoading = ref(false)
const isConnecting = ref(false)
const walletAddress = ref('')
const needsVerification = ref(false)
const isResending = ref(false)
const resendMsg = ref('')

async function handleLogin() {
  error.value = ''
  needsVerification.value = false
  resendMsg.value = ''
  isLoading.value = true
  try {
    await auth.login(email.value, password.value)
    const role = auth.userRole
    if (role) {
      await router.push('/' + role.toLowerCase())
    } else {
      error.value = 'User role not found'
    }
  } catch (err: any) {
    const msg = err?.response?.data?.message || 'Login failed. Please check your credentials.'
    const emailVerified = err?.response?.data?.email_verified
    if (emailVerified === false) {
      needsVerification.value = true
    }
    error.value = msg
  } finally {
    isLoading.value = false
  }
}

async function handleConnectWallet() {
  isConnecting.value = true
  error.value = ''
  try {
    walletAddress.value = await connectWallet()
  } catch (err: any) {
    error.value = err.message || 'Failed to connect MetaMask'
  } finally {
    isConnecting.value = false
  }
}

async function handleResendVerification() {
  isResending.value = true
  resendMsg.value = ''
  try {
    const response = await api.post('/auth/resend-verification', { email: email.value })
    resendMsg.value = response.data.message
  } catch (err: any) {
    resendMsg.value = err?.response?.data?.message || 'Failed to resend.'
  } finally {
    isResending.value = false
  }
}
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
  padding: var(--space-md);
  animation: fadeIn 0.5s ease;
}
.auth-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  max-width: 940px;
  width: 100%;
  border-radius: var(--radius-xl);
  overflow: hidden;
  box-shadow: var(--shadow-xl);
  animation: slideUp 0.6s ease;
}
.brand-panel {
  background: linear-gradient(160deg, #6366f1 0%, #4f46e5 40%, #4338ca 100%);
  color: white;
  padding: 3rem 2.5rem;
  display: flex;
  align-items: center;
  position: relative;
  overflow: hidden;
}
.brand-panel::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
  pointer-events: none;
}
.brand-content { width: 100%; position: relative; z-index: 1; }
.brand-icon {
  width: 64px; height: 64px;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(8px);
  border-radius: var(--radius-lg);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1.5rem;
  border: 1px solid rgba(255,255,255,0.1);
}
.brand-title { font-size: 1.8rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
.brand-subtitle { font-size: 0.9rem; color: rgba(255,255,255,0.7); margin-bottom: 2rem; line-height: 1.5; }
.brand-features { display: flex; flex-direction: column; gap: 0.85rem; }
.feature-item { display: flex; align-items: center; gap: 0.7rem; font-size: 0.88rem; color: rgba(255,255,255,0.85); }
.feature-icon-wrap {
  width: 32px; height: 32px;
  background: rgba(255,255,255,0.12);
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: rgba(255,255,255,0.9);
}
.form-panel { background: var(--color-surface); padding: 3rem 2.5rem; display: flex; align-items: center; }
.form-content { width: 100%; }
.form-title { font-size: 1.6rem; font-weight: 800; color: var(--color-text); margin-bottom: 0.3rem; letter-spacing: -0.02em; }
.form-subtitle { font-size: 0.9rem; color: var(--color-text-secondary); margin-bottom: 2rem; }
.auth-form { display: flex; flex-direction: column; gap: 1.1rem; }
.form-group label { display: block; font-size: 0.82rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.04em; }
.input-wrapper { display: flex; align-items: center; border: 1.5px solid var(--color-border); border-radius: var(--radius-md); padding: 0 0.85rem; transition: border-color 200ms ease, box-shadow 200ms ease; background: var(--color-surface); }
.input-wrapper:focus-within { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08); }
.input-wrapper svg { flex-shrink: 0; color: var(--color-text-muted); }
.input-wrapper input { flex: 1; border: none; outline: none; padding: 0.72rem 0.6rem; font-size: 0.93rem; background: transparent; color: var(--color-text); }
.input-wrapper input::placeholder { color: var(--color-text-muted); }
.btn-primary { width: 100%; padding: 0.78rem; background: var(--color-primary); color: white; border: none; border-radius: var(--radius-md); font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: background 200ms ease, transform 150ms ease; margin-top: 0.5rem; }
.btn-primary:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
.btn-primary:active { transform: translateY(0); }
.btn-primary:disabled { background: var(--color-text-muted); cursor: not-allowed; transform: none; }
.forgot-link-row { text-align: right; margin-top: 0.2rem; }
.forgot-link { font-size: 0.82rem; color: var(--color-text-secondary); text-decoration: none; font-weight: 600; transition: color 200ms ease; }
.forgot-link:hover { color: var(--color-primary); }
.verify-banner { background: var(--color-warning-bg); border: 1px solid var(--color-warning); border-radius: var(--radius-md); padding: 1rem; margin-top: 1rem; text-align: center; }
.verify-banner p { font-size: 0.85rem; color: var(--color-warning-text); font-weight: 600; margin-bottom: 0.5rem; }
.btn-resend { padding: 0.45rem 1rem; background: var(--color-warning); color: white; border: none; border-radius: var(--radius-sm); font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: background 200ms ease; }
.btn-resend:hover { background: #d97706; }
.btn-resend:disabled { background: var(--color-text-muted); cursor: not-allowed; }
.resend-msg { font-size: 0.8rem; color: var(--color-success); margin-top: 0.25rem; font-weight: 600; }
.divider { display: flex; align-items: center; gap: 1rem; margin: 1.2rem 0; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--color-border); }
.divider span { font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
.btn-metamask { width: 100%; padding: 0.75rem; background: var(--color-metamask); color: white; border: none; border-radius: var(--radius-md); font-size: 0.93rem; font-weight: 700; cursor: pointer; transition: background 200ms ease, transform 150ms ease; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
.btn-metamask:hover { background: #c96516; transform: translateY(-1px); }
.btn-metamask:active { transform: translateY(0); }
.btn-metamask:disabled { background: var(--color-text-muted); cursor: not-allowed; transform: none; }
.wallet-connected { display: flex; align-items: center; justify-content: center; gap: 0.35rem; font-size: 0.85rem; font-weight: 600; color: var(--color-success); margin-top: 0.5rem; }
.error-msg { color: var(--color-error); font-weight: 600; font-size: 0.88rem; margin-top: 1rem; text-align: center; background: var(--color-error-bg); padding: 0.5rem 1rem; border-radius: var(--radius-sm); }
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