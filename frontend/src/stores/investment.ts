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

                // Step 2: Record on blockchain using the database ID
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
                    console.warn('Blockchain recording failed (investment saved to DB):', blockchainError.message);
                    // Investment is still saved in the database even if blockchain fails
                }

                // Add to local state
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
                // Step 1: Record approval on blockchain first
                let txHash = '';
                try {
                    txHash = await approveInvestmentOnChain(id);
                } catch (blockchainError: any) {
                    console.warn('Blockchain approval failed:', blockchainError.message);
                    // Continue with database approval even if blockchain fails
                }

                // Step 2: Approve in Laravel database
                const response = await api.patch('/investments/' + id + '/approve');

                // Update the blockchain hash if we got one
                if (txHash) {
                    await api.patch('/investments/' + id + '/hash', {
                        blockchain_hash: txHash
                    });
                    response.data.investment.blockchain_hash = txHash;
                }

                // Update local state
                const index = this.investments.findIndex((inv) => inv.id === id);
                if (index !== -1) {
                    this.investments[index] = response.data.investment;
                }
                return true;
            } catch (error: any) {
                this.error = error.response?.data?.message || 'Failed to approve investment';
                console.error(error);
                return false;
            } finally {
                this.isLoading = false;
            }
        }
    }
})
