/**
 * fund-wallet.cjs
 * 
 * Standalone Node.js script that sends Sepolia test ETH from a System Wallet
 * to a recipient address. Called by the Laravel backend via shell_exec().
 * 
 * Usage:
 *   node fund-wallet.cjs <recipientAddress> <amountInEth>
 * 
 * Environment variables (from .env):
 *   SEPOLIA_RPC_URL          - Alchemy RPC endpoint
 *   DEPLOYER_PRIVATE_KEY     - System wallet private key (the pre-funded wallet)
 * 
 * Output:
 *   JSON to stdout: { "success": true, "tx_hash": "0x..." }
 *   or:             { "success": false, "error": "..." }
 */

const { ethers } = require("ethers");
const dotenv = require("dotenv");
const path = require("path");

// Load .env from the backend directory
dotenv.config({ path: path.resolve(__dirname, "../.env") });

async function main() {
  const recipientAddress = process.argv[2];
  const amountEth = process.argv[3] || "0.05";

  // Validate inputs
  if (!recipientAddress) {
    console.log(JSON.stringify({ success: false, error: "Recipient address is required" }));
    process.exit(1);
  }

  if (!ethers.isAddress(recipientAddress)) {
    console.log(JSON.stringify({ success: false, error: "Invalid Ethereum address" }));
    process.exit(1);
  }

  const privateKey = process.env.DEPLOYER_PRIVATE_KEY;
  const rpcUrl = process.env.SEPOLIA_RPC_URL;

  if (!privateKey) {
    console.log(JSON.stringify({ success: false, error: "DEPLOYER_PRIVATE_KEY not set in .env" }));
    process.exit(1);
  }

  if (!rpcUrl) {
    console.log(JSON.stringify({ success: false, error: "SEPOLIA_RPC_URL not set in .env" }));
    process.exit(1);
  }

  try {
    // Connect to Sepolia via Alchemy
    const provider = new ethers.JsonRpcProvider(rpcUrl);
    const wallet = new ethers.Wallet(privateKey, provider);

    // Check system wallet balance before sending
    const balance = await provider.getBalance(wallet.address);
    const amountWei = ethers.parseEther(amountEth);

    if (balance < amountWei) {
      console.log(JSON.stringify({
        success: false,
        error: `Insufficient system wallet balance. Has: ${ethers.formatEther(balance)} ETH, needs: ${amountEth} ETH`
      }));
      process.exit(1);
    }

    // Send the transaction
    const tx = await wallet.sendTransaction({
      to: recipientAddress,
      value: amountWei,
    });

    // Wait for confirmation (1 block)
    const receipt = await tx.wait(1);

    console.log(JSON.stringify({
      success: true,
      tx_hash: receipt.hash,
      from: wallet.address,
      to: recipientAddress,
      amount_eth: amountEth,
      block_number: receipt.blockNumber,
    }));

  } catch (error) {
    console.log(JSON.stringify({
      success: false,
      error: error.message || "Transaction failed"
    }));
    process.exit(1);
  }
}

main();
