# 🔗 Blockchain-Powered Investment Tracking System

> **Because trusting a single system admin with your money is *so* 2008.**

Welcome to the **Blockchain-Powered Investment Tracking System**! This is a full-stack, hybrid Web3 application built to solve a simple problem: how do we track investments securely without sacrificing the speed of traditional web apps or the trustless nature of the blockchain?

This project bridges the gap between high-performance relational databases and immutable ledger technology. It's designed to ensure that while your frontend loads faster than you can say "crypto," the actual financial truths are cryptographically sealed on an Ethereum network.

---

## 🏗️ Architecture: The Best of Both Worlds

We employ a **Hybrid On-Chain/Off-Chain Architecture**. 

Why? Because storing a 2MB user profile picture on the Ethereum network costs more than a decent used car. 

- **The Off-Chain Layer (Laravel & MySQL):** Handles the heavy lifting. User authentication, caching, role mappings, audit logs, and UI assets. It’s fast, queryable, and cost-effective.
- **The On-Chain Layer (Solidity & Hardhat):** Handles the truth. The investor's wallet address, the principal amount, the asset name, and the immutable approval state. 

When an investment is approved, the transaction hash is synced back to the off-chain database, proving that the local database state isn't just a figment of a rogue database admin's imagination.

---

## 🛠️ Tech Stack

### Frontend 🖥️
* **Vue 3 & Vite:** For a snappy, reactive Single Page Application (SPA) experience.
* **Pinia:** Global state management.
* **Ethers.js:** The magic wand that makes your browser talk to the blockchain via MetaMask.

### Backend ⚙️
* **Laravel 11:** The PHP powerhouse handling RESTful API routes, validation, and Sanctum token authentication.
* **MySQL 8:** Reliable relational storage for metadata and chronological audit logs.

### Blockchain ⛓️
* **Solidity (^0.8.0):** Smart contracts governing the business logic and Role-Based Access Control (RBAC).
* **Hardhat:** Local Ethereum development environment for compiling, testing, and deploying. 

---

## ✨ Key Features

- **🔐 Role-Based Access Control (RBAC):** Investors invest, Admins approve, and Auditors audit. We enforce this symmetrically: off-chain via Vue router guards and Laravel middleware, and on-chain via Solidity modifiers. If you aren't on the list, the smart contract will bounce your transaction.
- **💸 Two-Stage Investment Submission:** Submit the off-chain metadata via the Vue dashboard, then sign the state-changing transaction with MetaMask. Your details go to MySQL; your cryptographic proof goes to the blockchain.
- **✅ Admin Verification:** Admins review pending investments and execute an on-chain approval. The resulting transaction hash is stored in the database for future cross-referencing.
- **🕵️‍♂️ Built-in Blockchain Explorer:** A custom interface allowing Auditors to bypass the database entirely. It queries the Hardhat node directly via JSON-RPC so you can verify that the database matches the immutable ledger. Trust, but verify.

---

## 🚀 Local Installation (Not Deployed... Yet!)

Since this project is fresh out of the oven and currently living locally, here is how you can spin it up on your own machine. 

### Prerequisites
- Node.js & npm
- PHP & Composer (for Laravel)
- MySQL Server
- MetaMask Extension installed in your browser

### 1. The Blockchain (Hardhat)
```bash
# Navigate to the smart contract directory
cd blockchain # (or wherever your hardhat project lives)
npm install

# Start the local blockchain node
npx hardhat node 

# In a new terminal, deploy the contracts:
npx hardhat run scripts/deploy.ts --network localhost
```
*🛑 Stop and note down the deployed contract address! You'll need to copy it to your frontend and backend `.env` files.*

### 2. The Backend (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Configure your DB credentials and the CONTRACT_ADDRESS in .env, then:
php artisan migrate:fresh --seed
php artisan serve
```

### 3. The Frontend (Vue 3)
```bash
cd frontend
npm install
cp .env.example .env

# Ensure your VITE_API_URL and VITE_CONTRACT_ADDRESS are set in .env
npm run dev
```

**Final Step:** Configure MetaMask to connect to your local network (`http://127.0.0.1:8545` with Chain ID `31337`). Import one of the private keys Hardhat gave you in the terminal, and you're ready to start signing transactions!

---

## ⚠️ Disclaimer

This is an academic/portfolio project. While it uses real cryptographic principles, the local Hardhat network uses monopoly money (fake ETH). Please do not deploy this exact code to the Ethereum Mainnet and expect it to manage your life savings without a professional audit. Gas fees are real, folks.

---
*Built with ❤️, ☕, and a healthy dose of decentralization.*
