<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="gas-modal">
      <!-- Header -->
      <div class="gas-modal-header">
        <div class="header-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
        </div>
        <div>
          <h2>Get Test ETH</h2>
          <p class="header-subtitle">Fund your wallet with Sepolia test ETH to pay for transaction gas fees</p>
        </div>
        <button class="close-btn" @click="$emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>

      <!-- Connected Wallet Info -->
      <div class="wallet-info">
        <span class="wallet-label">Connected Wallet:</span>
        <span class="wallet-addr" :title="walletAddress">
          {{ walletAddress.slice(0, 6) }}...{{ walletAddress.slice(-4) }}
        </span>
      </div>

      <!-- Two Option Cards -->
      <div class="options-grid">
        <!-- Auto-Fund Card -->
        <div class="option-card auto-fund-card" :class="{ disabled: cooldownActive || isLoading }">
          <div class="option-icon auto-icon">⚡</div>
          <h3>Auto-Fund</h3>
          <p class="option-desc">Receive <strong>0.05 ETH</strong> instantly from the system pool. No external site needed.</p>
          
          <div v-if="cooldownActive" class="cooldown-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <span>Available in {{ cooldownDisplay }}</span>
          </div>

          <button 
            class="btn-auto-fund" 
            @click="handleAutoFund" 
            :disabled="cooldownActive || isLoading"
          >
            <span v-if="isLoading" class="spinner"></span>
            <span v-else>⚡ Auto-Fund My Wallet</span>
          </button>
        </div>

        <!-- Manual Mine Card -->
        <div class="option-card manual-card">
          <div class="option-icon manual-icon">⛏️</div>
          <h3>Manual Mine</h3>
          <p class="option-desc">Open the Sepolia PoW faucet in a new tab. Mine directly using your browser.</p>
          
          <button class="btn-manual" @click="handleManualMine">
            ⛏️ Open Faucet Page
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          </button>
        </div>
      </div>

      <!-- Success State -->
      <div v-if="txResult" class="result-panel success-panel">
        <div class="result-icon">✅</div>
        <div class="result-content">
          <p class="result-title">Funding Successful!</p>
          <p class="result-detail">0.05 ETH has been sent to your wallet.</p>
          <a :href="txResult.etherscan_url" target="_blank" class="etherscan-link">
            View on Etherscan
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          </a>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="errorMsg" class="result-panel error-panel">
        <div class="result-icon">❌</div>
        <div class="result-content">
          <p class="result-title">Funding Failed</p>
          <p class="result-detail">{{ errorMsg }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '../api'

const props = defineProps<{
  walletAddress: string
}>()

defineEmits(['close'])

const isLoading = ref(false)
const cooldownActive = ref(false)
const cooldownEndsAt = ref<Date | null>(null)
const cooldownDisplay = ref('')
const txResult = ref<any>(null)
const errorMsg = ref('')

let cooldownTimer: ReturnType<typeof setInterval> | null = null

onMounted(async () => {
  await checkFaucetStatus()
})

async function checkFaucetStatus() {
  try {
    const response = await api.get('/faucet/status')
    cooldownActive.value = response.data.cooldown_active
    if (response.data.cooldown_ends_at) {
      cooldownEndsAt.value = new Date(response.data.cooldown_ends_at)
      startCooldownTimer()
    }
  } catch (err) {
    console.warn('Failed to check faucet status:', err)
  }
}

function startCooldownTimer() {
  updateCooldownDisplay()
  cooldownTimer = setInterval(() => {
    updateCooldownDisplay()
  }, 60000) // Update every minute
}

function updateCooldownDisplay() {
  if (!cooldownEndsAt.value) return
  
  const now = new Date()
  const diff = cooldownEndsAt.value.getTime() - now.getTime()
  
  if (diff <= 0) {
    cooldownActive.value = false
    cooldownDisplay.value = ''
    if (cooldownTimer) clearInterval(cooldownTimer)
    return
  }

  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  cooldownDisplay.value = `${hours}h ${minutes}m`
}

async function handleAutoFund() {
  if (isLoading.value || cooldownActive.value) return

  isLoading.value = true
  txResult.value = null
  errorMsg.value = ''

  try {
    const response = await api.post('/faucet/auto-fund')
    txResult.value = response.data
    
    // Start cooldown after success
    cooldownActive.value = true
    cooldownEndsAt.value = new Date(Date.now() + 24 * 60 * 60 * 1000)
    startCooldownTimer()
  } catch (err: any) {
    const data = err.response?.data
    if (data?.cooldown_active) {
      cooldownActive.value = true
      cooldownEndsAt.value = new Date(data.cooldown_ends_at)
      startCooldownTimer()
      errorMsg.value = data.message
    } else {
      errorMsg.value = data?.message || 'Failed to fund wallet. Please try manual mining.'
    }
  } finally {
    isLoading.value = false
  }
}

function handleManualMine() {
  const faucetUrl = `https://sepolia-faucet.pk910.de/#/mine/${props.walletAddress}`
  window.open(faucetUrl, '_blank')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 600;
  animation: fadeIn 0.2s ease;
}

.gas-modal {
  background: #ffffff;
  border-radius: 20px;
  padding: 0;
  width: 520px;
  max-width: 92vw;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.05);
  animation: scaleIn 0.25s ease;
  overflow: hidden;
}

