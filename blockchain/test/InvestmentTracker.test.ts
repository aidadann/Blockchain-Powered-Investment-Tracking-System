import { expect } from "chai";
import { ethers } from "hardhat";

describe("InvestmentTracker", function () {
    let investmentTracker: any;
    let admin: any;
    let investor: any;
    let hacker: any;

    beforeEach(async function () {
        // Grab the fake hardhat accounts
        [admin, investor, hacker] = await ethers.getSigners();

        // Deploy a fresh contract before every single test
        const InvestmentTracker = await ethers.getContractFactory("InvestmentTracker");
        investmentTracker = await InvestmentTracker.deploy();
        await investmentTracker.waitForDeployment();
    });

    it("Should allow an investor to submit an investment", async function () {
        // The investor connects and calls submitInvestment
        await investmentTracker.connect(investor).submitInvestment(1, 500, "Real Estate Property A");

        // Fetch the stored investment to verify
        const investment = await investmentTracker.investments(1);

        expect(investment.investor).to.equal(investor.address);
        expect(investment.amount).to.equal(500);
        expect(investment.isApproved).to.equal(false);
    });

    it("Should allow the admin to approve an investment", async function () {
        // Investor submits it first
        await investmentTracker.connect(investor).submitInvestment(2, 1000, "Startup Equity");

        // Admin approves it
        await investmentTracker.connect(admin).approveInvestment(2);

        const investment = await investmentTracker.investments(2);
        expect(investment.isApproved).to.equal(true);
    });

    it("Should REJECT a non-admin trying to approve an investment", async function () {
        await investmentTracker
            .connect(investor)
            .submitInvestment(3, 200, "Gold");

        await expect(
            investmentTracker
                .connect(hacker)
                .approveInvestment(3)
        ).to.be.revertedWithCustomError(
            investmentTracker,
            "AccessControlUnauthorizedAccount"
        );
    });
});