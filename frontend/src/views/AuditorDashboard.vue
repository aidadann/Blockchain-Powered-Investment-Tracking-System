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
          <p class="profile-role">Auditor</p>
          <hr />
          <button @click="handleLogout" class="dropdown-logout">Logout</button>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <h1 class="welcome-heading">Welcome back, Auditor</h1>

      <div class="top-row">
        <!-- User Logs (Left) -->
        <div class="card users-card">
          <h3 class="card-title">User Logs</h3>
          <div class="user-list">
            <div v-for="user in uniqueUsers" :key="user" class="user-item">
              <div class="user-avatar">{{ user.charAt(0).toUpperCase() }}</div>
              <span class="user-name">{{ user }}</span>
            </div>
            <p v-if="uniqueUsers.length === 0" class="empty-list">No user activity logged yet.</p>
          </div>
        </div>

        <!-- Right Column -->
        <div class="right-column">
          <!-- Report Buttons -->
          <div class="report-buttons">
            <button class="btn-report" @click="generateReport('pdf')">Generate Report (pdf)</button>
            <button class="btn-report btn-report-alt" @click="generateReport('csv')">Generate Report (csv)</button>
          </div>

          <!-- Audit Trail Timeline -->
          <div class="card timeline-card">
            <div class="timeline-list">
              <div v-for="log in logs" :key="log.id" class="timeline-item">
                <div class="timeline-date">{{ formatShortDate(log.created_at) }}</div>
                <div class="timeline-content">
                  <span class="timeline-user">{{ log.user?.name || 'User #' + log.user_id }}</span>
                  <span class="timeline-action">{{ log.action }}</span>
                  <p class="timeline-details">• {{ log.details || 'No additional details' }}</p>
                </div>
              </div>
              <p v-if="logs.length === 0" class="empty-list">No audit logs recorded yet.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="card notifications-card">
        <div class="notif-header">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4a5568" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="notif-count">{{ logs.length }}</span>
        </div>
        <p class="notif-title">Notifications</p>
        <ul class="notif-list">
          <li v-for="log in recentLogs" :key="'notif-' + log.id">
            {{ log.details || log.action }}
          </li>
          <li v-if="recentLogs.length === 0">No new notifications.</li>
        </ul>
      </div>

      <p v-if="error" class="error-msg">{{ error }}</p>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../api'

const router = useRouter()
const auth = useAuthStore()

const logs = ref<any[]>([])
const isLoading = ref(false)
const error = ref('')
const showProfileMenu = ref(false)

onMounted(async () => {
  isLoading.value = true
  try {
    const response = await api.get('/audits')
    logs.value = response.data.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to fetch audit logs'
  } finally {
    isLoading.value = false
  }
})

const uniqueUsers = computed(() => {
  const names = logs.value
    .map((log: any) => log.user?.name || 'User #' + log.user_id)
    .filter((name: string, index: number, self: string[]) => self.indexOf(name) === index)
  return names
})

const recentLogs = computed(() => logs.value.slice(0, 4))

function formatShortDate(dateStr: string) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

function generateReport(type: string) {
  if (logs.value.length === 0) {
    alert('No audit logs to export.')
    return
  }

  if (type === 'csv') {
    const header = 'ID,User,Action,Details,Timestamp\n'
    const rows = logs.value.map((log: any) =>
      `${log.id},"${log.user?.name || 'User #' + log.user_id}","${log.action}","${log.details || ''}","${log.created_at}"`
    ).join('\n')
    const blob = new Blob([header + rows], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'audit_report.csv'
    a.click()
    URL.revokeObjectURL(url)
  } else {
    // Simple printable report for PDF
    const content = logs.value.map((log: any) =>
      `[${log.created_at}] ${log.user?.name || 'User #' + log.user_id} — ${log.action}: ${log.details || 'N/A'}`
    ).join('\n\n')
    const printWindow = window.open('', '_blank')
    if (printWindow) {
      printWindow.document.write(`
        <html><head><title>Audit Report</title>
        <style>body { font-family: 'Segoe UI', sans-serif; padding: 2rem; line-height: 1.8; } h1 { margin-bottom: 1rem; }</style>
        </head><body>
        <h1>Audit Trail Report</h1>
        <pre>${content}</pre>
        </body></html>
      `)
      printWindow.document.close()
      printWindow.print()
    }
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
  grid-template-columns: 1.2fr 1.8fr;
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

/* ===== User Logs ===== */
.user-list {
  max-height: 380px;
  overflow-y: auto;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.7rem 0;
  border-bottom: 1px solid #f7fafc;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #4a5568;
  font-size: 1rem;
  flex-shrink: 0;
}

.user-name {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1a202c;
}

/* ===== Right Column ===== */
.right-column {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Report Buttons */
.report-buttons {
  display: flex;
  gap: 0.75rem;
}

.btn-report {
  flex: 1;
  padding: 0.7rem 1rem;
  background: #1a1a1a;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: background 0.2s;
}

.btn-report:hover { background: #333; }

.btn-report-alt {
  background: white;
  color: #1a202c;
  border: 2px solid #1a1a1a;
}

.btn-report-alt:hover { background: #f7fafc; }

/* Timeline */
.timeline-card {
  max-height: 350px;
  overflow-y: auto;
}

.timeline-item {
  display: flex;
  gap: 1rem;
  padding: 0.7rem 0;
  border-bottom: 1px solid #f7fafc;
}

.timeline-date {
  font-size: 0.82rem;
  font-weight: 700;
  color: #1a202c;
  min-width: 55px;
  flex-shrink: 0;
}

.timeline-content {
  display: flex;
  flex-direction: column;
}

.timeline-user {
  font-weight: 600;
  color: #1a202c;
  font-size: 0.9rem;
}

.timeline-action {
  font-size: 0.82rem;
  color: #4299e1;
  font-weight: 500;
}

.timeline-details {
  font-size: 0.8rem;
  color: #718096;
  margin-top: 0.15rem;
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
  word-wrap: break-word;
  word-break: break-word;
}

.empty-list {
  color: #a0aec0;
  font-style: italic;
  font-size: 0.85rem;
  padding: 0.5rem 0;
}

.error-msg { color: #e53e3e; font-weight: 600; margin-top: 1rem; }

/* ===== Responsive ===== */
@media (max-width: 800px) {
  .top-row { grid-template-columns: 1fr; }
  .main-content { padding: 1.5rem 1rem; }
  .welcome-heading { font-size: 1.5rem; }
  .report-buttons { flex-direction: column; }
}
</style>
