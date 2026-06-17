<template>
  <div class="dashboard-layout">
    <nav class="top-nav">
      <div class="nav-left">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        <span class="nav-brand">Investment Tracker</span>
      </div>
      <div class="nav-right">
        <div v-if="walletAddress" class="wallet-badge" :title="walletAddress">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0H5m14 0l2 0M5 21l-2 0M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>
          {{ walletAddress.slice(0, 6) }}...{{ walletAddress.slice(-4) }}
        </div>
        <button v-else class="btn-connect-wallet" @click="handleConnectWallet">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0H5m14 0l2 0M5 21l-2 0M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>
          Connect Wallet
        </button>
        <router-link to="/explorer" class="explorer-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
          Explorer
        </router-link>
        <button class="nav-btn profile-btn" @click="showProfileMenu = !showProfileMenu">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </button>
        <div v-if="showProfileMenu" class="profile-dropdown">
          <p class="profile-name">{{ auth.user?.name }}</p>
          <p class="profile-role">Admin</p>
          <hr />
          <button @click="handleLogout" class="dropdown-logout">Logout</button>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <h1 class="welcome-heading">Welcome back, Admin</h1>

      <div class="top-row">
        <div class="card stats-card">
          <h3 class="card-title">Approval Statistics</h3>
          <div class="donut-container">
            <svg viewBox="0 0 180 180" class="donut-svg">
              <circle cx="90" cy="90" r="70" fill="none" stroke="var(--color-border-light)" stroke-width="22" />
              <circle cx="90" cy="90" r="70" fill="none" stroke="var(--color-success)" stroke-width="22"
                :stroke-dasharray="approvedArc + ' ' + (440 - approvedArc)"
                stroke-dashoffset="110" stroke-linecap="round" />
              <circle cx="90" cy="90" r="70" fill="none" stroke="var(--color-error)" stroke-width="22"
                :stroke-dasharray="rejectedArc + ' ' + (440 - rejectedArc)"
                :stroke-dashoffset="110 - approvedArc" stroke-linecap="round" />
              <circle cx="90" cy="90" r="70" fill="none" stroke="var(--color-warning)" stroke-width="22"
                :stroke-dasharray="pendingArc + ' ' + (440 - pendingArc)"
                :stroke-dashoffset="110 - approvedArc - rejectedArc" stroke-linecap="round" />
              <text x="90" y="82" text-anchor="middle" font-size="32" font-weight="800" fill="var(--color-text)">{{ totalCount }}</text>
              <text x="90" y="102" text-anchor="middle" font-size="12" fill="var(--color-success)" font-weight="600">Approval</text>
            </svg>
          </div>
          <div class="donut-legend">
            <span class="legend-item"><span class="legend-dot dot-approved"></span> Approved</span>
            <span class="legend-item"><span class="legend-dot dot-rejected"></span> Rejected</span>
            <span class="legend-item"><span class="legend-dot dot-pending"></span> Pending</span>
          </div>
        </div>

        <div class="right-column">
          <div class="card pending-card">
            <h3 class="card-title">Pending Approval</h3>
            <div v-if="!walletAddress" class="wallet-required">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              <span>Connect your wallet to approve or reject investments</span>
            </div>
            <div class="pending-list">
              <div v-for="inv in pendingInvestments" :key="inv.id" class="pending-item">
                <div class="pending-avatar">{{ getInitial(inv) }}</div>
                <div class="pending-info">
                  <span class="pending-name">Investor #{{ inv.user_id }}</span>
                  <span class="pending-asset">{{ inv.asset_name }}</span>
                </div>
                <div class="action-buttons">
                  <button class="btn-approve" @click="handleApprove(inv.id)" :disabled="investmentStore.isLoading || !walletAddress">Approve</button>
                  <button class="btn-reject" @click="handleReject(inv.id)" :disabled="investmentStore.isLoading || !walletAddress">Reject</button>
                </div>
              </div>
              <p v-if="pendingInvestments.length === 0" class="empty-list">No pending approvals.</p>
            </div>
          </div>

          <div class="card users-card">
            <h3 class="card-title">Manage Users</h3>
            <div class="user-list">
              <div v-for="inv in investmentStore.investments" :key="inv.id" class="user-item">
                <div class="user-avatar">{{ getInitial(inv) }}</div>
                <div class="user-name">Investor #{{ inv.user_id }} -- {{ inv.asset_name }}</div>
                <span :class="'mini-badge mini-' + inv.status">{{ inv.status }}</span>
              </div>
              <p v-if="investmentStore.investments.length === 0" class="empty-list">No users found.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="card notifications-card">
        <div class="notif-header">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-text-secondary)" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="notif-count">{{ pendingInvestments.length }}</span>
        </div>
        <p class="notif-title">Notifications</p>
        <ul class="notif-list">
          <li v-for="inv in pendingInvestments" :key="'notif-' + inv.id">
            Investment on {{ inv.asset_name }} is pending
          </li>
          <li v-if="pendingInvestments.length === 0">No new notifications.</li>
        </ul>
      </div>

      <p v-if="successMsg" class="global-success">{{ successMsg }}</p>
    </main>

    <!-- Help/Guide Button -->
    <button class="btn-guide-floating" @click="showGuide = true">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      Guide
    </button>

    <!-- Guide Modal -->
    <div v-if="showGuide" class="modal-overlay" @click.self="showGuide = false">
      <div class="modal-content guide-modal">
        <h2>MetaMask Setup Guide</h2>
        <ol class="guide-steps">
          <li>Install the <a href="https://metamask.io/" target="_blank">MetaMask Browser Extension</a>.</li>
          <li>Follow the setup instructions to create a new wallet (save your seed phrase).</li>
          <li>Once logged in, click the <strong>Network</strong> after opening the menu at the top right of MetaMask.</li>
          <li>Toggle on <strong>"Show test networks"</strong>.</li>
          <li>Select <strong>Sepolia</strong> from the list of networks.</li>
        </ol>
        <div style="margin-top: 1rem;">
          <button class="btn-cancel" @click="showGuide = false" style="width: 100%">Close Guide</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useInvestmentStore } from '../stores/investment'
