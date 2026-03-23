<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { defineForm, field, isValidForm, toObject } from 'vue-yup-form'
import { string } from 'yup'
import api from '../api'

const router = useRouter()

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
    const payload = toObject(form)
    const res = await api.post('/login', payload)

    localStorage.setItem('token', res.data.data.token)
    router.push('/')
  } catch (e) {
    errorMessage.value = 'Giriş başarısız. Bilgilerini kontrol et.'
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
        <p
          v-if="errorMessage"
          class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
          {{ errorMessage }}
        </p>

        <div>
          <label for="login" class="mb-1 block text-sm font-medium text-zinc-700">
            E-posta veya Telefon
          </label>
          <input
            id="login"
            v-model="form.login.$value"
            type="text"
            placeholder="ornek@mail.com veya 5551112233"
            class="w-full rounded-xl border border-zinc-300 px-4 py-3 outline-none transition focus:border-zinc-900"
            :class="{
              'border-red-300 focus:border-red-500': submitAttempted && form.login.$errorMessages.length
            }"
          />
          <p
            v-if="submitAttempted && form.login.$errorMessages.length"
            class="mt-2 text-sm text-red-600"
          >
            {{ form.login.$errorMessages[0] }}
          </p>
        </div>

        <div>
          <label for="password" class="mb-1 block text-sm font-medium text-zinc-700">
            Şifre
          </label>
          <input
            id="password"
            v-model="form.password.$value"
            type="password"
            placeholder="Şifrenizi girin"
            class="w-full rounded-xl border border-zinc-300 px-4 py-3 outline-none transition focus:border-zinc-900"
            :class="{
              'border-red-300 focus:border-red-500': submitAttempted && form.password.$errorMessages.length
            }"
          />
          <p
            v-if="submitAttempted && form.password.$errorMessages.length"
            class="mt-2 text-sm text-red-600"
          >
            {{ form.password.$errorMessages[0] }}
          </p>
        </div>

        <button
          type="submit"
          :disabled="!canSubmit"
          class="w-full rounded-xl bg-zinc-900 px-4 py-3 font-medium text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ isLoading ? 'Giriş yapılıyor...' : 'Giriş Yap' }}
        </button>
      </form>
    </div>
  </div>
</template>
