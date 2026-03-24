<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppButton from '../ui/AppButton.vue'
import { useAuth } from '../../composables/useAuth'

const router = useRouter()
const route = useRoute()
const auth = useAuth()

const isLoggingOut = ref(false)

const links = computed(() => {
  const items = [
    { label: 'Dashboard', path: '/' },
  ]

  if (['admin', 'writer'].includes(auth.user.value?.role)) {
    items.push({
      label: auth.user.value?.role === 'admin' ? 'Tüm Yazılar' : 'Yazılarım',
      path: '/my-posts',
    })
  }

  if (auth.user.value?.role === 'admin') {
    items.push({ label: 'Kategoriler', path: '/categories' })
  }

  return items
})

const handleLogout = async () => {
  isLoggingOut.value = true

  try {
    await auth.logout()
    router.push('/login')
  } finally {
    isLoggingOut.value = false
  }
}
</script>

<template>
  <header class="sticky top-0 z-20 border-b border-zinc-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4">
      <button
        type="button"
        class="text-left"
        @click="router.push('/')"
      >
        <p class="text-xs font-medium uppercase tracking-[0.3em] text-zinc-400">
          Eterna Blog
        </p>
        <p class="mt-1 text-lg font-semibold text-zinc-900">
          Yönetim Paneli
        </p>
      </button>

      <nav class="hidden items-center gap-2 md:flex">
        <button
          v-for="link in links"
          :key="link.path"
          type="button"
          class="rounded-xl px-4 py-2 text-sm font-medium transition"
          :class="route.path === link.path
            ? 'bg-zinc-900 text-white'
            : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900'"
          @click="router.push(link.path)"
        >
          {{ link.label }}
        </button>
      </nav>

      <div class="flex items-center gap-3">
        <div class="hidden text-right sm:block">
          <p class="text-sm font-medium text-zinc-900">
            {{ [auth.user.value?.name, auth.user.value?.surname].filter(Boolean).join(' ') || 'Kullanıcı' }}
          </p>
          <p class="text-xs text-zinc-500">
            {{ auth.user.value?.role || '-' }}
          </p>
        </div>

        <AppButton
          variant="secondary"
          :full-width="false"
          :loading="isLoggingOut"
          loading-text="Çıkış yapılıyor..."
          @click="handleLogout"
        >
          Çıkış
        </AppButton>
      </div>
    </div>
  </header>
</template>