import { connectWallet, listenForApprovals } from '../services/blockchain'

const router = useRouter()
const auth = useAuthStore()
const investmentStore = useInvestmentStore()

const showProfileMenu = ref(false)
const showGuide = ref(false)
const successMsg = ref('')
const walletAddress = ref('')
let cleanupListener: (() => void) | null = null

onMounted(() => {
  investmentStore.fetchInvestments()
  cleanupListener = listenForApprovals((investmentId: number) => {
    console.log(`Investment #${investmentId} approved on-chain. Refreshing...`)
    investmentStore.fetchInvestments()
  })
})

onUnmounted(() => {
  if (cleanupListener) cleanupListener()
})

const totalCount = computed(() => investmentStore.investments.length)
const approvedCount = computed(() => investmentStore.investments.filter((i: any) => i.status === 'approved').length)
const rejectedCount = computed(() => investmentStore.investments.filter((i: any) => i.status === 'rejected').length)
const pendingCountVal = computed(() => investmentStore.investments.filter((i: any) => i.status === 'pending').length)
const circumference = 440
const approvedArc = computed(() => { if (totalCount.value === 0) return 0; return (approvedCount.value / totalCount.value) * circumference })
const rejectedArc = computed(() => { if (totalCount.value === 0) return 0; return (rejectedCount.value / totalCount.value) * circumference })
const pendingArc = computed(() => { if (totalCount.value === 0) return 0; return (pendingCountVal.value / totalCount.value) * circumference })
const pendingInvestments = computed(() => investmentStore.investments.filter((i: any) => i.status === 'pending'))

function getInitial(inv: any) { return inv.asset_name?.charAt(0)?.toUpperCase() || '?' }

async function handleApprove(id: number) {
  successMsg.value = ''
  const ok = await investmentStore.approveInvestment(id)
  if (ok) { successMsg.value = `Investment #${id} approved successfully!`; setTimeout(() => { successMsg.value = '' }, 3000) }
}
async function handleReject(id: number) {
  successMsg.value = ''
  const ok = await investmentStore.rejectInvestment(id)
  if (ok) { successMsg.value = `Investment #${id} rejected successfully!`; setTimeout(() => { successMsg.value = '' }, 3000) }
}
async function handleLogout() { await auth.logout(); router.push('/login') }
async function handleConnectWallet() {
  try { walletAddress.value = await connectWallet() } catch (err: any) { console.warn('MetaMask connection failed:', err.message) }
}
</script>

