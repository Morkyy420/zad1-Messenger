import { createRouter, createWebHistory } from 'vue-router'
import Login from './components/Login.vue'
import Register from './components/Register.vue'
import Dashboard from './components/Dashboard.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard for authentication
router.beforeEach((to, from, next) => {
  const user = localStorage.getItem('user')
  const token = localStorage.getItem('token')
  const isAuthenticated = user && token

  // If trying to access protected route without BOTH user and token
  if (to.meta.requiresAuth && !isAuthenticated) {
    // Clear all localStorage to force fresh login
    localStorage.clear()
    next('/login')
  } else if ((to.path === '/login' || to.path === '/register') && isAuthenticated) {
    next('/dashboard')
  } else if ((to.path === '/login' || to.path === '/register') && user && !token) {
    // Has user but no token - clear and stay on login
    localStorage.clear()
    next()
  } else {
    next()
  }
})

export default router
