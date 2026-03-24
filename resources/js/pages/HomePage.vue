<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const auth = useAuth()

const errorMessage = ref('')
const isLoggingOut = ref(false)

const handleLogout = async () => {
  errorMessage.value = ''
  isLoggingOut.value = true

  try {
    await auth.logout()
    router.push('/login')
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoggingOut.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-zinc-100 px-4 py-10">
    <div class="mx-auto max-w-3xl rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
            Dashboard
          </p>
          <h1 class="mt-2 text-3xl font-bold text-zinc-900">
            Hoş geldin, {{ auth.user.value?.name || 'Kullanıcı' }}
          </h1>
        </div>

        <AppButton
          variant="secondary"
          :full-width="false"
          :loading="isLoggingOut"
          loading-text="Çıkış yapılıyor..."
          @click="handleLogout"
        >
          Çıkış Yap
        </AppButton>
      </div>

      <div class="mt-8 grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl bg-zinc-50 p-5">
          <p class="text-sm text-zinc-500">Ad Soyad</p>
          <p class="mt-1 text-lg font-semibold text-zinc-900">
            {{ [auth.user.value?.name, auth.user.value?.surname].filter(Boolean).join(' ') || '-' }}
          </p>
        </div>

        <div class="rounded-2xl bg-zinc-50 p-5">
          <p class="text-sm text-zinc-500">E-posta</p>
          <p class="mt-1 text-lg font-semibold text-zinc-900">
            {{ auth.user.value?.email || '-' }}
          </p>
        </div>
      </div>

      <div class="mt-6">
        <AppAlert :message="errorMessage" />
      </div>
    </div>
  </div>
</template>
