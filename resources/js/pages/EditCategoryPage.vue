<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import AppInput from '../components/ui/AppInput.vue'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const route = useRoute()
const router = useRouter()
const auth = useAuth()

const errorMessage = ref('')
const isLoading = ref(false)
const isSaving = ref(false)
const categories = ref([])

const form = reactive({
  name: '',
})

const loadCategory = async () => {
  errorMessage.value = ''
  isLoading.value = true

  try {
    const { data } = await api.get('/categories')
    categories.value = data.data ?? []

    const currentCategory = categories.value.find(item => String(item.id) === String(route.params.category))

    if (!currentCategory) {
      throw new Error('Kategori bulunamadı.')
    }

    form.name = currentCategory.name
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

const saveCategory = async () => {
  errorMessage.value = ''
  isSaving.value = true

  try {
    await api.put(`/categories/${route.params.category}`, {
      name: form.name,
    })

    router.push('/categories')
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  loadCategory()
})
</script>

<template>
  <div class="min-h-screen bg-zinc-100 px-4 py-10">
    <div class="mx-auto max-w-3xl space-y-6">
      <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
              Düzenleme
            </p>
            <h1 class="mt-2 text-3xl font-bold text-zinc-900">
              Kategoriyi Düzenle
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

        <div v-if="isLoading" class="mt-8 space-y-3">
          <div class="h-14 animate-pulse rounded-2xl bg-zinc-100"></div>
        </div>

        <form v-else class="mt-8 space-y-5" @submit.prevent="saveCategory">
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