<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppNavbar from '../components/layout/AppNavbar.vue'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const auth = useAuth()

const categories = ref([])
const isLoading = ref(false)
const deletingId = ref(null)
const errorMessage = ref('')
const successMessage = ref('')

const resetMessages = () => {
  errorMessage.value = ''
  successMessage.value = ''
}

const loadCategories = async () => {
  resetMessages()
  isLoading.value = true

  try {
    const { data } = await api.get('/categories')
    categories.value = data.data ?? []
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

const openEditPage = category => {
  router.push(`/categories/${category.id}/edit`)
}

const deleteCategory = async category => {
  const confirmed = window.confirm(`"${category.name}" kategorisini silmek istiyor musun?`)

  if (!confirmed) {
    return
  }

  resetMessages()
  deletingId.value = category.id

  try {
    await api.delete(`/categories/${category.id}`)
    successMessage.value = 'Kategori silindi.'
    await loadCategories()
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    deletingId.value = null
  }
}

onMounted(() => {
  loadCategories()
})
</script>

<template>
  <div class="min-h-screen bg-zinc-100">
    <AppNavbar />

    <div class="px-4 py-10">
      <div class="mx-auto max-w-5xl space-y-6">
        <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-end">
            <AppButton
              variant="secondary"
              :full-width="false"
              :disabled="isLoading"
              @click="loadCategories"
            >
              Yenile
            </AppButton>
          </div>

          <div class="mt-6 space-y-3">
            <AppAlert :message="errorMessage" />
            <AppAlert :message="successMessage" variant="success" />
          </div>

          <div class="mt-8 flex items-center justify-between gap-4">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
              Kategoriler
            </p>

            <AppButton
              variant="secondary"
              :full-width="false"
              @click="router.push('/categories/create')"
            >
              Kategori Ekle
            </AppButton>
          </div>

          <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200">
            <div class="grid grid-cols-12 gap-4 bg-zinc-50 px-5 py-4 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
              <div class="col-span-12 md:col-span-5">Kategori Adı</div>
              <div class="col-span-12 md:col-span-4">Slug</div>
              <div class="col-span-12 md:col-span-3 md:text-right">İşlemler</div>
            </div>

            <div v-if="isLoading" class="space-y-3 bg-white p-5">
              <div v-for="item in 4" :key="item" class="h-16 animate-pulse rounded-2xl bg-zinc-100"></div>
            </div>

            <div v-else-if="categories.length" class="divide-y divide-zinc-200 bg-white">
              <div v-for="category in categories" :key="category.id" class="grid grid-cols-12 gap-4 px-5 py-5">
                <div class="col-span-12 md:col-span-5">
                  <p class="font-semibold text-zinc-900">{{ category.name }}</p>
                </div>

                <div class="col-span-12 md:col-span-4 text-sm text-zinc-500">{{ category.slug }}</div>

                <div class="col-span-12 flex gap-3 md:col-span-3 md:justify-end">
                  <AppButton variant="secondary" :full-width="false" @click="openEditPage(category)">
                    Düzenle
                  </AppButton>

                  <AppButton
                    :full-width="false"
                    :loading="deletingId === category.id"
                    loading-text="Siliniyor..."
                    @click="deleteCategory(category)"
                  >
                    Sil
                  </AppButton>
                </div>
              </div>
            </div>

            <div v-else class="bg-white px-6 py-14 text-center">
              <p class="text-lg font-semibold text-zinc-900">Henüz kategori yok</p>
              <p class="mt-2 text-sm text-zinc-500">İlk kategori eklendiğinde burada listelenecek.</p>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>