/* Header */
.gas-modal-header {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
  padding: 1.5rem 1.5rem 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.header-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.gas-modal-header h2 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
  line-height: 1.3;
}

.header-subtitle {
  font-size: 0.82rem;
  color: #94a3b8;
  margin: 0.15rem 0 0;
  line-height: 1.4;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 4px;
  border-radius: 8px;
  margin-left: auto;
  transition: all 0.2s;
}
.close-btn:hover {
  background: #f1f5f9;
  color: #475569;
}

/* Wallet Info */
.wallet-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #f8fafc;
  border-bottom: 1px solid #f1f5f9;
}

.wallet-label {
  font-size: 0.78rem;
  color: #94a3b8;
  font-weight: 500;
}

.wallet-addr {
  font-family: 'SF Mono', 'Fira Code', monospace;
  font-size: 0.82rem;
  font-weight: 600;
  color: #e2761b;
  background: rgba(226, 118, 27, 0.08);
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
}

/* Options Grid */
.options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
}

.option-card {
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.25rem;
  text-align: center;
  transition: all 0.25s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.option-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.option-card.disabled {
  opacity: 0.6;
}

.option-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.option-card h3 {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.4rem;
}

.option-desc {
  font-size: 0.78rem;
  color: #64748b;
  line-height: 1.5;
  margin: 0 0 1rem;
  flex: 1;
}

/* Cooldown */
.cooldown-info {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  color: #f59e0b;
  font-weight: 600;
  margin-bottom: 0.6rem;
  background: #fef3c7;
  padding: 0.3rem 0.7rem;
  border-radius: 20px;
}

/* Buttons */
.btn-auto-fund {
  width: 100%;
  padding: 0.65rem 1rem;
  border: none;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  min-height: 38px;
}

.btn-auto-fund:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-auto-fund:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-manual {
  width: 100%;
  padding: 0.65rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
  color: #334155;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
}

.btn-manual:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

/* Spinner */
.spinner {
  width: 18px;
  height: 18px;
  border: 2.5px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* Result Panels */
.result-panel {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin: 0 1.5rem 1.25rem;
  padding: 1rem;
  border-radius: 12px;
  animation: slideUp 0.3s ease;
}

.success-panel {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
}

.error-panel {
  background: #fef2f2;
  border: 1px solid #fecaca;
}

.result-icon {
  font-size: 1.3rem;
  flex-shrink: 0;
}

.result-content {
  flex: 1;
}

.result-title {
  font-size: 0.88rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.2rem;
}

.result-detail {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0 0 0.4rem;
}

.etherscan-link {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: #667eea;
  text-decoration: none;
  transition: color 0.2s;
}
.etherscan-link:hover {
  color: #764ba2;
  text-decoration: underline;
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scaleIn {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(8px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 540px) {
  .options-grid {
    grid-template-columns: 1fr;
  }
  .gas-modal {
    width: 95vw;
  }
}
</style>
