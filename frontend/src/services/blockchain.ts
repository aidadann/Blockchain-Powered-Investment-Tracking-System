import { ethers } from 'ethers'

// Contract address from Hardhat deployment
// Update this if you redeploy the contract
const CONTRACT_ADDRESS = '0x5eB8f731c49211Ac77c58E77befd984972512738'

// Only the ABI entries we actually need (submit, approve, read)
const CONTRACT_ABI = [
  'function submitInvestment(uint256 _id, uint256 _amount, string memory _assetName) public',
  'function approveInvestment(uint256 _id) public',
  'function investments(uint256) public view returns (address investor, uint256 amount, string assetName, bool isApproved)',
  'event InvestmentApproved(uint256 id)'
]

// Default to Sepolia RPC URL (Alchemy), but allow override via environment variable
const RPC_URL = import.meta.env.VITE_RPC_URL || 'https://eth-sepolia.g.alchemy.com/v2/FNpoIBaU2cIa09Tj99F1a'

/**
 * Get a read-only provider connected to the local Hardhat node.
 */
export function getProvider() {
  return new ethers.JsonRpcProvider(RPC_URL)
}

/**
 * Get a signer from MetaMask (browser wallet).
 * The user must have MetaMask installed and connected.
 */
export async function getSigner() {
  if (!(window as any).ethereum) {
    throw new Error('MetaMask is not installed. Please install MetaMask to continue.')
  }

  // Request account access from MetaMask
  await (window as any).ethereum.request({ method: 'eth_requestAccounts' })

  const provider = new ethers.BrowserProvider((window as any).ethereum)
  return provider.getSigner()
}

/**
 * Get a contract instance connected to a signer (for write operations).
 */
export async function getContract() {
  const signer = await getSigner()
  return new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer) as any
}

/**
 * Get a read-only contract instance (no MetaMask needed).
 */
export function getReadOnlyContract() {
  const provider = getProvider()
  return new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, provider) as any
}

/**
 * Submit an investment to the blockchain.
 * Returns the transaction hash.
 */
export async function submitInvestmentOnChain(id: number, amount: number, assetName: string) {
  const contract = await getContract()
  const tx = await contract.submitInvestment(id, amount, assetName)
  const receipt = await tx.wait()
  return receipt.hash
}

/**
 * Approve an investment on the blockchain (Admin only).
 * Returns the transaction hash.
 */
export async function approveInvestmentOnChain(id: number) {
  const contract = await getContract()
  const tx = await contract.approveInvestment(id)
  const receipt = await tx.wait()
  return receipt.hash
}

/**
 * Read an investment record from the blockchain (verify on-chain data).
 */
export async function getInvestmentFromChain(id: number) {
  const contract = getReadOnlyContract()
  const result = await contract.investments(id)
  return {
    investor: result[0],
    amount: Number(result[1]),
    assetName: result[2],
    isApproved: result[3]
  }
}

/**
 * Connect to MetaMask and return the connected wallet address.
 */
export async function connectWallet(): Promise<string> {
  if (!(window as any).ethereum) {
    throw new Error('MetaMask is not installed. Please install MetaMask to continue.')
  }

  const accounts = await (window as any).ethereum.request({ method: 'eth_requestAccounts' })
  return accounts[0] as string
}

/**
 * Listen for InvestmentApproved events emitted by the smart contract.
 * Calls the provided callback with the investment ID whenever an approval occurs.
 * Returns a cleanup function to stop listening.
 */
export function listenForApprovals(onApproved: (investmentId: number) => void): () => void {
  const contract = getReadOnlyContract()

  const handler = (id: any) => {
    onApproved(Number(id))
  }

  contract.on('InvestmentApproved', handler)

  // Return cleanup function
  return () => {
    contract.off('InvestmentApproved', handler)
  }
}

/**
 * Check if MetaMask is already connected WITHOUT prompting the user.
 * Returns the connected wallet address or null if not connected.
 */
export async function getConnectedWalletAddress(): Promise<string | null> {
  if (!(window as any).ethereum) {
    return null
  }

  try {
    // eth_accounts does NOT trigger a popup — it only returns already-connected accounts
    const accounts = await (window as any).ethereum.request({ method: 'eth_accounts' })
    return accounts.length > 0 ? accounts[0] : null
  } catch {
    return null
  }
}

/**
 * Ensure MetaMask is connected to the Sepolia test network.
 * If the user is on a different network, prompt them to switch.
 * Sepolia chainId: 0xaa36a7 (11155111 in decimal)
 */
export async function switchToSepolia(): Promise<boolean> {
  if (!(window as any).ethereum) return false

  const SEPOLIA_CHAIN_ID = '0xaa36a7'

  try {
    const currentChainId = await (window as any).ethereum.request({ method: 'eth_chainId' })

    if (currentChainId === SEPOLIA_CHAIN_ID) {
      return true // Already on Sepolia
    }

    // Request MetaMask to switch to Sepolia
    await (window as any).ethereum.request({
      method: 'wallet_switchEthereumChain',
      params: [{ chainId: SEPOLIA_CHAIN_ID }]
    })

    return true
  } catch (error: any) {
    // Error code 4902 means the chain hasn't been added to MetaMask
    if (error.code === 4902) {
      try {
        await (window as any).ethereum.request({
          method: 'wallet_addEthereumChain',
          params: [{
            chainId: SEPOLIA_CHAIN_ID,
            chainName: 'Sepolia Testnet',
            nativeCurrency: { name: 'SepoliaETH', symbol: 'ETH', decimals: 18 },
            rpcUrls: ['https://rpc.sepolia.org'],
            blockExplorerUrls: ['https://sepolia.etherscan.io']
          }]
        })
        return true
      } catch {
        return false
      }
    }
    return false
  }
}

