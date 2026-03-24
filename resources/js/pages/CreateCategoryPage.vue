<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import AppInput from '../components/ui/AppInput.vue'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const auth = useAuth()

const errorMessage = ref('')
const isSaving = ref(false)

const form = reactive({
  name: '',
})

const createCategory = async () => {
  errorMessage.value = ''
  isSaving.value = true

  try {
    await api.post('/categories', {
      name: form.name,
    })

    router.push('/categories')
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isSaving.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-zinc-100 px-4 py-10">
    <div class="mx-auto max-w-3xl space-y-6">
      <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
              Oluşturma
            </p>
            <h1 class="mt-2 text-3xl font-bold text-zinc-900">
              Yeni Kategori Ekle
            </h1>
          </div>

          <AppButton
            variant="secondary"
            :full-width="false"
            @click="router.push('/categories')"
          >
            Listeye Dön
          </AppButton>
        </div>

        <div class="mt-6">
          <AppAlert :message="errorMessage" />
        </div>

        <form class="mt-8 space-y-5" @submit.prevent="createCategory">
          <AppInput
            id="name"
            v-model="form.name"
            label="Kategori Adı"
            placeholder="Kategori adını gir"
          />

          <div class="flex justify-end">
            <AppButton
              type="submit"
              :full-width="false"
              :loading="isSaving"
              loading-text="Kaydediliyor..."
            >
              Kaydet
            </AppButton>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>