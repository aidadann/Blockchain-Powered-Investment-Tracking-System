import { ethers } from 'ethers'

// Contract address from Hardhat deployment
// Update this if you redeploy the contract
const CONTRACT_ADDRESS = '0x5FbDB2315678afecb367f032d93F642f64180aa3'

// Only the ABI entries we actually need (submit, approve, read)
const CONTRACT_ABI = [
  'function submitInvestment(uint256 _id, uint256 _amount, string memory _assetName) public',
  'function approveInvestment(uint256 _id) public',
  'function investments(uint256) public view returns (address investor, uint256 amount, string assetName, bool isApproved)',
  'event InvestmentApproved(uint256 id)'
]

// Hardhat local node RPC URL
const HARDHAT_RPC_URL = 'http://127.0.0.1:8545'

/**
 * Get a read-only provider connected to the local Hardhat node.
 */
export function getProvider() {
  return new ethers.JsonRpcProvider(HARDHAT_RPC_URL)
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
  return new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer)
}

/**
 * Get a read-only contract instance (no MetaMask needed).
 */
export function getReadOnlyContract() {
  const provider = getProvider()
  return new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, provider)
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
