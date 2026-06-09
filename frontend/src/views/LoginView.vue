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

      <!-- Right Panel - Login Form -->
      <div class="form-panel">
        <div class="form-content">
          <h2 class="form-title">Welcome back</h2>
          <p class="form-subtitle">Sign in to your account to continue</p>

          <form @submit.prevent="handleLogin" class="auth-form">
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
                  placeholder="••••••••"
                  required
                />
              </div>
            </div>

            <button type="submit" class="btn-login" :disabled="isLoading">
              {{ isLoading ? 'Signing in...' : 'Sign In' }}
            </button>

            <div class="forgot-link-row">
              <router-link to="/forgot-password" class="forgot-link">Forgot password?</router-link>
            </div>
          </form>

          <!-- Email Verification Notice -->
          <div v-if="needsVerification" class="verify-banner">
            <p>⚠️ Your email is not verified. Please check your inbox.</p>
            <button class="btn-resend" @click="handleResendVerification" :disabled="isResending">
              {{ isResending ? 'Sending...' : 'Resend Verification Email' }}
            </button>
            <p v-if="resendMsg" class="resend-msg">{{ resendMsg }}</p>
          </div>

          <div class="divider">
            <span>or</span>
          </div>

          <button class="btn-metamask" @click="handleConnectWallet" :disabled="isConnecting">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.22-8.56"/><path d="M21 3v6h-6"/></svg>
            {{ isConnecting ? 'Connecting...' : walletAddress ? '🦊 ' + walletAddress.slice(0, 6) + '...' + walletAddress.slice(-4) : '🦊 Connect MetaMask' }}
          </button>
          <p v-if="walletAddress" class="wallet-connected">✅ Wallet connected</p>

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
    const response = await api.post('/auth/resend-verification', {
      email: email.value,
    })
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
  padding: 3rem 2.5rem;
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
  margin-bottom: 2rem;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
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

.input-wrapper input {
  flex: 1;
  border: none;
  outline: none;
  padding: 0.7rem 0.6rem;
  font-size: 0.95rem;
  background: transparent;
  color: #1a202c;
}

.input-wrapper input::placeholder {
  color: #cbd5e0;
}

.btn-login {
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
  margin-top: 0.5rem;
}

.btn-login:hover { background: #333; }
.btn-login:disabled { background: #a0aec0; cursor: not-allowed; }

.forgot-link-row {
  text-align: right;
  margin-top: 0.3rem;
}

.forgot-link {
  font-size: 0.82rem;
  color: #718096;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.forgot-link:hover { color: #1a1a1a; }

.verify-banner {
  background: #fffbeb;
  border: 1px solid #fbbf24;
  border-radius: 10px;
  padding: 1rem;
  margin-top: 1rem;
  text-align: center;
}

.verify-banner p {
  font-size: 0.85rem;
  color: #92400e;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.btn-resend {
  padding: 0.45rem 1rem;
  background: #f59e0b;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-resend:hover { background: #d97706; }
.btn-resend:disabled { background: #a0aec0; cursor: not-allowed; }

.resend-msg {
  font-size: 0.8rem;
  color: #38a169;
  margin-top: 0.4rem;
  font-weight: 600;
}

.divider {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 1.2rem 0;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e2e8f0;
}

.divider span {
  font-size: 0.82rem;
  color: #a0aec0;
  font-weight: 600;
  text-transform: uppercase;
}

.btn-metamask {
  width: 100%;
  padding: 0.75rem;
  background: #f6851b;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-metamask:hover { background: #e2761b; transform: translateY(-1px); }
.btn-metamask:disabled { background: #a0aec0; cursor: not-allowed; transform: none; }

.wallet-connected {
  text-align: center;
  font-size: 0.85rem;
  font-weight: 600;
  color: #38a169;
  margin-top: 0.5rem;
}

.error-msg {
  color: #e53e3e;
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