<script setup>
import { computed, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { defineForm, field, isValidForm, toObject } from 'vue-yup-form'
import { string } from 'yup'
import { mask as vMask } from 'vue-the-mask'
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
  name: field(
    '',
    string()
      .required('Ad zorunludur.')
      .min(2, 'Ad en az 2 karakter olmalıdır.')
  ),
  surname: field(
    '',
    string()
      .required('Soyadı zorunludur.')
      .min(2, 'Soyadı en az 2 karakter olmalıdır.')
  ),
  phone: field(
    '',
    string()
      .required('Telefon numarası zorunludur.')
      .matches(/^0\d{10}$/, 'Telefon numarası 0 ile başlayan 11 haneli olmalıdır.')
  ),
  email: field(
    '',
    string()
      .required('E-posta zorunludur.')
      .email('Geçerli bir e-posta adresi girin.')
  ),
  password: field(
    '',
    string()
      .required('Şifre zorunludur.')
      .min(6, 'Şifre en az 6 karakter olmalıdır.')
  )
})

const canSubmit = computed(() => isValidForm(form) && !isLoading.value)

const handleRegister = async () => {
  submitAttempted.value = true
  errorMessage.value = ''

  if (!isValidForm(form)) {
    return
  }

  isLoading.value = true

  try {
    await auth.register(toObject(form))
    router.push('/')
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-zinc-100 px-4 py-10">
    <div class="w-full max-w-2xl rounded-2xl border border-zinc-200 bg-white p-8 shadow-xl">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-zinc-900">Kayıt Ol</h1>
        <p class="mt-2 text-sm text-zinc-500">
          Blog sistemine erişmek için hesabını oluştur.
        </p>
      </div>

      <form class="space-y-4" @submit.prevent="handleRegister">
        <AppAlert :message="errorMessage" />

        <div class="grid gap-4 md:grid-cols-2">
          <AppInput
            id="name"
            v-model="form.name.$value"
            label="Ad"
            placeholder="Ahmet"
            :error="submitAttempted ? form.name.$errorMessages[0] : ''"
          />

          <AppInput
            id="surname"
            v-model="form.surname.$value"
            label="Soyadı"
            placeholder="Yılmaz"
            :error="submitAttempted ? form.surname.$errorMessages[0] : ''"
          />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label for="phone" class="mb-1 block text-sm font-medium text-zinc-700">
              Telefon Numarası
            </label>
            <input
              id="phone"
              v-model="form.phone.$value"
              v-mask="'0##########'"
              type="tel"
              placeholder="05551112233"
              class="w-full rounded-xl border px-4 py-3 outline-none transition"
              :class="{
                'border-zinc-300 focus:border-zinc-900': !(submitAttempted && form.phone.$errorMessages.length),
                'border-red-300 focus:border-red-500': submitAttempted && form.phone.$errorMessages.length
              }"
            />
            <p v-if="submitAttempted && form.phone.$errorMessages.length" class="mt-2 text-sm text-red-600">
              {{ form.phone.$errorMessages[0] }}
            </p>
          </div>

          <AppInput
            id="email"
            v-model="form.email.$value"
            label="E-posta"
            type="email"
            placeholder="ahmet@gmail.com"
            :error="submitAttempted ? form.email.$errorMessages[0] : ''"
          />
        </div>

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
          loading-text="Kayıt oluşturuluyor..."
        >
          Kayıt Ol
        </AppButton>
      </form>

      <p class="mt-6 text-center text-sm text-zinc-600">
        Zaten hesabın var mı?
        <RouterLink to="/login" class="font-medium text-zinc-900 underline underline-offset-4">
          Giriş Yap
        </RouterLink>
      </p>
    </div>
  </div>
</template>
