<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const auth = useAuth()

const errorMessage = ref('')
const isLoggingOut = ref(false)
const isLoadingPosts = ref(false)
const postsError = ref('')
const posts = ref([])

const userFullName = computed(() => {
  return [auth.user.value?.name, auth.user.value?.surname].filter(Boolean).join(' ') || '-'
})

const canManagePosts = computed(() => {
  return ['admin', 'writer'].includes(auth.user.value?.role)
})

const isAdmin = computed(() => auth.user.value?.role === 'admin')

const managePostsLabel = computed(() => {
  return auth.user.value?.role === 'admin' ? 'Tüm Yazılar' : 'Yazılarım'
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

const handleLogout = async () => {
  errorMessage.value = ''
  isLoggingOut.value = true

  try {
    await auth.logout()
    router.push('/login')
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoggingOut.value = false
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
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
              Dashboard
            </p>
            <h1 class="mt-2 text-3xl font-bold text-zinc-900">
              Hoş geldin, {{ auth.user.value?.name || 'Kullanıcı' }}
            </h1>
          </div>

          <div class="flex flex-wrap gap-3">
            <AppButton
              v-if="canManagePosts"
              variant="secondary"
              :full-width="false"
              @click="router.push('/my-posts')"
            >
              {{ managePostsLabel }}
            </AppButton>

            <AppButton
              v-if="isAdmin"
              variant="secondary"
              :full-width="false"
              @click="router.push('/categories')"
            >
              Kategoriler
            </AppButton>

            <AppButton
              variant="secondary"
              :full-width="false"
              :loading="isLoggingOut"
              loading-text="Çıkış yapılıyor..."
              @click="handleLogout"
            >
              Çıkış Yap
            </AppButton>
          </div>
        </div>

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

        <div class="mt-6">
          <AppAlert :message="errorMessage" />
        </div>
      </section>

      <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
              Yayındaki Yazılar
            </p>
          </div>

          <AppButton
            variant="secondary"
            :full-width="false"
            :disabled="isLoadingPosts"
            @click="loadPosts"
          >
            Yenile
          </AppButton>
        </div>

        <div class="mt-6">
          <AppAlert :message="postsError" />
        </div>

        <div v-if="isLoadingPosts" class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <div
            v-for="item in 3"
            :key="item"
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50"
          >
            <div class="h-48 animate-pulse bg-zinc-200"></div>
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

        <div
          v-else-if="posts.length"
          class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
        >
          <article
            v-for="post in posts"
            :key="post.id"
            class="flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 transition hover:-translate-y-0.5 hover:border-zinc-300"
          >
            <div class="relative h-52 bg-zinc-200">
              <img
                v-if="post.cover_image_url"
                :src="post.cover_image_url"
                :alt="post.title"
                class="h-full w-full object-cover"
              />
              <div
                v-else
                class="flex h-full items-center justify-center bg-gradient-to-br from-zinc-200 to-zinc-300 text-sm font-medium text-zinc-500"
              >
                Kapak görseli yok
              </div>

              <div class="absolute left-4 top-4 flex items-start justify-between gap-3">
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                  {{ post.status === 'published' ? 'Yayında' : post.status }}
                </span>
              </div>
            </div>

            <div class="flex h-full flex-col p-5">
              <div class="flex items-center justify-between gap-3">
                <span class="text-xs text-zinc-500">
                  {{ formatDate(post.published_at) }}
                </span>
              </div>

              <h3 class="mt-4 text-xl font-semibold text-zinc-900">
                {{ post.title }}
              </h3>

              <p class="mt-3 text-sm leading-6 text-zinc-600">
                {{ excerpt(post.content) }}
              </p>

              <div class="mt-auto pt-6">
                <div class="flex items-center justify-between border-t border-zinc-200 pt-4">
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
                      Kategori
                    </p>
                    <p class="mt-1 text-sm font-medium text-zinc-900">
                      {{ post.categories?.length ? post.categories.length : 0 }}
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
            İlk published içerik eklendiğinde burada listelenecek.
          </p>
        </div>
      </section>
    </div>
  </div>
</template>