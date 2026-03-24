import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from './composables/useAuth'
import CreatePostPage from './pages/CreatePostPage.vue'
import EditPostPage from './pages/EditPostPage.vue'
import HomePage from './pages/HomePage.vue'
import Login from './pages/Login.vue'
import ManagePostsPage from './pages/ManagePostsPage.vue'
import Register from './pages/Register.vue'

const routes = [
  { path: '/', component: HomePage, meta: { requiresAuth: true } },
  { path: '/my-posts', component: ManagePostsPage, meta: { requiresAuth: true } },
  { path: '/my-posts/create', component: CreatePostPage, meta: { requiresAuth: true } },
  { path: '/my-posts/:post/edit', component: EditPostPage, meta: { requiresAuth: true } },
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