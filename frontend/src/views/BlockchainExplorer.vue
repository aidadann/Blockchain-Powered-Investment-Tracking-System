<template>
  <div class="explorer-layout">
    <!-- Top Navbar -->
    <nav class="top-nav">
      <button class="nav-btn">☰</button>
      <span class="nav-title">⛓️ Blockchain Explorer</span>
      <button class="nav-btn nav-back" @click="router.back()">← Back</button>
    </nav>

    <main class="main-content">
      <h1 class="welcome-heading">Blockchain Explorer</h1>
      <p class="subtitle">Live on-chain data read directly from the smart contract — not the database.</p>

      <!-- Network Info Cards -->
      <div class="info-row">
        <div class="info-card">
          <div class="info-label">🌐 Network</div>
          <div class="info-value">Hardhat Local (Chain ID: 31337)</div>
        </div>
        <div class="info-card">
          <div class="info-label">📄 Contract Address</div>
          <div class="info-value mono">{{ contractAddress }}</div>
        </div>
        <div class="info-card">
          <div class="info-label">🧱 Latest Block</div>
          <div class="info-value">{{ latestBlock !== null ? '#' + latestBlock : 'Loading...' }}</div>
        </div>
        <div class="info-card">
          <div class="info-label">📊 Investments On-Chain</div>
          <div class="info-value">{{ onChainInvestments.length }}</div>
        </div>
      </div>

      <!-- Cross-Reference Table -->
      <div class="card">
        <div class="card-header">
          <h2>On-Chain vs Database Cross-Reference</h2>
          <button class="btn-refresh" @click="loadData" :disabled="isLoading">
            {{ isLoading ? 'Reading chain...' : '🔄 Refresh' }}
          </button>
        </div>
        <p class="card-desc">
          The table below compares the database record (off-chain) with the actual smart contract state (on-chain).
          A ✅ match proves the blockchain data is authentic and untampered.
        </p>

        <p v-if="isLoading" class="loading-msg">Connecting to local Hardhat node at http://127.0.0.1:8545...</p>
        <p v-if="error" class="error-msg">{{ error }}</p>

        <div v-if="!isLoading && crossRef.length > 0" class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>DB ID</th>
                <th>Asset Name</th>
                <th>Amount</th>
                <th>DB Status</th>
                <th>On-Chain Approved</th>
                <th>Match</th>
                <th>Blockchain Hash</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in crossRef" :key="row.id">
                <td>#{{ row.id }}</td>
                <td>{{ row.asset_name }}</td>
                <td>${{ Number(row.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                <td>
                  <span :class="'badge badge-' + row.db_status">{{ row.db_status }}</span>
                </td>
                <td>
                  <span v-if="row.chain_found" :class="row.chain_approved ? 'badge badge-approved' : 'badge badge-pending'">
                    {{ row.chain_approved ? 'approved' : 'pending' }}
                  </span>
                  <span v-else class="badge badge-missing">not found</span>
                </td>
                <td class="match-cell">
                  <span v-if="row.chain_found" :class="row.match ? 'match-yes' : 'match-no'">
                    {{ row.match ? '✅ Match' : '❌ Mismatch' }}
                  </span>
                  <span v-else class="match-na">— N/A</span>
                </td>
                <td class="hash-cell">
                  <span v-if="row.blockchain_hash" class="hash-text" :title="row.blockchain_hash">
                    {{ row.blockchain_hash.slice(0, 18) }}...
                  </span>
                  <span v-else class="no-hash">No hash yet</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else-if="!isLoading && crossRef.length === 0 && !error" class="empty-state">
          No investments found. Submit one from the Investor Dashboard first!
        </p>
      </div>

      <!-- Raw On-Chain Records -->
      <div class="card">
        <h2>Raw Smart Contract Records</h2>
        <p class="card-desc">These records are read directly from <code>InvestmentTracker.sol</code> on the blockchain.</p>

        <div v-if="onChainInvestments.length > 0" class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Investment ID</th>
                <th>Investor Wallet</th>
                <th>Amount (Wei)</th>
                <th>Asset Name</th>
                <th>Is Approved</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="inv in onChainInvestments" :key="inv.id">
                <td>#{{ inv.id }}</td>
                <td class="mono small">{{ inv.investor }}</td>
                <td>{{ inv.amount }}</td>
                <td>{{ inv.assetName }}</td>
                <td>
                  <span :class="inv.isApproved ? 'badge badge-approved' : 'badge badge-pending'">
                    {{ inv.isApproved ? '✅ Yes' : '⏳ No' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else-if="!isLoading" class="empty-state">No on-chain records found.</p>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getReadOnlyContract, getProvider } from '../services/blockchain'
import api from '../api'

const router = useRouter()

const CONTRACT_ADDRESS = '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512'
const contractAddress = ref(CONTRACT_ADDRESS)
const latestBlock = ref<number | null>(null)
const isLoading = ref(false)
const error = ref('')

const onChainInvestments = ref<any[]>([])
const crossRef = ref<any[]>([])

onMounted(() => {
  loadData()
})

async function loadData() {
  isLoading.value = true
  error.value = ''
  onChainInvestments.value = []
  crossRef.value = []

  try {
    // Get latest block number
    const provider = getProvider()
    latestBlock.value = await provider.getBlockNumber()

    // Fetch DB investments
    const dbResponse = await api.get('/investments')
    const dbInvestments: any[] = dbResponse.data

    // For each DB investment, try to read its on-chain record
    const onChain: any[] = []
    const cross: any[] = []

    for (const inv of dbInvestments) {
      let chainRecord = null

      try {
        const contract = getReadOnlyContract()
        const result = await contract.investments(inv.id)

        // If investor address is zero, it doesn't exist on-chain
        const zeroAddress = '0x0000000000000000000000000000000000000000'
        if (result[0] !== zeroAddress) {
          chainRecord = {
            id: inv.id,
            investor: result[0],
            amount: Number(result[1]),
            assetName: result[2],
            isApproved: result[3]
          }
          onChain.push(chainRecord)
        }
      } catch (e) {
        // Investment not found on chain
      }

      // Build cross-reference row
      const dbApproved = inv.status === 'approved'
      const chainApproved = chainRecord?.isApproved ?? false
      const chainFound = chainRecord !== null

      cross.push({
        id: inv.id,
        asset_name: inv.asset_name,
        amount: inv.amount,
        db_status: inv.status,
        blockchain_hash: inv.blockchain_hash,
        chain_found: chainFound,
        chain_approved: chainApproved,
        match: chainFound && dbApproved === chainApproved
      })
    }

    onChainInvestments.value = onChain
    crossRef.value = cross

  } catch (err: any) {
    error.value = 'Could not connect to Hardhat node. Make sure "npx hardhat node" is running at http://127.0.0.1:8545'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.explorer-layout {
  min-height: 100vh;
  background: #f5f5f5;
}

/* ===== Navbar ===== */
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
  font-size: 1.1rem;
}

.nav-title {
  color: white;
  font-weight: 700;
  font-size: 1rem;
}

.nav-back {
  font-size: 0.9rem;
  color: #a0aec0;
  transition: color 0.2s;
}
.nav-back:hover { color: white; }

/* ===== Main ===== */
.main-content {
  padding: 2rem 2.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.welcome-heading {
  font-size: 2rem;
  font-weight: 800;
  color: #1a202c;
  margin-bottom: 0.3rem;
}

.subtitle {
  color: #718096;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
}

/* ===== Info Row ===== */
.info-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.info-card {
  background: white;
  border-radius: 12px;
  padding: 1rem 1.2rem;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  border: 1px solid #edf2f7;
}

.info-label {
  font-size: 0.78rem;
  color: #a0aec0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.4rem;
}

.info-value {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a202c;
  word-break: break-all;
}

.mono { font-family: monospace; font-size: 0.82rem; }

/* ===== Cards ===== */
.card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  border: 1px solid #edf2f7;
}

.card h2 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 0.5rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.card-desc {
  font-size: 0.875rem;
  color: #718096;
  margin-bottom: 1rem;
  line-height: 1.6;
}

code {
  background: #edf2f7;
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  font-family: monospace;
  font-size: 0.85rem;
}

.btn-refresh {
  padding: 0.45rem 1rem;
  background: #1a1a1a;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
  transition: background 0.2s;
  white-space: nowrap;
}
.btn-refresh:hover { background: #333; }
.btn-refresh:disabled { background: #a0aec0; cursor: not-allowed; }

/* ===== Table ===== */
.table-wrapper { overflow-x: auto; }

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 0.75rem 0.9rem;
  text-align: left;
  border-bottom: 1px solid #edf2f7;
  font-size: 0.875rem;
}

th {
  background: #f7fafc;
  color: #a0aec0;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge {
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}
.badge-pending { background: #fefcbf; color: #b7791f; }
.badge-approved { background: #c6f6d5; color: #276749; }
.badge-rejected { background: #fed7d7; color: #c53030; }
.badge-missing { background: #e2e8f0; color: #4a5568; }

.match-yes { color: #276749; font-weight: 700; }
.match-no { color: #c53030; font-weight: 700; }
.match-na { color: #a0aec0; }

.hash-cell { max-width: 200px; }
.hash-text {
  font-family: monospace;
  font-size: 0.8rem;
  color: #4299e1;
  cursor: help;
}
.no-hash { color: #a0aec0; font-style: italic; font-size: 0.82rem; }

.small { font-size: 0.78rem; }

.loading-msg { color: #4299e1; font-weight: 600; }
.error-msg { color: #e53e3e; font-weight: 600; background: #fff5f5; padding: 0.75rem; border-radius: 8px; }
.empty-state { color: #a0aec0; font-style: italic; }

/* ===== Responsive ===== */
@media (max-width: 900px) {
  .info-row { grid-template-columns: 1fr 1fr; }
  .main-content { padding: 1.5rem 1rem; }
  .welcome-heading { font-size: 1.5rem; }
}
</style>
