<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppNavbar from '../components/layout/AppNavbar.vue'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const route = useRoute()
const router = useRouter()
const auth = useAuth()

const post = ref(null)
const isLoading = ref(false)
const errorMessage = ref('')
const commentMessage = ref('')
const commentError = ref('')
const isSubmittingComment = ref(false)
const deletingCommentId = ref(null)
const commentContent = ref('')

const loadPost = async () => {
  errorMessage.value = ''
  isLoading.value = true

  try {
    const { data } = await api.get(`/posts/${route.params.post}`)
    post.value = data.data ?? null
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

const submitComment = async () => {
  commentError.value = ''
  commentMessage.value = ''
  isSubmittingComment.value = true

  try {
    const { data } = await api.post(`/posts/${route.params.post}/comments`, {
      content: commentContent.value,
    })

    commentContent.value = ''
    commentMessage.value = data.message || 'Yorumunuz gönderildi.'
    await loadPost()
  } catch (error) {
    commentError.value = auth.normalizeErrorMessage(error)
  } finally {
    isSubmittingComment.value = false
  }
}

const deleteComment = async comment => {
  const confirmed = window.confirm('Bu yorumu silmek istiyor musun?')

  if (!confirmed) {
    return
  }

  commentError.value = ''
  commentMessage.value = ''
  deletingCommentId.value = comment.id

  try {
    const { data } = await api.delete(`/comments/${comment.id}`)
    commentMessage.value = data.message || 'Yorum silindi.'
    await loadPost()
  } catch (error) {
    commentError.value = auth.normalizeErrorMessage(error)
  } finally {
    deletingCommentId.value = null
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
  loadPost()
})
</script>

<template>
  <div class="min-h-screen bg-zinc-100">
    <AppNavbar />

    <div class="px-4 py-10">
      <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex justify-between gap-4">
          <AppButton variant="secondary" :full-width="false" @click="router.push('/')">
            Dashboard'a Dön
          </AppButton>

          <AppButton variant="secondary" :full-width="false" :disabled="isLoading" @click="loadPost">
            Yenile
          </AppButton>
        </div>

        <AppAlert :message="errorMessage" />

        <section v-if="isLoading" class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
          <div class="h-80 animate-pulse rounded-3xl bg-zinc-200"></div>
          <div class="mt-6 h-8 w-1/2 animate-pulse rounded bg-zinc-200"></div>
          <div class="mt-4 space-y-3">
            <div class="h-4 w-full animate-pulse rounded bg-zinc-200"></div>
            <div class="h-4 w-5/6 animate-pulse rounded bg-zinc-200"></div>
            <div class="h-4 w-4/6 animate-pulse rounded bg-zinc-200"></div>
          </div>
        </section>

        <template v-else-if="post">
          <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-xl">
            <div class="h-80 overflow-hidden bg-zinc-200">
              <img
                v-if="post.cover_image_url"
                :src="post.cover_image_url"
                :alt="post.title"
                class="h-full w-full object-cover"
              />
              <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-zinc-200 to-zinc-300 text-lg font-medium text-zinc-500"
              >
                Kapak görseli yok
              </div>
            </div>

            <div class="p-8">
              <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                  Yayında
                </span>
                <span class="text-sm text-zinc-500">
                  {{ formatDate(post.published_at) }}
                </span>
              </div>

              <h1 class="mt-4 text-4xl font-bold tracking-tight text-zinc-900">
                {{ post.title }}
              </h1>

              <div class="mt-6 grid gap-4 rounded-2xl bg-zinc-50 p-5 md:grid-cols-3">
                <div>
                  <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">Yazar</p>
                  <p class="mt-2 text-sm font-medium text-zinc-900">
                    {{ [post.author?.name, post.author?.surname].filter(Boolean).join(' ') || '-' }}
                  </p>
                </div>

                <div>
                  <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">Yorum Sayısı</p>
                  <p class="mt-2 text-sm font-medium text-zinc-900">
                    {{ post.comments_count ?? 0 }}
                  </p>
                </div>

                <div>
                  <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">Kategoriler</p>
                  <div v-if="post.categories?.length" class="mt-2 flex flex-wrap gap-2">
                    <span
                      v-for="category in post.categories"
                      :key="category.id"
                      class="rounded-full bg-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700"
                    >
                      {{ category.name }}
                    </span>
                  </div>
                  <p v-else class="mt-2 text-sm font-medium text-zinc-900">-</p>
                </div>
              </div>

              <div class="mt-8 whitespace-pre-line text-zinc-700">
                {{ post.content }}
              </div>
            </div>
          </section>

          <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
            <div class="flex items-center justify-between gap-4">
              <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">Yorum Ekle</p>
                <h2 class="mt-2 text-2xl font-bold text-zinc-900">Görüşünü Yaz</h2>
              </div>
            </div>

            <div class="mt-6 space-y-3">
              <AppAlert :message="commentError" />
              <AppAlert :message="commentMessage" variant="success" />
            </div>

            <form class="mt-6 space-y-4" @submit.prevent="submitComment">
              <div>
                <label for="comment_content" class="mb-1 block text-sm font-medium text-zinc-700">
                  Yorum
                </label>
                <textarea
                  id="comment_content"
                  v-model="commentContent"
                  rows="5"
                  class="w-full rounded-2xl border border-zinc-300 px-4 py-3 outline-none transition focus:border-zinc-900"
                  placeholder="Bu yazı hakkındaki düşünceni yaz"
                ></textarea>
              </div>

              <div class="flex justify-end">
                <AppButton
                  type="submit"
                  :full-width="false"
                  :loading="isSubmittingComment"
                  loading-text="Gönderiliyor..."
                >
                  Yorumu Gönder
                </AppButton>
              </div>
            </form>
          </section>

          <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
            <div class="flex items-center justify-between gap-4">
              <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">Yorumlar</p>
                <h2 class="mt-2 text-2xl font-bold text-zinc-900">Yorumlar</h2>
              </div>

              <span class="rounded-full bg-zinc-100 px-3 py-1 text-sm font-semibold text-zinc-700">
                {{ post.comments_count ?? 0 }} yorum
              </span>
            </div>

            <div v-if="post.comments?.length" class="mt-8 space-y-4">
              <article
                v-for="comment in post.comments"
                :key="comment.id"
                class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5"
              >
                <div class="flex items-center justify-between gap-4">
                  <div>
                    <p class="font-semibold text-zinc-900">
                      {{ [comment.author?.name, comment.author?.surname].filter(Boolean).join(' ') || '-' }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-500">
                      {{ formatDate(comment.created_at) }}
                    </p>
                  </div>

                  <span
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    :class="comment.is_approved ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                  >
                    {{ comment.is_approved ? 'Onaylı' : 'Onay Bekliyor' }}
                  </span>
                </div>

                <p class="mt-4 whitespace-pre-line text-sm leading-6 text-zinc-700">
                  {{ comment.content }}
                </p>

                <div
                  v-if="!comment.is_approved && comment.author?.id === auth.user.value?.id"
                  class="mt-4 flex justify-end"
                >
                  <AppButton
                    :full-width="false"
                    :loading="deletingCommentId === comment.id"
                    loading-text="Siliniyor..."
                    @click="deleteComment(comment)"
                  >
                    Sil
                  </AppButton>
                </div>
              </article>
            </div>

            <div v-else class="mt-8 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center">
              <p class="text-lg font-semibold text-zinc-900">Henüz yorum yok</p>
              <p class="mt-2 text-sm text-zinc-500">İlk yorum burada listelenecek.</p>
            </div>
          </section>
        </template>
      </div>
    </div>
  </div>
</template>