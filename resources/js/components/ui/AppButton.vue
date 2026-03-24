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
    default: 'Yükleniyor...'
  },
  fullWidth: {
    type: Boolean,
    default: true
  },
  variant: {
    type: String,
    default: 'primary'
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
    class="rounded-xl px-4 py-3 font-medium transition disabled:cursor-not-allowed disabled:opacity-60"
    :class="{
      'w-full': fullWidth,
      'bg-zinc-900 text-white hover:bg-zinc-800': variant === 'primary',
      'border border-zinc-300 bg-white text-zinc-800 hover:bg-zinc-50': variant === 'secondary'
    }"
  >
    {{ label }}
  </button>
</template>
