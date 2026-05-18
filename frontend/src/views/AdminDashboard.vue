<template>
  <div class="dashboard-layout">
    <!-- Top Navbar -->
    <nav class="top-nav">
      <button class="nav-btn">
        <span class="hamburger">☰</span>
      </button>
      <div class="nav-right">
        <router-link to="/explorer" class="explorer-link">⛓️ Explorer</router-link>
        <button class="nav-btn profile-btn" @click="showProfileMenu = !showProfileMenu">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </button>
        <div v-if="showProfileMenu" class="profile-dropdown">
          <p class="profile-name">{{ auth.user?.name }}</p>
          <p class="profile-role">Admin</p>
          <hr />
          <button @click="handleLogout" class="dropdown-logout">Logout</button>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <h1 class="welcome-heading">Welcome back, Admin</h1>

      <div class="top-row">
        <!-- Approval Statistics (Donut Chart) -->
        <div class="card stats-card">
          <h3 class="card-title">Approval Statistics</h3>
          <div class="donut-container">
            <svg viewBox="0 0 180 180" class="donut-svg">
              <!-- Background circle -->
              <circle cx="90" cy="90" r="70" fill="none" stroke="#edf2f7" stroke-width="22" />
              <!-- Approved arc -->
              <circle cx="90" cy="90" r="70" fill="none" stroke="#38a169" stroke-width="22"
                :stroke-dasharray="approvedArc + ' ' + (440 - approvedArc)"
                stroke-dashoffset="110"
                stroke-linecap="round"
              />
              <!-- Rejected arc -->
              <circle cx="90" cy="90" r="70" fill="none" stroke="#e53e3e" stroke-width="22"
                :stroke-dasharray="rejectedArc + ' ' + (440 - rejectedArc)"
                :stroke-dashoffset="110 - approvedArc"
                stroke-linecap="round"
              />
              <!-- Pending arc -->
              <circle cx="90" cy="90" r="70" fill="none" stroke="#ecc94b" stroke-width="22"
                :stroke-dasharray="pendingArc + ' ' + (440 - pendingArc)"
                :stroke-dashoffset="110 - approvedArc - rejectedArc"
                stroke-linecap="round"
              />
              <!-- Center text -->
              <text x="90" y="82" text-anchor="middle" font-size="32" font-weight="800" fill="#1a202c">{{ totalCount }}</text>
              <text x="90" y="102" text-anchor="middle" font-size="12" fill="#38a169" font-weight="600">Approval</text>
            </svg>
          </div>
          <div class="donut-legend">
            <span class="legend-item"><span class="legend-dot dot-approved"></span> Approved</span>
            <span class="legend-item"><span class="legend-dot dot-rejected"></span> Rejected</span>
            <span class="legend-item"><span class="legend-dot dot-pending"></span> Pending</span>
          </div>
        </div>

        <!-- Right Column -->
        <div class="right-column">
          <!-- Pending Approvals -->
          <div class="card pending-card">
            <h3 class="card-title">Pending Approval</h3>
            <div class="pending-list">
              <div v-for="inv in pendingInvestments" :key="inv.id" class="pending-item">
                <div class="pending-avatar">{{ getInitial(inv) }}</div>
                <div class="pending-info">
                  <span class="pending-name">Investor #{{ inv.user_id }}</span>
                  <span class="pending-asset">{{ inv.asset_name }}</span>
                </div>
                <button class="btn-approve" @click="handleApprove(inv.id)" :disabled="investmentStore.isLoading">
                  Approve
                </button>
              </div>
              <p v-if="pendingInvestments.length === 0" class="empty-list">No pending approvals.</p>
            </div>
          </div>

          <!-- Manage Users (All investments as user list) -->
          <div class="card users-card">
            <h3 class="card-title">Manage Users</h3>
            <div class="user-list">
              <div v-for="inv in investmentStore.investments" :key="inv.id" class="user-item">
                <div class="user-avatar">{{ getInitial(inv) }}</div>
                <div class="user-name">Investor #{{ inv.user_id }} — {{ inv.asset_name }}</div>
                <span :class="'mini-badge mini-' + inv.status">{{ inv.status }}</span>
              </div>
              <p v-if="investmentStore.investments.length === 0" class="empty-list">No users found.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="card notifications-card">
        <div class="notif-header">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4a5568" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useInvestmentStore } from '../stores/investment'

const router = useRouter()
const auth = useAuthStore()
const investmentStore = useInvestmentStore()

const showProfileMenu = ref(false)
const successMsg = ref('')

onMounted(() => {
  investmentStore.fetchInvestments()
})

const totalCount = computed(() => investmentStore.investments.length)

const approvedCount = computed(() => investmentStore.investments.filter((i: any) => i.status === 'approved').length)
const rejectedCount = computed(() => investmentStore.investments.filter((i: any) => i.status === 'rejected').length)
const pendingCountVal = computed(() => investmentStore.investments.filter((i: any) => i.status === 'pending').length)

const circumference = 440

const approvedArc = computed(() => {
  if (totalCount.value === 0) return 0
  return (approvedCount.value / totalCount.value) * circumference
})

