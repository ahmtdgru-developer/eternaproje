<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppNavbar from '../components/layout/AppNavbar.vue'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const auth = useAuth()

const isLoadingPosts = ref(false)
const isLoadingFeatured = ref(false)
const postsError = ref('')
const featuredError = ref('')
const posts = ref([])
const featuredPosts = ref([])

const userFullName = computed(() => {
  return [auth.user.value?.name, auth.user.value?.surname].filter(Boolean).join(' ') || '-'
})

const loadPosts = async () => {
  postsError.value = ''
  isLoadingPosts.value = true

  try {
    const { data } = await api.get('/posts')
    posts.value = data.data ?? []
  } catch (error) {
    postsError.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoadingPosts.value = false
  }
}

const loadFeaturedPosts = async () => {
  featuredError.value = ''
  isLoadingFeatured.value = true

  try {
    const { data } = await api.get('/featured-posts')
    featuredPosts.value = data.data ?? []
  } catch (error) {
    featuredError.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoadingFeatured.value = false
  }
}

const reloadDashboard = async () => {
  await Promise.all([loadFeaturedPosts(), loadPosts()])
}

const openPostDetail = post => {
  router.push(`/posts/${post.id}`)
}

const formatDate = value => {
  if (!value) {
    return 'Tarih yok'
  }

  return new Intl.DateTimeFormat('tr-TR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(new Date(value))
}

const excerpt = content => {
  if (!content) {
    return ''
  }

  return content.length > 140 ? `${content.slice(0, 140)}...` : content
}

const statusLabel = status => {
  return status === 'published' ? 'Yayında' : 'Taslak'
}

onMounted(() => {
  reloadDashboard()
})
</script>

<template>
  <div class="min-h-screen bg-zinc-100">
    <AppNavbar />

    <div class="px-4 py-10">
      <div class="mx-auto max-w-6xl space-y-6">
        <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
          <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
            Dashboard
          </p>
          <h1 class="mt-2 text-3xl font-bold text-zinc-900">
            Hoş geldin, {{ auth.user.value?.name || 'Kullanıcı' }}
          </h1>

          <div class="mt-8 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl bg-zinc-50 p-5">
              <p class="text-sm text-zinc-500">Ad Soyad</p>
              <p class="mt-1 text-lg font-semibold text-zinc-900">
                {{ userFullName }}
              </p>
            </div>

            <div class="rounded-2xl bg-zinc-50 p-5">
              <p class="text-sm text-zinc-500">E-posta</p>
              <p class="mt-1 text-lg font-semibold text-zinc-900">
                {{ auth.user.value?.email || '-' }}
              </p>
            </div>
          </div>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
                Öne Çıkan Yazılar
              </p>
              <h2 class="mt-2 text-2xl font-bold text-zinc-900">
                En yüksek skorlu içerikler
              </h2>
            </div>

            <AppButton
              variant="secondary"
              :full-width="false"
              :disabled="isLoadingPosts || isLoadingFeatured"
              @click="reloadDashboard"
            >
              Yenile
            </AppButton>
          </div>

          <div class="mt-6">
            <AppAlert :message="featuredError" />
          </div>

          <div v-if="isLoadingFeatured" class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
              v-for="item in 3"
              :key="`featured-skeleton-${item}`"
              class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50"
            >
              <div class="h-52 animate-pulse bg-zinc-200"></div>
              <div class="p-5">
                <div class="h-4 w-24 animate-pulse rounded bg-zinc-200"></div>
                <div class="mt-4 h-6 w-3/4 animate-pulse rounded bg-zinc-200"></div>
                <div class="mt-3 space-y-2">
                  <div class="h-4 w-full animate-pulse rounded bg-zinc-200"></div>
                  <div class="h-4 w-5/6 animate-pulse rounded bg-zinc-200"></div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="featuredPosts.length" class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article
              v-for="post in featuredPosts"
              :key="`featured-${post.id}`"
              class="flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 transition hover:-translate-y-0.5 hover:border-zinc-300"
            >
              <div class="relative h-52 overflow-hidden bg-zinc-200">
                <img
                  v-if="post.cover_image_url"
                  :src="post.cover_image_url"
                  :alt="post.title"
                  class="h-full w-full object-cover"
                />
                <div
                  v-else
                  class="flex h-full w-full items-center justify-center bg-gradient-to-br from-zinc-200 to-zinc-300 text-sm font-medium text-zinc-500"
                >
                  Kapak görseli yok
                </div>

                <div class="absolute left-4 top-4 flex flex-wrap items-center gap-2">
                  <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ statusLabel(post.status) }}
                  </span>
                  <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-zinc-700">
                    Skor: {{ post.score ?? 0 }}
                  </span>
                </div>
              </div>

              <div class="flex h-full flex-col p-5">
                <span class="text-xs text-zinc-500">
                  {{ formatDate(post.published_at) }}
                </span>

                <h3 class="mt-4 text-xl font-semibold text-zinc-900">
                  {{ post.title }}
                </h3>

                <p class="mt-3 text-sm leading-6 text-zinc-600">
                  {{ excerpt(post.content) }}
                </p>

                <div class="mt-6 flex items-center gap-3">
                  <AppButton variant="secondary" :full-width="false" @click="openPostDetail(post)">
                    Detaya Git
                  </AppButton>
                  <span class="text-sm font-medium text-zinc-500">
                    {{ post.comments_count ?? 0 }} yorum
                  </span>
                </div>

                <div class="mt-auto pt-6">
                  <div class="flex items-start justify-between gap-6 border-t border-zinc-200 pt-4">
                    <div>
                      <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">
                        Yazar
                      </p>
                      <p class="mt-1 text-sm font-medium text-zinc-900">
                        {{ [post.author?.name, post.author?.surname].filter(Boolean).join(' ') || '-' }}
                      </p>
                    </div>

                    <div class="text-right">
                      <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">
                        Kategoriler
                      </p>
                      <div v-if="post.categories?.length" class="mt-1 space-y-1">
                        <p
                          v-for="category in post.categories"
                          :key="category.id"
                          class="text-sm font-medium text-zinc-900"
                        >
                          {{ category.name }}
                        </p>
                      </div>
                      <p v-else class="mt-1 text-sm font-medium text-zinc-900">
                        -
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          </div>

          <div
            v-else
            class="mt-8 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center"
          >
            <p class="text-lg font-semibold text-zinc-900">
              Henüz öne çıkan yazı yok
            </p>
            <p class="mt-2 text-sm text-zinc-500">
              Skorlama için yeterli yayın ve yorum verisi oluştuğunda burada listelenecek.
            </p>
          </div>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
              Yayındaki Yazılar
            </p>

            <AppButton
              variant="secondary"
              :full-width="false"
              :disabled="isLoadingPosts"
              @click="loadPosts"
            >
              Listeyi Yenile
            </AppButton>
          </div>

          <div class="mt-6">
            <AppAlert :message="postsError" />
          </div>

          <div v-if="isLoadingPosts" class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
              v-for="item in 3"
              :key="`post-skeleton-${item}`"
              class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50"
            >
              <div class="h-52 animate-pulse bg-zinc-200"></div>
              <div class="p-5">
                <div class="h-4 w-24 animate-pulse rounded bg-zinc-200"></div>
                <div class="mt-4 h-6 w-3/4 animate-pulse rounded bg-zinc-200"></div>
                <div class="mt-3 space-y-2">
                  <div class="h-4 w-full animate-pulse rounded bg-zinc-200"></div>
                  <div class="h-4 w-5/6 animate-pulse rounded bg-zinc-200"></div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="posts.length" class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article
              v-for="post in posts"
              :key="post.id"
              class="flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 transition hover:-translate-y-0.5 hover:border-zinc-300"
            >
              <div class="relative h-52 overflow-hidden bg-zinc-200">
                <img
                  v-if="post.cover_image_url"
                  :src="post.cover_image_url"
                  :alt="post.title"
                  class="h-full w-full object-cover"
                />
                <div
                  v-else
                  class="flex h-full w-full items-center justify-center bg-gradient-to-br from-zinc-200 to-zinc-300 text-sm font-medium text-zinc-500"
                >
                  Kapak görseli yok
                </div>

                <div class="absolute left-4 top-4 flex items-center gap-2">
                  <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ statusLabel(post.status) }}
                  </span>
                  <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-zinc-700">
                    {{ post.comments_count ?? 0 }} yorum
                  </span>
                </div>
              </div>

              <div class="flex h-full flex-col p-5">
                <span class="text-xs text-zinc-500">
                  {{ formatDate(post.published_at) }}
                </span>

                <h3 class="mt-4 text-xl font-semibold text-zinc-900">
                  {{ post.title }}
                </h3>

                <p class="mt-3 text-sm leading-6 text-zinc-600">
                  {{ excerpt(post.content) }}
                </p>

                <div class="mt-6">
                  <AppButton variant="secondary" :full-width="false" @click="openPostDetail(post)">
                    Detaya Git
                  </AppButton>
                </div>

                <div class="mt-auto pt-6">
                  <div class="flex items-start justify-between gap-6 border-t border-zinc-200 pt-4">
                    <div>
                      <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">
                        Yazar
                      </p>
                      <p class="mt-1 text-sm font-medium text-zinc-900">
                        {{ [post.author?.name, post.author?.surname].filter(Boolean).join(' ') || '-' }}
                      </p>
                    </div>

                    <div class="text-right">
                      <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">
                        Kategoriler
                      </p>
                      <div v-if="post.categories?.length" class="mt-1 space-y-1">
                        <p
                          v-for="category in post.categories"
                          :key="category.id"
                          class="text-sm font-medium text-zinc-900"
                        >
                          {{ category.name }}
                        </p>
                      </div>
                      <p v-else class="mt-1 text-sm font-medium text-zinc-900">
                        -
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          </div>

          <div
            v-else
            class="mt-8 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center"
          >
            <p class="text-lg font-semibold text-zinc-900">
              Henüz yayında yazı yok
            </p>
            <p class="mt-2 text-sm text-zinc-500">
              İlk içerik yayınlandığında burada listelenecek.
            </p>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>
