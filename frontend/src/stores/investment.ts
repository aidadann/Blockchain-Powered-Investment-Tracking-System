import { defineStore } from 'pinia'
import api from '../api'
import { submitInvestmentOnChain, approveInvestmentOnChain } from '../services/blockchain'

export const useInvestmentStore = defineStore('investment', {
    state: () => ({
        investments: [] as any[],
        isLoading: false,
        error: null as string | null,
    }),

    actions: {
        async fetchInvestments() {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await api.get('/investments');
                this.investments = response.data;
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to fetch investments';
                console.error(error);
            } finally {
                this.isLoading = false;
            }
        },

        async submitInvestment(amount: number, assetName: string) {
            this.isLoading = true;
            this.error = null;
            try {
                // Step 1: Save to Laravel database first (to get the investment ID)
                const response = await api.post('/investments', {
                    amount,
                    asset_name: assetName
                });

                const investment = response.data.investment;

                // Step 2: Record on blockchain using the database ID (REQUIRED)
                try {
                    const txHash = await submitInvestmentOnChain(
                        investment.id,
                        amount,
                        assetName
                    );

                    // Step 3: Update the database with the blockchain hash
                    await api.patch('/investments/' + investment.id + '/hash', {
                        blockchain_hash: txHash
                    });

                    investment.blockchain_hash = txHash;
                } catch (blockchainError: any) {
                    // MetaMask was rejected or closed — roll back the off-chain record
                    console.warn('MetaMask transaction failed. Rolling back database record:', blockchainError.message);
                    try {
                        await api.delete('/investments/' + investment.id);
                    } catch (deleteError: any) {
                        console.error('Failed to roll back investment record:', deleteError.message);
                    }
                    this.error = 'MetaMask transaction was not completed. Investment was not submitted. Please try again and complete the MetaMask confirmation.';
                    return false;
                }

                // Add to local state only after successful blockchain recording
                this.investments.push(investment);
                return true;
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to submit investment';
                console.error(error);
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        async approveInvestment(id: number) {
            this.isLoading = true;
            this.error = null;
            try {
                // Step 1: Record approval on blockchain first (REQUIRED)
                // If MetaMask is cancelled/rejected, this will throw and skip Step 2 entirely
                const txHash = await approveInvestmentOnChain(id);

                // Step 2: Approve in Laravel database (only runs if MetaMask succeeded)
                const response = await api.patch('/investments/' + id + '/approve');

                // Step 3: Update the blockchain hash
                await api.patch('/investments/' + id + '/hash', {
                    blockchain_hash: txHash
                });
                response.data.investment.blockchain_hash = txHash;

                // Update local state
                const index = this.investments.findIndex((inv) => inv.id === id);
                if (index !== -1) {
                    this.investments[index] = response.data.investment;
                }
                return true;
            } catch (error: any) {
                this.error = error.response?.data?.message || 'MetaMask transaction was not completed. Approval was not processed. Please try again.';
                console.error('Approve failed:', error);
                return false;
            } finally {
                this.isLoading = false;
            }
        },

        async rejectInvestment(id: number) {
            this.isLoading = true;
            this.error = null;
            try {
                // Reject in Laravel database
                const response = await api.patch('/investments/' + id + '/reject');

                // Update local state
                const index = this.investments.findIndex((inv) => inv.id === id);
                if (index !== -1) {
                    this.investments[index] = response.data.investment;
                }
                return true;
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to reject investment';
                console.error(error);
                return false;
            } finally {
                this.isLoading = false;
            }
        }
    }
})