<style scoped>
.dashboard-layout { min-height: 100vh; background: var(--color-bg); }

.top-nav { background: var(--color-dark); display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 1.5rem; position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.nav-left { display: flex; align-items: center; gap: 0.75rem; }
.nav-brand { color: white; font-weight: 700; font-size: 1rem; letter-spacing: -0.01em; }
.nav-btn { background: none; border: none; cursor: pointer; color: white; display: flex; align-items: center; padding: 0.3rem; border-radius: var(--radius-sm); transition: background 200ms ease; }
.nav-btn:hover { background: rgba(255,255,255,0.08); }
.nav-right { position: relative; display: flex; align-items: center; gap: 0.7rem; }

.wallet-badge { background: rgba(226, 118, 27, 0.12); color: var(--color-metamask); padding: 0.3rem 0.7rem; border-radius: var(--radius-sm); font-size: 0.78rem; font-weight: 700; font-family: monospace; cursor: default; display: flex; align-items: center; gap: 0.35rem; }
.btn-connect-wallet { background: var(--color-metamask); color: white; border: none; padding: 0.35rem 0.8rem; border-radius: var(--radius-sm); font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: background 200ms ease; display: flex; align-items: center; gap: 0.35rem; }
.btn-connect-wallet:hover { background: #c96516; }

.explorer-link { color: var(--color-text-muted); font-size: 0.82rem; font-weight: 600; text-decoration: none; padding: 0.35rem 0.7rem; border: 1px solid var(--color-dark-muted); border-radius: var(--radius-sm); transition: color 200ms ease, border-color 200ms ease; display: flex; align-items: center; gap: 0.35rem; }
.explorer-link:hover { color: white; border-color: var(--color-text-muted); }

.profile-btn { display: flex; align-items: center; }
.profile-dropdown { position: absolute; right: 0; top: 45px; background: var(--color-surface); border-radius: var(--radius-md); box-shadow: var(--shadow-lg); padding: 1rem; min-width: 180px; z-index: 200; animation: slideDown 0.2s ease; }
.profile-name { font-weight: 700; color: var(--color-text); margin-bottom: 0.2rem; }
.profile-role { font-size: 0.85rem; color: var(--color-text-secondary); }
.profile-dropdown hr { border: none; border-top: 1px solid var(--color-border); margin: 0.7rem 0; }
.dropdown-logout { width: 100%; background: var(--color-error); color: white; border: none; padding: 0.5rem; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600; transition: background 200ms ease; }
.dropdown-logout:hover { background: #dc2626; }

.main-content { padding: 2rem 2.5rem; max-width: 1100px; margin: 0 auto; animation: slideUp 0.5s ease; }
.welcome-heading { font-size: 1.75rem; font-weight: 800; color: var(--color-text); margin-bottom: 1.5rem; letter-spacing: -0.02em; }

.top-row { display: grid; grid-template-columns: 1fr 1.6fr; gap: 1.25rem; margin-bottom: 1.25rem; }
.card { background: var(--color-surface); border-radius: var(--radius-lg); padding: 1.25rem; box-shadow: var(--shadow-md); border: 1px solid var(--color-border); transition: box-shadow 200ms ease, transform 200ms ease; }
.card:hover { box-shadow: var(--shadow-lg); }
.card-title { font-size: 0.9rem; font-weight: 700; color: var(--color-text); margin-bottom: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; }

.donut-container { display: flex; justify-content: center; padding: 0.5rem 0; }
.donut-svg { width: 180px; height: 180px; }
.donut-legend { display: flex; justify-content: center; gap: 1.2rem; margin-top: 0.8rem; flex-wrap: wrap; }
.legend-item { display: flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; color: var(--color-text-secondary); font-weight: 600; }
.legend-dot { width: 10px; height: 10px; border-radius: 3px; }
.dot-approved { background: var(--color-success); }
.dot-rejected { background: var(--color-error); }
.dot-pending { background: var(--color-warning); }

.right-column { display: flex; flex-direction: column; gap: 1rem; }
.pending-list { max-height: 200px; overflow-y: auto; }
.pending-item { display: flex; align-items: center; gap: 0.8rem; padding: 0.6rem 0; border-bottom: 1px solid var(--color-border-light); }
.pending-avatar, .user-avatar { width: 36px; height: 36px; border-radius: var(--radius-full); background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; }
.pending-info { display: flex; flex-direction: column; flex: 1; }
.pending-name { font-weight: 600; color: var(--color-text); font-size: 0.88rem; }
.pending-asset { font-size: 0.78rem; color: var(--color-info); }
.action-buttons { display: flex; gap: 0.4rem; }
.btn-approve { padding: 0.3rem 0.7rem; background: var(--color-success); color: white; border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600; font-size: 0.78rem; transition: background 200ms ease; }
.btn-approve:hover { background: #059669; }
.btn-approve:disabled { background: var(--color-text-muted); cursor: not-allowed; }
.btn-reject { padding: 0.3rem 0.7rem; background: var(--color-error); color: white; border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600; font-size: 0.78rem; transition: background 200ms ease; }
.btn-reject:hover { background: #dc2626; }
.btn-reject:disabled { background: var(--color-text-muted); cursor: not-allowed; }

.user-list { max-height: 280px; overflow-y: auto; }
.user-item { display: flex; align-items: center; gap: 0.8rem; padding: 0.55rem 0; border-bottom: 1px solid var(--color-border-light); }
.user-name { flex: 1; font-size: 0.88rem; color: var(--color-text-secondary); font-weight: 500; }
.mini-badge { font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: var(--radius-sm); font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
.mini-pending { background: var(--color-warning-bg); color: var(--color-warning-text); }
.mini-approved { background: var(--color-success-bg); color: var(--color-success-text); }
.mini-rejected { background: var(--color-error-bg); color: var(--color-error-text); }
.empty-list { color: var(--color-text-muted); font-style: italic; font-size: 0.85rem; padding: 0.5rem 0; }

.notifications-card { max-width: 340px; }
.notif-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem; }
.notif-count { font-size: 1.1rem; font-weight: 700; color: var(--color-primary); }
.notif-title { font-size: 0.82rem; color: var(--color-text-muted); margin-bottom: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; }
.notif-list { list-style: none; padding-left: 0; font-size: 0.82rem; color: var(--color-text-secondary); line-height: 1.7; }
.notif-list li { padding: 0.3rem 0; padding-left: 1rem; position: relative; }
.notif-list li::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 6px; height: 6px; border-radius: 50%; background: var(--color-primary); }
.global-success { color: var(--color-success); font-weight: 700; margin-top: 1rem; font-size: 0.93rem; background: var(--color-success-bg); padding: 0.5rem 1rem; border-radius: var(--radius-sm); }

.wallet-required { display: flex; align-items: center; gap: 0.5rem; background: var(--color-warning-bg); color: var(--color-warning-text); padding: 0.6rem 0.85rem; border-radius: var(--radius-sm); font-size: 0.82rem; font-weight: 600; margin-bottom: 0.75rem; border: 1px solid rgba(245, 158, 11, 0.2); }

/* ===== Guide Modal ===== */
.btn-guide-floating { position: fixed; bottom: 2rem; left: 2rem; background: var(--color-surface); border: 1px solid var(--color-border); padding: 0.8rem 1.2rem; border-radius: 30px; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--color-text); cursor: pointer; box-shadow: var(--shadow-md); z-index: 100; transition: all 200ms ease; }
.btn-guide-floating:hover { background: var(--color-primary); color: white; border-color: var(--color-primary); transform: translateY(-2px); box-shadow: var(--shadow-lg); }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 999; animation: fadeIn 0.2s ease; }
.modal-content { background: var(--color-surface); padding: 2rem; border-radius: var(--radius-lg); width: 100%; max-width: 400px; box-shadow: var(--shadow-xl); animation: slideUp 0.3s ease; }
.guide-modal { max-width: 500px; }
.guide-steps { margin: 1.5rem 0; padding-left: 1.5rem; text-align: left; line-height: 1.6; color: var(--color-text-secondary); }
.guide-steps li { margin-bottom: 0.5rem; }
.guide-steps a { color: var(--color-primary); text-decoration: none; }
.guide-steps a:hover { text-decoration: underline; }
.btn-cancel { padding: 0.6rem; background: #edf2f7; color: #4a5568; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.2s; }
.btn-cancel:hover { background: #e2e8f0; }

@media (max-width: 800px) {
  .top-row { grid-template-columns: 1fr; }
  .main-content { padding: 1.5rem 1rem; }
  .welcome-heading { font-size: 1.5rem; }
}
</style>
