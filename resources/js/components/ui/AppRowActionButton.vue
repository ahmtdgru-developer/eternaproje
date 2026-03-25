<script setup>
import { computed, useSlots } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'button'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'İşleniyor...'
  },
  variant: {
    type: String,
    default: 'neutral'
  }
})

const slots = useSlots()

const label = computed(() => {
  if (props.loading) {
    return props.loadingText
  }

  return slots.default?.()[0]?.children || ''
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex h-11 min-w-[92px] self-center items-center justify-center rounded-full border px-3.5 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60"
    :class="{
      'border-zinc-300 bg-white text-zinc-700 hover:border-zinc-400 hover:bg-zinc-50': variant === 'neutral',
      'border-emerald-200 bg-emerald-50 text-emerald-700 hover:border-emerald-300 hover:bg-emerald-100': variant === 'success',
      'border-rose-200 bg-rose-50 text-rose-700 hover:border-rose-300 hover:bg-rose-100': variant === 'danger'
    }"
  >
    {{ label }}
  </button>
</template>