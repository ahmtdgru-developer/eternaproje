import { createRouter, createWebHistory } from 'vue-router'
import HomePage from './pages/HomePage.vue'
import Login from './pages/Login.vue'

const routes = [
  { path: '/', component: HomePage },
  { path: '/login', component: Login },
]

export default createRouter({
  history: createWebHistory(),
  routes
})