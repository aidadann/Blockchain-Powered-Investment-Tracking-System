import { expect } from "chai";
import { ethers } from "hardhat";

describe("InvestmentTracker", function () {
    let investmentTracker: any;
    let admin: any;
    let investor: any;
    let nonAdmin: any;

    beforeEach(async function () {
        // Grab the fake hardhat accounts
        // admin = deployer (Account #0), investor = Account #1, nonAdmin = Account #2
        [admin, investor, nonAdmin] = await ethers.getSigners();

        // Deploy a fresh contract before every single test
        const InvestmentTracker = await ethers.getContractFactory("InvestmentTracker");
        investmentTracker = await InvestmentTracker.deploy();
        await investmentTracker.waitForDeployment();
    });

    // =========================================================================
    // WB-01 to WB-03: RoleManager — Role Assignment Tests
    // =========================================================================

    describe("RoleManager", function () {

        it("WB-01: Deployer is assigned DEFAULT_ADMIN_ROLE", async function () {
            const DEFAULT_ADMIN_ROLE = await investmentTracker.DEFAULT_ADMIN_ROLE();
            const hasRole = await investmentTracker.hasRole(DEFAULT_ADMIN_ROLE, admin.address);
            expect(hasRole).to.equal(true);
        });

        it("WB-02: Deployer is assigned SYSTEM_ADMIN_ROLE", async function () {
            const SYSTEM_ADMIN_ROLE = await investmentTracker.SYSTEM_ADMIN_ROLE();
            const hasRole = await investmentTracker.hasRole(SYSTEM_ADMIN_ROLE, admin.address);
            expect(hasRole).to.equal(true);
        });

        it("WB-03: Non-admin address does not have SYSTEM_ADMIN_ROLE", async function () {
            const SYSTEM_ADMIN_ROLE = await investmentTracker.SYSTEM_ADMIN_ROLE();
            const hasRole = await investmentTracker.hasRole(SYSTEM_ADMIN_ROLE, nonAdmin.address);
            expect(hasRole).to.equal(false);
        });

    });

    // =========================================================================
    // WB-04 to WB-09: InvestmentTracker — Core Logic Tests
    // =========================================================================

    describe("InvestmentTracker", function () {

        it("WB-04: Submit an investment with valid parameters", async function () {
            // Investor submits a valid investment
            await investmentTracker.connect(investor).submitInvestment(1, 5000, "Bitcoin");

            // Fetch the stored investment to verify
            const investment = await investmentTracker.investments(1);

            expect(investment.investor).to.equal(investor.address);
            expect(investment.amount).to.equal(5000);
            expect(investment.assetName).to.equal("Bitcoin");
            expect(investment.isApproved).to.equal(false);
        });

        it("WB-05: Submitting with a duplicate ID is reverted", async function () {
            // First submission succeeds
            await investmentTracker.connect(investor).submitInvestment(1, 5000, "Bitcoin");

            // Second submission with the same ID should revert
            await expect(
                investmentTracker.connect(investor).submitInvestment(1, 1000, "Gold")
            ).to.be.revertedWith("Investment ID already exists");
        });

        it("WB-06: Non-admin cannot call approveInvestment()", async function () {
            // Investor submits an investment
            await investmentTracker.connect(investor).submitInvestment(1, 5000, "Bitcoin");

            // Non-admin (hacker) tries to approve — should revert with AccessControl error
            await expect(
                investmentTracker.connect(nonAdmin).approveInvestment(1)
            ).to.be.revertedWithCustomError(
                investmentTracker,
                "AccessControlUnauthorizedAccount"
            );
        });

        it("WB-07: Admin successfully approves investment and emits event", async function () {
            // Investor submits an investment
            await investmentTracker.connect(investor).submitInvestment(1, 5000, "Bitcoin");

            // Admin approves — should emit InvestmentApproved event with correct ID
            await expect(investmentTracker.connect(admin).approveInvestment(1))
                .to.emit(investmentTracker, "InvestmentApproved")
                .withArgs(1);

            // Verify the on-chain state has been updated
            const investment = await investmentTracker.investments(1);
            expect(investment.isApproved).to.equal(true);
        });

        it("WB-08: Approving a non-existent investment reverts", async function () {
            // Try to approve investment ID 999 which was never submitted
            await expect(
                investmentTracker.connect(admin).approveInvestment(999)
            ).to.be.revertedWith("Investment does not exist");
        });

        it("WB-09: Approving an already-approved investment reverts", async function () {
            // Submit and approve an investment
            await investmentTracker.connect(investor).submitInvestment(1, 5000, "Bitcoin");
            await investmentTracker.connect(admin).approveInvestment(1);

            // Try to approve the same investment again — should revert
            await expect(
                investmentTracker.connect(admin).approveInvestment(1)
            ).to.be.revertedWith("Investment already approved");
        });

    });
});