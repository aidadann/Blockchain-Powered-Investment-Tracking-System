import { defineStore } from 'pinia'
import api from '../api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as any | null,
        token: localStorage.getItem('token') as string | null,
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

            localStorage.removeItem('token')
            delete api.defaults.headers.common['Authorization']
        },

        async fetchUser() {
            try {
                if (!this.token) return

                api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

                const response = await api.get('/auth/user')

                this.user = response.data.user
            } catch (error) {
                console.error('Fetch user failed:', error)

                this.logout()
            }
        },
    },
})