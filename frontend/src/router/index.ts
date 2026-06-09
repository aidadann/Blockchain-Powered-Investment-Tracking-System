import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import ResetPasswordView from '../views/ResetPasswordView.vue'
import InvestorDashboard from '../views/InvestorDashboard.vue'
import AuditorDashboard from '../views/AuditorDashboard.vue'
import AdminDashboard from '../views/AdminDashboard.vue'
import BlockchainExplorer from '../views/BlockchainExplorer.vue'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginView
  },

  {
    path: '/register',
    name: 'Register',
    component: RegisterView
  },

  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPasswordView
  },

  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPasswordView
  },

  {
    path: '/investor',
    name: 'InvestorDashboard',
    component: InvestorDashboard,
    meta: { requiresAuth: true, role: 'investor' }
  },

  {
    path: '/admin',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, role: 'admin' }
  },

  {
    path: '/auditor',
    name: 'AuditorDashboard',
    component: AuditorDashboard,
    meta: { requiresAuth: true, role: 'auditor' }
  },

  {
    path: '/explorer',
    name: 'BlockchainExplorer',
    component: BlockchainExplorer,
    meta: { requiresAuth: true }
  },

  {
    path: '/',
    redirect: '/login'
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth) {
    if (!auth.isAuthenticated) {
      return next('/login')
    }
    if (!auth.user) {
      await auth.fetchUser()
    }
    if (!auth.user || (to.meta.role && auth.userRole?.toLowerCase() !== to.meta.role)) {
      return next('/login')
    }
  }
  if (to.path === '/login' && auth.isAuthenticated) {
    if (auth.user) {
      const role = auth.userRole?.toLowerCase()
      return next(`/${role}`)
    }
  }
  next()
})

export default router
