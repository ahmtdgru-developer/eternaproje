import { computed, reactive } from 'vue'
import { authService } from '../services/authService'

const state = reactive({
  user: null,
  token: localStorage.getItem('token'),
  initialized: false,
  initializing: false
})

const normalizeErrorMessage = error => {
  return (
    error?.response?.data?.message ||
    error?.response?.data?.error ||
    'Bir hata oluştu. Lütfen tekrar dene.'
  )
}

const fetchCurrentUser = async () => {
  if (!state.token) {
    state.user = null
    return null
  }

  const { data } = await authService.me()
  state.user = data

  return data
}

const initialize = async () => {
  if (state.initialized || state.initializing) {
    return
  }

  state.initializing = true

  try {
    await fetchCurrentUser()
  } catch (error) {
    localStorage.removeItem('token')
    state.token = null
    state.user = null
  } finally {
    state.initializing = false
    state.initialized = true
  }
}

const login = async credentials => {
  const { data } = await authService.login(credentials)
  const token = data.data.token
  const user = data.data.user

  localStorage.setItem('token', token)
  state.token = token
  state.user = user
  state.initialized = true

  return user
}

const logout = async () => {
  try {
    if (state.token) {
      await authService.logout()
    }
  } finally {
    localStorage.removeItem('token')
    state.token = null
    state.user = null
    state.initialized = true
  }
}

export const useAuth = () => {
  return {
    user: computed(() => state.user),
    token: computed(() => state.token),
    isAuthenticated: computed(() => Boolean(state.token)),
    isInitialized: computed(() => state.initialized),
    initialize,
    fetchCurrentUser,
    login,
    logout,
    normalizeErrorMessage
  }
}
