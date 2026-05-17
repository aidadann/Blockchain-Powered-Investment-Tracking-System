<template>
  <div class="dashboard-layout">
    <!-- Top Navbar -->
    <nav class="top-nav">
      <button class="nav-btn" @click="sidebarOpen = !sidebarOpen">
        <span class="hamburger">☰</span>
      </button>
      <div class="nav-right">
        <button class="nav-btn profile-btn" @click="showProfileMenu = !showProfileMenu">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </button>
        <div v-if="showProfileMenu" class="profile-dropdown">
          <p class="profile-name">{{ auth.user?.name }}</p>
          <p class="profile-role">Investor</p>
          <hr />
          <button @click="handleLogout" class="dropdown-logout">Logout</button>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <h1 class="welcome-heading">Welcome back, {{ auth.user?.name || 'Investor' }}</h1>

      <!-- Summary Cards Row -->
      <div class="summary-row">
        <div class="summary-card">
          <div class="summary-icon icon-green">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div class="summary-info">
            <span class="summary-value">${{ totalInvestment }}</span>
            <span class="summary-label">Total Investment</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="summary-icon icon-blue">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.22-8.56"/><path d="M21 3v6h-6"/></svg>
          </div>
          <div class="summary-info">
            <span class="summary-value">${{ approvedTotal }}</span>
            <span class="summary-label">Total Balance</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="summary-icon icon-orange">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          </div>
          <div class="summary-info">
            <span class="summary-value">{{ pendingCount }}</span>
            <span class="summary-label">Pending Approval</span>
          </div>
        </div>

        <button class="add-investment-btn" @click="showForm = true">
          <span class="add-icon">＋</span> Add Investments
        </button>
      </div>

      <!-- Middle Row: Active Investments + Chart + Notifications -->
      <div class="middle-row">
        <!-- Active Investment List -->
        <div class="card active-investments">
          <h3 class="card-title">Active Investment</h3>
          <div class="investment-list-header">
            <span>Name</span>
            <span>Total</span>
          </div>
          <div class="investment-list">
            <div v-for="inv in approvedInvestments" :key="inv.id" class="investment-list-item">
              <span>{{ inv.asset_name }}</span>
              <span>${{ Number(inv.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
            </div>
            <div v-if="approvedInvestments.length === 0" class="empty-list">
              No active investments yet.
            </div>
          </div>
        </div>

        <!-- Investment Over Time (Placeholder Chart) -->
        <div class="card chart-card">
          <h3 class="card-title">Investment over time</h3>
          <div class="chart-placeholder">
            <svg viewBox="0 0 400 150" class="chart-svg">
              <defs>
                <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#38a169" stop-opacity="0.3"/>
                  <stop offset="100%" stop-color="#38a169" stop-opacity="0.02"/>
                </linearGradient>
              </defs>
              <path :d="chartAreaPath" fill="url(#chartGradient)" />
              <path :d="chartLinePath" fill="none" stroke="#38a169" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
              <circle v-for="(pt, i) in chartPoints" :key="i" :cx="pt.x" :cy="pt.y" r="4" fill="#38a169" stroke="white" stroke-width="2"/>
            </svg>
            <div class="chart-y-labels">
              <span v-for="label in chartYLabels" :key="label">${{ label }}</span>
            </div>
          </div>
        </div>

        <!-- Notifications -->
        <div class="card notifications-card">
          <div class="notif-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4a5568" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span class="notif-count">{{ recentNotifications.length }}</span>
          </div>
          <p class="notif-title">Notifications</p>
          <ul class="notif-list">
            <li v-for="(n, i) in recentNotifications" :key="i">{{ n }}</li>
            <li v-if="recentNotifications.length === 0">No new notifications.</li>
          </ul>
        </div>
      </div>

      <!-- Investment Table -->
      <div class="card table-card">
        <table>
          <thead>
            <tr>
              <th><input type="checkbox" disabled /></th>
              <th>NAME</th>
              <th>STATUS</th>
              <th>QUANTITY</th>
              <th>PRICE</th>
              <th>DATE</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inv in investmentStore.investments" :key="inv.id" :class="{ 'row-selected': inv.status === 'approved' }">
              <td><input type="checkbox" :checked="inv.status === 'approved'" disabled /></td>
              <td class="name-cell">{{ inv.asset_name.toUpperCase() }}</td>
              <td>
                <span :class="'badge badge-' + inv.status">{{ inv.status.toUpperCase() }}</span>
              </td>
              <td>{{ inv.status === 'approved' ? '1' : '-' }}</td>
              <td>{{ inv.status === 'approved' ? '$' + Number(inv.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) : '-' }}</td>
              <td>{{ formatDate(inv.created_at) }}</td>
            </tr>
            <tr v-if="investmentStore.investments.length === 0">
              <td colspan="6" class="empty-table">No investments yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <!-- Add Investment Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal">
        <h2>Add New Investment</h2>
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label>Asset Name</label>
            <input v-model="assetName" type="text" placeholder="e.g. Real Estate Property A" required />
          </div>
          <div class="form-group">
            <label>Amount (USD)</label>
            <input v-model.number="amount" type="number" min="1" placeholder="e.g. 5000" required />
          </div>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="showForm = false">Cancel</button>
            <button type="submit" class="btn-submit" :disabled="investmentStore.isLoading">
              {{ investmentStore.isLoading ? 'Submitting...' : 'Submit' }}
            </button>
          </div>
        </form>
        <p v-if="successMsg" class="success-msg">{{ successMsg }}</p>
        <p v-if="investmentStore.error" class="error-msg">{{ investmentStore.error }}</p>
      </div>
    </div>
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

const showForm = ref(false)
const showProfileMenu = ref(false)
const sidebarOpen = ref(false)
const assetName = ref('')
const amount = ref<number | null>(null)
const successMsg = ref('')

onMounted(() => {
  investmentStore.fetchInvestments()
})

// Computed values for summary cards
const totalInvestment = computed(() => {
  const total = investmentStore.investments.reduce((sum: number, inv: any) => sum + Number(inv.amount), 0)
  return total >= 1000000 ? (total / 1000000).toFixed(1) + 'M' : total.toLocaleString('en-US')
})

const approvedTotal = computed(() => {
  const total = investmentStore.investments
    .filter((inv: any) => inv.status === 'approved')
    .reduce((sum: number, inv: any) => sum + Number(inv.amount), 0)
  return total >= 1000000 ? (total / 1000000).toFixed(1) + 'M' : total.toLocaleString('en-US')
})

const pendingCount = computed(() => {
  return investmentStore.investments.filter((inv: any) => inv.status === 'pending').length
})

const approvedInvestments = computed(() => {
  return investmentStore.investments.filter((inv: any) => inv.status === 'approved')
})

// Generate notifications from recent activity
const recentNotifications = computed(() => {
  const notifs: string[] = []
  investmentStore.investments.forEach((inv: any) => {
    if (inv.status === 'approved') {
      notifs.push(`Your investment on ${inv.asset_name} has been approved`)
    } else if (inv.status === 'rejected') {
      notifs.push(`Your investment on ${inv.asset_name} has been rejected`)
    }
  })
  return notifs.slice(0, 4)
})

// Simple chart data generation
const chartPoints = computed(() => {
  const investments = investmentStore.investments
  if (investments.length === 0) return [{ x: 0, y: 75 }, { x: 400, y: 75 }]

  const sorted = [...investments].sort((a: any, b: any) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
  const amounts = sorted.map((inv: any) => Number(inv.amount))
  const max = Math.max(...amounts, 1)
  const min = Math.min(...amounts, 0)
  const range = max - min || 1

  return sorted.map((inv: any, i: number) => ({
    x: (i / Math.max(sorted.length - 1, 1)) * 380 + 10,
    y: 140 - ((Number(inv.amount) - min) / range) * 120
  }))
})

const chartLinePath = computed(() => {
  return chartPoints.value.map((pt, i) => `${i === 0 ? 'M' : 'L'}${pt.x},${pt.y}`).join(' ')
})

const chartAreaPath = computed(() => {
  const pts = chartPoints.value
  if (pts.length === 0) return ''
  const line = pts.map((pt, i) => `${i === 0 ? 'M' : 'L'}${pt.x},${pt.y}`).join(' ')
  return line + ' L' + pts[pts.length - 1].x + ',150 L' + pts[0].x + ',150 Z'
})

const chartYLabels = computed(() => {
  const investments = investmentStore.investments
  if (investments.length === 0) return ['0', '250', '500', '750', '1000']
  const amounts = investments.map((inv: any) => Number(inv.amount))
  const max = Math.max(...amounts, 1000)
  const step = Math.ceil(max / 4)
  return [max, max - step, max - step * 2, max - step * 3].map(v => v.toLocaleString())
})

function formatDate(dateStr: string) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' })
}

async function handleSubmit() {
  successMsg.value = ''
  if (!amount.value || !assetName.value) return

  const ok = await investmentStore.submitInvestment(amount.value, assetName.value)
  if (ok) {
    successMsg.value = 'Investment submitted successfully!'
    assetName.value = ''
    amount.value = null
    setTimeout(() => { showForm.value = false; successMsg.value = '' }, 1500)
  }
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
/* ===== Layout ===== */
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

.nav-right {
  position: relative;
}

.profile-btn {
  display: flex;
  align-items: center;
}

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
  transition: background 0.2s;
}
.dropdown-logout:hover { background: #c53030; }

/* ===== Main Content ===== */
.main-content {
  padding: 2rem 2.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.welcome-heading {
  font-size: 2rem;
  font-weight: 800;
  color: #1a202c;
  margin-bottom: 1.5rem;
}

/* ===== Summary Row ===== */
.summary-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr) auto;
  gap: 1rem;
  margin-bottom: 1.5rem;
  align-items: stretch;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 1.2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  border: 1px solid #edf2f7;
}

.summary-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-green { background: #c6f6d5; color: #276749; }
.icon-blue { background: #bee3f8; color: #2b6cb0; }
.icon-orange { background: #feebc8; color: #c05621; }

.summary-info {
  display: flex;
  flex-direction: column;
}

.summary-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1a202c;
}

.summary-label {
  font-size: 0.8rem;
  color: #a0aec0;
  margin-top: 0.15rem;
}

.add-investment-btn {
  background: #1a1a1a;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background 0.2s;
  white-space: nowrap;
}

.add-investment-btn:hover { background: #333; }

.add-icon {
  font-size: 1.4rem;
  background: white;
  color: #1a1a1a;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

/* ===== Middle Row ===== */
.middle-row {
  display: grid;
  grid-template-columns: 1fr 2fr 1fr;
  gap: 1rem;
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

/* Active Investments */
.investment-list-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #a0aec0;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #edf2f7;
  margin-bottom: 0.5rem;
}

.investment-list-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  font-size: 0.9rem;
  color: #4a5568;
  border-bottom: 1px solid #f7fafc;
}

.empty-list {
  color: #a0aec0;
  font-style: italic;
  font-size: 0.85rem;
  padding: 0.5rem 0;
}

/* Chart */
.chart-card {
  position: relative;
}

.chart-placeholder {
  position: relative;
  height: 160px;
}

.chart-svg {
  width: 100%;
  height: 100%;
}

.chart-y-labels {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  font-size: 0.7rem;
  color: #a0aec0;
  pointer-events: none;
  padding: 5px 0;
}

/* Notifications */
.notifications-card {
  display: flex;
  flex-direction: column;
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

/* ===== Table ===== */
.table-card {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 0.85rem 1rem;
  text-align: left;
  border-bottom: 1px solid #edf2f7;
  font-size: 0.9rem;
}

th {
  color: #a0aec0;
  font-size: 0.78rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.name-cell {
  font-weight: 600;
  color: #1a202c;
}

.row-selected {
  background: #ebf8ff;
}

.badge {
  padding: 0.25rem 0.7rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.03em;
}

.badge-pending { background: #fefcbf; color: #b7791f; }
.badge-approved { background: #c6f6d5; color: #276749; }
.badge-rejected { background: #fed7d7; color: #c53030; }

.empty-table {
  text-align: center;
  color: #a0aec0;
  font-style: italic;
  padding: 2rem !important;
}

/* ===== Modal ===== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 500;
}

.modal {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  width: 420px;
  max-width: 90vw;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal h2 {
  font-size: 1.3rem;
  margin-bottom: 1.5rem;
  color: #1a202c;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 0.3rem;
}

.form-group input {
  width: 100%;
  padding: 0.65rem 0.8rem;
  border: 1px solid #cbd5e0;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
}

.modal-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.btn-cancel {
  flex: 1;
  padding: 0.6rem;
  background: #edf2f7;
  color: #4a5568;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}
.btn-cancel:hover { background: #e2e8f0; }

.btn-submit {
  flex: 1;
  padding: 0.6rem;
  background: #1a1a1a;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}
.btn-submit:hover { background: #333; }
.btn-submit:disabled { background: #a0aec0; cursor: not-allowed; }

.success-msg { color: #38a169; margin-top: 0.75rem; font-weight: 600; font-size: 0.9rem; }
.error-msg { color: #e53e3e; margin-top: 0.75rem; font-weight: 600; font-size: 0.9rem; }

/* ===== Responsive ===== */
@media (max-width: 900px) {
  .summary-row { grid-template-columns: 1fr 1fr; }
  .middle-row { grid-template-columns: 1fr; }
  .main-content { padding: 1.5rem 1rem; }
}

@media (max-width: 600px) {
  .summary-row { grid-template-columns: 1fr; }
  .welcome-heading { font-size: 1.5rem; }
}
</style>
