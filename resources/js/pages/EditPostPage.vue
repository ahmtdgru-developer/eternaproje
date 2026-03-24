<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
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
const post = ref(null)
const coverImageFile = ref(null)
const coverImagePreview = ref('')

const form = reactive({
  title: '',
  content: '',
  published_at: '',
  status: 'draft',
})

const isAdmin = computed(() => auth.user.value?.role === 'admin')
const isWriterEditingPublished = computed(() => {
  return !isAdmin.value && post.value?.status === 'published'
})
const pageTitle = computed(() => isAdmin.value ? 'Yazıyı Düzenle' : 'Yazımı Düzenle')

const toDateTimeLocal = value => {
  if (!value) {
    return ''
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return ''
  }

  const offset = date.getTimezoneOffset()
  const localDate = new Date(date.getTime() - offset * 60 * 1000)

  return localDate.toISOString().slice(0, 16)
}

const handleCoverImageChange = event => {
  const file = event.target.files?.[0] ?? null
  coverImageFile.value = file
  coverImagePreview.value = file ? URL.createObjectURL(file) : post.value?.cover_image_url || ''
}

const loadPost = async () => {
  errorMessage.value = ''
  isLoading.value = true

  try {
    const { data } = await api.get(`/my-posts/${route.params.post}`)
    post.value = data.data ?? data
    form.title = post.value?.title ?? ''
    form.content = post.value?.content ?? ''
    form.published_at = toDateTimeLocal(post.value?.published_at)
    form.status = post.value?.status ?? 'draft'
    coverImagePreview.value = post.value?.cover_image_url || ''
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

const savePost = async () => {
  errorMessage.value = ''

  if (isWriterEditingPublished.value) {
    const confirmed = window.confirm('Devam ederseniz bu yazı taslak durumuna alınacaktır. Devam etmek istiyor musunuz?')

    if (!confirmed) {
      return
    }
  }

  isSaving.value = true

  const payload = new FormData()
  payload.append('_method', 'PUT')
  payload.append('title', form.title)
  payload.append('content', form.content)

  if (coverImageFile.value) {
    payload.append('cover_image', coverImageFile.value)
  }

  if (isAdmin.value) {
    payload.append('status', form.status)

    if (form.published_at) {
      payload.append('published_at', form.published_at)
    }
  }

  try {
    await api.post(`/posts/${route.params.post}`, payload)
    router.push('/my-posts')
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  loadPost()
})
</script>

<template>
  <div class="min-h-screen bg-zinc-100 px-4 py-10">
    <div class="mx-auto max-w-4xl space-y-6">
      <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
              Düzenleme
            </p>
            <h1 class="mt-2 text-3xl font-bold text-zinc-900">
              {{ pageTitle }}
            </h1>
            <p class="mt-3 text-sm leading-6 text-zinc-600">
              Başlık, içerik ve kapak görseli bilgilerini buradan güncelleyebilirsin.
            </p>
          </div>

          <AppButton
            variant="secondary"
            :full-width="false"
            @click="router.push('/my-posts')"
          >
            Listeye Dön
          </AppButton>
        </div>

        <div class="mt-6 space-y-3">
          <AppAlert :message="errorMessage" />

          <AppAlert
            v-if="isWriterEditingPublished"
            message="Bu yazı şu an yayında. Kaydedersen yazı otomatik olarak taslak durumuna alınır."
          />
        </div>

        <div v-if="isLoading" class="mt-8 space-y-3">
          <div class="h-14 animate-pulse rounded-2xl bg-zinc-100"></div>
          <div class="h-14 animate-pulse rounded-2xl bg-zinc-100"></div>
          <div class="h-40 animate-pulse rounded-2xl bg-zinc-100"></div>
        </div>

        <form v-else class="mt-8 space-y-5" @submit.prevent="savePost">
          <div class="grid gap-5 md:grid-cols-2">
            <AppInput
              id="title"
              v-model="form.title"
              label="Başlık"
              placeholder="Yazı başlığını gir"
            />

            <div>
              <label for="cover_image" class="mb-1 block text-sm font-medium text-zinc-700">
                Kapak Görseli
              </label>
              <input
                id="cover_image"
                type="file"
                accept="image/png,image/jpeg,image/jpg,image/webp"
                class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm outline-none transition file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-zinc-800"
                @change="handleCoverImageChange"
              />
              <p class="mt-2 text-xs text-zinc-500">
                Yeni bir dosya seçersen mevcut kapak görseli değişir.
              </p>
            </div>

            <template v-if="isAdmin">
              <AppInput
                id="published_at"
                v-model="form.published_at"
                label="Yayın Tarihi"
                type="datetime-local"
              />

              <div>
                <label for="status" class="mb-1 block text-sm font-medium text-zinc-700">
                  Durum
                </label>
                <select
                  id="status"
                  v-model="form.status"
                  class="w-full rounded-xl border border-zinc-300 px-4 py-3 outline-none transition focus:border-zinc-900"
                >
                  <option value="draft">Taslak</option>
                  <option value="published">Yayında</option>
                </select>
              </div>
            </template>
          </div>

          <div v-if="coverImagePreview" class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 p-3">
            <img :src="coverImagePreview" alt="Kapak görseli önizleme" class="h-56 w-full rounded-xl object-cover" />
          </div>

          <div>
            <label for="content" class="mb-1 block text-sm font-medium text-zinc-700">
              İçerik
            </label>
            <textarea
              id="content"
              v-model="form.content"
              rows="10"
              class="w-full rounded-2xl border border-zinc-300 px-4 py-3 outline-none transition focus:border-zinc-900"
              placeholder="Yazı içeriğini gir"
            ></textarea>
          </div>

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