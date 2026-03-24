<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { defineForm, field, isValidForm, toObject } from 'vue-yup-form'
import { string } from 'yup'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import AppInput from '../components/ui/AppInput.vue'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const auth = useAuth()

const errorMessage = ref('')
const isLoading = ref(false)
const submitAttempted = ref(false)

const form = defineForm({
  login: field(
    '',
    string()
      .required('E-posta veya telefon numarası zorunludur.')
      .min(3, 'E-posta veya telefon numarası en az 3 karakter olmalıdır.')
  ),
  password: field(
    '',
    string()
      .required('Şifre zorunludur.')
      .min(6, 'Şifre en az 6 karakter olmalıdır.')
  )
})

const canSubmit = computed(() => isValidForm(form) && !isLoading.value)

const handleLogin = async () => {
  submitAttempted.value = true
  errorMessage.value = ''

  if (!isValidForm(form)) {
    return
  }

  isLoading.value = true

  try {
    await auth.login(toObject(form))
    router.push('/')
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-zinc-100 px-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-8 shadow-xl">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-zinc-900">Giriş Yap</h1>
      </div>

      <form class="space-y-4" @submit.prevent="handleLogin">
        <AppAlert :message="errorMessage" />

        <AppInput
          id="login"
          v-model="form.login.$value"
          label="E-posta veya Telefon"
          placeholder="ahmet@gmail.com veya 5551112233"
          :error="submitAttempted ? form.login.$errorMessages[0] : ''"
        />

        <AppInput
          id="password"
          v-model="form.password.$value"
          label="Şifre"
          type="password"
          placeholder="Şifrenizi girin"
          :error="submitAttempted ? form.password.$errorMessages[0] : ''"
        />

        <AppButton
          type="submit"
          :disabled="!canSubmit"
          :loading="isLoading"
          loading-text="Giriş yapılıyor..."
        >
          Giriş Yap
        </AppButton>
      </form>

      <p class="mt-6 text-center text-sm text-zinc-600">
        Hesabın yok mu?
        <RouterLink to="/register" class="font-medium text-zinc-900 underline underline-offset-4">
          Kayıt Ol
        </RouterLink>
      </p>
    </div>
  </div>
</template>