const rejectedArc = computed(() => {
  if (totalCount.value === 0) return 0
  return (rejectedCount.value / totalCount.value) * circumference
})

const pendingArc = computed(() => {
  if (totalCount.value === 0) return 0
  return (pendingCountVal.value / totalCount.value) * circumference
})

const pendingInvestments = computed(() => {
  return investmentStore.investments.filter((i: any) => i.status === 'pending')
})

function getInitial(inv: any) {
  return inv.asset_name?.charAt(0)?.toUpperCase() || '?'
}

async function handleApprove(id: number) {
  successMsg.value = ''
  const ok = await investmentStore.approveInvestment(id)
  if (ok) {
    successMsg.value = `Investment #${id} approved successfully!`
    setTimeout(() => { successMsg.value = '' }, 3000)
  }
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.dashboard-layout {
  min-height: 100vh;
  background: #f5f5f5;
}

/* ===== Top Navbar ===== */
.top-nav {
  background: #1a1a1a;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6rem 1.5rem;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: white;
  font-size: 1.4rem;
}

.nav-right { position: relative; }
.profile-btn { display: flex; align-items: center; }

.profile-dropdown {
  position: absolute;
  right: 0;
  top: 45px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  padding: 1rem;
  min-width: 180px;
  z-index: 200;
}

.profile-name { font-weight: 700; color: #1a202c; margin-bottom: 0.2rem; }
.profile-role { font-size: 0.85rem; color: #718096; }
.profile-dropdown hr { border: none; border-top: 1px solid #e2e8f0; margin: 0.7rem 0; }

.dropdown-logout {
  width: 100%;
  background: #e53e3e;
  color: white;
  border: none;
  padding: 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}
.dropdown-logout:hover { background: #c53030; }

/* ===== Main ===== */
.main-content {
  padding: 2rem 2.5rem;
  max-width: 1100px;
  margin: 0 auto;
}

.welcome-heading {
  font-size: 2rem;
  font-weight: 800;
  color: #1a202c;
  margin-bottom: 1.5rem;
}

/* ===== Top Row ===== */
.top-row {
  display: grid;
  grid-template-columns: 1fr 1.6fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 1.2rem;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  border: 1px solid #edf2f7;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 0.8rem;
}

/* ===== Donut Chart ===== */
.donut-container {
  display: flex;
  justify-content: center;
  padding: 0.5rem 0;
}

.donut-svg {
  width: 180px;
  height: 180px;
}

.donut-legend {
  display: flex;
  justify-content: center;
  gap: 1.2rem;
  margin-top: 0.8rem;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.82rem;
  color: #4a5568;
  font-weight: 600;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 3px;
}

.dot-approved { background: #38a169; }
.dot-rejected { background: #e53e3e; }
.dot-pending { background: #ecc94b; }

/* ===== Right Column ===== */
.right-column {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Pending Approvals */
.pending-list {
  max-height: 200px;
  overflow-y: auto;
}

.pending-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.6rem 0;
  border-bottom: 1px solid #f7fafc;
}

.pending-avatar, .user-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #4a5568;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.pending-info {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.pending-name { font-weight: 600; color: #1a202c; font-size: 0.9rem; }
.pending-asset { font-size: 0.8rem; color: #4299e1; }

.btn-approve {
  padding: 0.35rem 0.8rem;
  background: #38a169;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.8rem;
  transition: background 0.2s;
}
.btn-approve:hover { background: #2f855a; }
.btn-approve:disabled { background: #a0aec0; cursor: not-allowed; }

/* Users List */
.user-list {
  max-height: 280px;
  overflow-y: auto;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.6rem 0;
  border-bottom: 1px solid #f7fafc;
}

.user-name {
  flex: 1;
  font-size: 0.9rem;
  color: #4a5568;
  font-weight: 500;
}

.mini-badge {
  font-size: 0.7rem;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  font-weight: 700;
  text-transform: uppercase;
}

.mini-pending { background: #fefcbf; color: #b7791f; }
.mini-approved { background: #c6f6d5; color: #276749; }
.mini-rejected { background: #fed7d7; color: #c53030; }

.empty-list {
  color: #a0aec0;
  font-style: italic;
  font-size: 0.85rem;
  padding: 0.5rem 0;
}

/* ===== Notifications ===== */
.notifications-card {
  max-width: 320px;
}

.notif-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.3rem;
}

.notif-count {
  font-size: 1.2rem;
  font-weight: 700;
  color: #4299e1;
}

.notif-title {
  font-size: 0.85rem;
  color: #a0aec0;
  margin-bottom: 0.7rem;
}

.notif-list {
  list-style: disc;
  padding-left: 1.2rem;
  font-size: 0.82rem;
  color: #4a5568;
  line-height: 1.6;
}

.global-success {
  color: #38a169;
  font-weight: 700;
  margin-top: 1rem;
  font-size: 0.95rem;
}

/* ===== Responsive ===== */
@media (max-width: 800px) {
  .top-row { grid-template-columns: 1fr; }
  .main-content { padding: 1.5rem 1rem; }
  .welcome-heading { font-size: 1.5rem; }
}
</style>
