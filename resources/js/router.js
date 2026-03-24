import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from './composables/useAuth'
import CreateCategoryPage from './pages/CreateCategoryPage.vue'
import CreatePostPage from './pages/CreatePostPage.vue'
import EditCategoryPage from './pages/EditCategoryPage.vue'
import EditPostPage from './pages/EditPostPage.vue'
import HomePage from './pages/HomePage.vue'
import Login from './pages/Login.vue'
import ManageCategoriesPage from './pages/ManageCategoriesPage.vue'
import ManagePostsPage from './pages/ManagePostsPage.vue'
import Register from './pages/Register.vue'

const routes = [
  { path: '/', component: HomePage, meta: { requiresAuth: true } },
  { path: '/my-posts', component: ManagePostsPage, meta: { requiresAuth: true } },
  { path: '/my-posts/create', component: CreatePostPage, meta: { requiresAuth: true } },
  { path: '/my-posts/:post/edit', component: EditPostPage, meta: { requiresAuth: true } },
  { path: '/categories', component: ManageCategoriesPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/categories/create', component: CreateCategoryPage, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/categories/:category/edit', component: EditCategoryPage, meta: { requiresAuth: true, requiresAdmin: true } },
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

  if (to.meta.requiresAdmin && auth.user.value?.role !== 'admin') {
    return '/'
  }
})

export default router