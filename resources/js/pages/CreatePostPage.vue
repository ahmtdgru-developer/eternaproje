<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import vSelect from 'vue-select'
import AppAlert from '../components/ui/AppAlert.vue'
import AppButton from '../components/ui/AppButton.vue'
import AppInput from '../components/ui/AppInput.vue'
import api from '../services/api'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const auth = useAuth()

const errorMessage = ref('')
const isSaving = ref(false)
const isLoadingCategories = ref(false)
const categories = ref([])
const selectedCategories = ref([])
const coverImageFile = ref(null)
const coverImagePreview = ref('')

const form = reactive({
  title: '',
  content: '',
  published_at: '',
  status: 'draft',
})

const isAdmin = computed(() => auth.user.value?.role === 'admin')
const pageTitle = computed(() => isAdmin.value ? 'Yeni Yazı Ekle' : 'Yeni Yazı Oluştur')

const handleCoverImageChange = event => {
  const file = event.target.files?.[0] ?? null
  coverImageFile.value = file
  coverImagePreview.value = file ? URL.createObjectURL(file) : ''
}

const loadCategories = async () => {
  isLoadingCategories.value = true

  try {
    const { data } = await api.get('/categories')
    categories.value = data.data ?? []
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isLoadingCategories.value = false
  }
}

const createPost = async () => {
  errorMessage.value = ''
  isSaving.value = true

  const payload = new FormData()
  payload.append('title', form.title)
  payload.append('content', form.content)
  payload.append('status', isAdmin.value ? form.status : 'draft')

  if (coverImageFile.value) {
    payload.append('cover_image', coverImageFile.value)
  }

  if (isAdmin.value && form.published_at) {
    payload.append('published_at', form.published_at)
  }

  payload.append('category_ids', JSON.stringify(selectedCategories.value.map(category => category.id)))

  try {
    await api.post('/posts', payload)
    router.push('/my-posts')
  } catch (error) {
    errorMessage.value = auth.normalizeErrorMessage(error)
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  loadCategories()
})
</script>

<template>
  <div class="min-h-screen bg-zinc-100 px-4 py-10">
    <div class="mx-auto max-w-4xl space-y-6">
      <section class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500">
              Oluşturma
            </p>
            <h1 class="mt-2 text-3xl font-bold text-zinc-900">
              {{ pageTitle }}
            </h1>
          </div>

          <AppButton
            variant="secondary"
            :full-width="false"
            @click="router.push('/my-posts')"
          >
            Listeye Dön
          </AppButton>
        </div>

        <div class="mt-6">
          <AppAlert :message="errorMessage" />
        </div>

        <form class="mt-8 space-y-5" @submit.prevent="createPost">
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
                JPG, JPEG, PNG veya WEBP. En fazla 2 MB.
              </p>
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                Kategoriler
              </label>
              <v-select
                v-model="selectedCategories"
                :options="categories"
                label="name"
                :multiple="true"
                :close-on-select="false"
                :searchable="true"
                :loading="isLoadingCategories"
                placeholder="Kategori ara ve seç"
              />
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