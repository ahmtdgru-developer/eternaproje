<script setup>
defineProps({
  message: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'error'
  }
})
</script>

<template>
  <p
    v-if="message"
    class="rounded-xl border px-4 py-3 text-sm"
    :class="{
      'border-red-200 bg-red-50 text-red-700': variant === 'error',
      'border-emerald-200 bg-emerald-50 text-emerald-700': variant === 'success'
    }"
  >
    {{ message }}
  </p>
</template>
