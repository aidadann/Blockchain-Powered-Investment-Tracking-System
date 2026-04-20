import { ethers } from "hardhat";

async function main() {
    console.log("Starting deployment...");

    // Get the ContractFactory
    const InvestmentTracker = await ethers.getContractFactory("InvestmentTracker");

    // Deploy the contract
    const tracker = await InvestmentTracker.deploy();

    // Wait for deployment to finish
    await tracker.waitForDeployment();

    console.log(`InvestmentTracker deployed to: ${await tracker.getAddress()}`);
}

// We recommend this pattern to be able to use async/await everywhere
// and properly handle errors.
main().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
