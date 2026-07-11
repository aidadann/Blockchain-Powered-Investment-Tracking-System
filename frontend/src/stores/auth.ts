import { defineStore } from 'pinia'
import api from '../api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as any | null,
        token: localStorage.getItem('token') as string | null,
        walletAddress: localStorage.getItem('walletAddress') as string | null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,

        userRole: (state) => state.user?.role?.name || null,
    },

    actions: {
        async login(email: string, password: string) {
            try {
                const response = await api.post('/auth/login', {
                    email,
                    password,
                })

                this.token = response.data.token
                this.user = response.data.user

                if (this.token) {
                    localStorage.setItem('token', this.token)
                }

                api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

                // Restore wallet address if the user already has one saved in DB
                if (this.user?.wallet_address) {
                    this.walletAddress = this.user.wallet_address
                    localStorage.setItem('walletAddress', this.walletAddress as string)
                }

            } catch (error) {
                console.error('Login failed:', error)
                throw error
            }
        },

        async logout() {
            try {
                await api.post('/auth/logout')
            } catch (error) {
                console.warn('Logout API failed, clearing locally')
            }

            this.user = null
            this.token = null
            this.walletAddress = null

            localStorage.removeItem('token')
            localStorage.removeItem('walletAddress')
            delete api.defaults.headers.common['Authorization']
        },

        async fetchUser() {
            try {
                if (!this.token) return

                api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

                const response = await api.get('/auth/user')

                this.user = response.data.user

                // Sync wallet address from DB if available
                if (this.user?.wallet_address && !this.walletAddress) {
                    this.walletAddress = this.user.wallet_address
                    localStorage.setItem('walletAddress', this.walletAddress as string)
                }
            } catch (error) {
                console.error('Fetch user failed:', error)

                this.logout()
            }
        },

        /**
         * Save the MetaMask wallet address to the backend and local state.
         */
        async saveWalletAddress(address: string) {
            try {
                await api.post('/auth/wallet', { wallet_address: address })
                this.walletAddress = address
                localStorage.setItem('walletAddress', address)
            } catch (error: any) {
                console.error('Failed to save wallet address:', error)
                throw error
            }
        },
    },
})