<script setup>
import { computed, onMounted, ref } from 'vue'
import AppNavbar from '../components/layout/AppNavbar.vue'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import AppRowActionButton from '../components/ui/AppRowActionButton.vue'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const auth = useAuth()

const comments = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const approvingId = ref(null)
const deletingId = ref(null)
const selectedFilter = ref('all')

const filterOptions = [
  { label: 'Tüm Yorumlar', value: 'all' },
  { label: 'Onay Bekleyenler', value: 'pending' },
  { label: 'Onaylı Yorumlar', value: 'approved' },
]

const pageTitle = computed(() => {
  return selectedFilter.value === 'pending' ? 'Onay Bekleyen Yorumlar' : 'Yorumlar'
})

const resetMessages = () => {
  errorMessage.value = ''
  successMessage.value = ''
}

const loadComments = async () => {
  resetMessages()
  isLoading.value = true

  try {
    const { data } = await api.get('/comments', {
      params: {
        approval_status: selectedFilter.value,
      },
    })

    comments.value = data.data ?? []
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

const setFilter = async value => {
  selectedFilter.value = value
  await loadComments()
}

const approveComment = async comment => {
  resetMessages()
  approvingId.value = comment.id

  try {
    await api.patch(`/comments/${comment.id}/approve`)
    successMessage.value = 'Yorum onaylandı.'
    await loadComments()
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    approvingId.value = null
  }
}

const deleteComment = async comment => {
  const confirmed = window.confirm('Bu yorumu silmek istiyor musun?')

  if (!confirmed) {
    return
  }

  resetMessages()
  deletingId.value = comment.id

  try {
    await api.delete(`/comments/${comment.id}`)
    successMessage.value = 'Yorum silindi.'
    await loadComments()
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    deletingId.value = null
  }
}

const formatDate = value => {
  if (!value) {
    return '-'
  }

  return new Intl.DateTimeFormat('tr-TR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value))
}

onMounted(() => {
  loadComments()
})
</script>

<template>
  <div class="min-h-screen bg-zinc-100">
    <AppNavbar />

    <div class="px-4 py-10">
      <div class="mx-auto max-w-6xl space-y-6">
        <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
                Yorum Yönetimi
              </p>
              <h1 class="mt-2 text-3xl font-bold text-zinc-900">
                {{ pageTitle }}
              </h1>
            </div>

            <AppButton
              variant="secondary"
              :full-width="false"
              :disabled="isLoading"
              @click="loadComments"
            >
              Yenile
            </AppButton>
          </div>

          <div class="mt-6 flex flex-wrap gap-3">
            <AppButton
              v-for="filter in filterOptions"
              :key="filter.value"
              :variant="selectedFilter === filter.value ? 'primary' : 'secondary'"
              :full-width="false"
              @click="setFilter(filter.value)"
            >
              {{ filter.label }}
            </AppButton>
          </div>

          <div class="mt-6 space-y-3">
            <AppAlert :message="errorMessage" />
            <AppAlert :message="successMessage" variant="success" />
          </div>

          <div class="mt-8 overflow-hidden rounded-2xl border border-zinc-200">
            <div class="grid grid-cols-12 gap-4 bg-zinc-50 px-5 py-4 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
              <div class="col-span-12 md:col-span-3">Yazan</div>
              <div class="col-span-12 md:col-span-4">Yorum</div>
              <div class="col-span-12 md:col-span-2">Yazı</div>
              <div class="col-span-6 md:col-span-1">Durum</div>
              <div class="col-span-6 md:col-span-2 md:text-right">İşlemler</div>
            </div>

            <div v-if="isLoading" class="space-y-3 bg-white p-5">
              <div v-for="item in 4" :key="item" class="h-24 animate-pulse rounded-2xl bg-zinc-100"></div>
            </div>

            <div v-else-if="comments.length" class="divide-y divide-zinc-200 bg-white">
              <div v-for="comment in comments" :key="comment.id" class="grid grid-cols-12 gap-4 px-5 py-5">
                <div class="col-span-12 md:col-span-3">
                  <p class="font-semibold text-zinc-900">
                    {{ [comment.author?.name, comment.author?.surname].filter(Boolean).join(' ') || '-' }}
                  </p>
                  <p class="mt-1 text-sm text-zinc-500">
                    {{ comment.author?.role || '-' }}
                  </p>
                  <p class="mt-2 text-xs text-zinc-400">
                    {{ formatDate(comment.created_at) }}
                  </p>
                </div>

                <div class="col-span-12 md:col-span-4">
                  <p class="text-sm leading-6 text-zinc-700">
                    {{ comment.content }}
                  </p>
                </div>

                <div class="col-span-12 md:col-span-2">
                  <p class="font-medium text-zinc-900">
                    {{ comment.post?.title || '-' }}
                  </p>
                  <p class="mt-1 text-sm text-zinc-500">
                    {{ comment.post?.slug || '-' }}
                  </p>
                </div>

                <div class="col-span-6 md:col-span-1">
                  <span
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                    :class="comment.is_approved ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                  >
                    {{ comment.is_approved ? 'Onaylı' : 'Bekliyor' }}
                  </span>
                </div>

                <div class="col-span-6 flex gap-2 md:col-span-2 md:justify-end">
                  <AppRowActionButton
                    v-if="!comment.is_approved"
                    :loading="approvingId === comment.id"
                    loading-text="Onaylanıyor..."
                    @click="approveComment(comment)"
                  >
                    Onayla
                  </AppRowActionButton>

                  <AppRowActionButton
                    variant="danger"
                    :loading="deletingId === comment.id"
                    loading-text="Siliniyor..."
                    @click="deleteComment(comment)"
                  >
                    Sil
                  </AppRowActionButton>
                </div>
              </div>
            </div>

            <div v-else class="bg-white px-6 py-14 text-center">
              <p class="text-lg font-semibold text-zinc-900">Henüz yorum yok</p>
              <p class="mt-2 text-sm text-zinc-500">Seçtiğin filtreye uygun yorum bulunduğunda burada listelenecek.</p>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>
