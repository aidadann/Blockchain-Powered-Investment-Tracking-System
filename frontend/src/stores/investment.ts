import { defineStore } from 'pinia'
import api from '../api'

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
                const response = await api.post('/investments', {
                    amount,
                    asset_name: assetName
                });

                this.investments.push(response.data.investment);
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
                const response = await api.patch(`/investments/${id}/approve`);

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
