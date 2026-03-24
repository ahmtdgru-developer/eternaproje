import { createRouter, createWebHistory } from 'vue-router'
import HomePage from './pages/HomePage.vue'
import Login from './pages/Login.vue'
import Register from './pages/Register.vue'
import { useAuth } from './composables/useAuth'

const routes = [
  { path: '/', component: HomePage, meta: { requiresAuth: true } },
  { path: '/login', component: Login, meta: { guestOnly: true } },
  { path: '/register', component: Register, meta: { guestOnly: true } }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async to => {
  const auth = useAuth()

  await auth.initialize()

  if (to.meta.requiresAuth && !auth.isAuthenticated.value) {
    return '/login'
  }

  if (to.meta.guestOnly && auth.isAuthenticated.value) {
    return '/'
  }
})

export default router
