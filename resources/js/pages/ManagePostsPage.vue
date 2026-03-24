<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const auth = useAuth()

const posts = ref([])
const isLoading = ref(false)
const deletingId = ref(null)
const errorMessage = ref('')
const successMessage = ref('')

const isAdmin = computed(() => auth.user.value?.role === 'admin')

const pageTitle = computed(() => {
  return isAdmin.value ? 'Tüm Yazılar' : 'Yazılarım'
})

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

const resetMessages = () => {
  errorMessage.value = ''
  successMessage.value = ''
}

const loadPosts = async () => {
  resetMessages()
  isLoading.value = true

  try {
    const { data } = await api.get('/my-posts')
    posts.value = data.data ?? []
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

const openEditPage = post => {
  router.push(`/my-posts/${post.id}/edit`)
}

const deletePost = async post => {
  const confirmed = window.confirm(`"${post.title}" başlıklı yazıyı silmek istiyor musun?`)

  if (!confirmed) {
    return
  }

  resetMessages()
  deletingId.value = post.id

  try {
    await api.delete(`/posts/${post.id}`)
    successMessage.value = 'Yazı silindi.'
    await loadPosts()
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    deletingId.value = null
  }
}

onMounted(() => {
  loadPosts()
})
</script>

<template>
  <div class="min-h-screen bg-zinc-100 px-4 py-10">
    <div class="mx-auto max-w-6xl space-y-6">
      <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div></div>

          <div class="flex flex-wrap gap-3">
            <AppButton
              variant="secondary"
              :full-width="false"
              @click="router.push('/')"
            >
              Dashboard
            </AppButton>

            <AppButton
              variant="secondary"
              :full-width="false"
              :disabled="isLoading"
              @click="loadPosts"
            >
              Yenile
            </AppButton>
          </div>
        </div>

        <div class="mt-6 space-y-3">
          <AppAlert :message="errorMessage" />
          <AppAlert :message="successMessage" variant="success" />
        </div>

        <div class="mt-8 flex items-center justify-between gap-4">
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
              {{ pageTitle }}
            </p>
          </div>

          <AppButton
            variant="secondary"
            :full-width="false"
            @click="router.push('/my-posts/create')"
          >
            Yeni Yazı Ekle
          </AppButton>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200">
          <div class="grid grid-cols-12 gap-4 bg-zinc-50 px-5 py-4 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
            <div class="col-span-12 md:col-span-5">Başlık</div>
            <div class="col-span-6 md:col-span-2">Durum</div>
            <div class="col-span-6 md:col-span-2">Yayın Tarihi</div>
            <div class="col-span-12 md:col-span-3 md:text-right">İşlemler</div>
          </div>

          <div v-if="isLoading" class="space-y-3 bg-white p-5">
            <div
              v-for="item in 4"
              :key="item"
              class="h-20 animate-pulse rounded-2xl bg-zinc-100"
            ></div>
          </div>

          <div v-else-if="posts.length" class="divide-y divide-zinc-200 bg-white">
            <div
              v-for="post in posts"
              :key="post.id"
              class="grid grid-cols-12 gap-4 px-5 py-5"
            >
              <div class="col-span-12 md:col-span-5">
                <div class="flex items-center gap-4">
                  <div class="h-16 w-16 overflow-hidden rounded-2xl bg-zinc-100">
                    <img
                      v-if="post.cover_image_url"
                      :src="post.cover_image_url"
                      :alt="post.title"
                      class="h-full w-full object-cover"
                    />
                    <div
                      v-else
                      class="flex h-full items-center justify-center bg-zinc-200 text-[10px] font-medium text-zinc-500"
                    >
                      Yok
                    </div>
                  </div>

                  <div>
                    <p class="font-semibold text-zinc-900">
                      {{ post.title }}
                    </p>
                    <p class="mt-1 text-sm text-zinc-500">
                      {{ post.slug }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-span-6 md:col-span-2">
                <span
                  class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                  :class="post.status === 'published'
                    ? 'bg-emerald-100 text-emerald-700'
                    : 'bg-amber-100 text-amber-700'"
                >
                  {{ post.status === 'published' ? 'Yayında' : 'Taslak' }}
                </span>
              </div>

              <div class="col-span-6 md:col-span-2 text-sm text-zinc-600">
                {{ formatDate(post.published_at) }}
              </div>

              <div class="col-span-12 flex gap-3 md:col-span-3 md:justify-end">
                <AppButton
                  variant="secondary"
                  :full-width="false"
                  @click="openEditPage(post)"
                >
                  Düzenle
                </AppButton>

                <AppButton
                  :full-width="false"
                  :loading="deletingId === post.id"
                  loading-text="Siliniyor..."
                  @click="deletePost(post)"
                >
                  Sil
                </AppButton>
              </div>
            </div>
          </div>

          <div
            v-else
            class="bg-white px-6 py-14 text-center"
          >
            <p class="text-lg font-semibold text-zinc-900">
              Henüz listelenecek yazı yok
            </p>
            <p class="mt-2 text-sm text-zinc-500">
              Yeni bir yazı eklediğinde burada gözükecek.
            </p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>