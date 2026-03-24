<script setup>
defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  modelValue: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div>
    <label :for="id" class="mb-1 block text-sm font-medium text-zinc-700">
      {{ label }}
    </label>
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      class="w-full rounded-xl border px-4 py-3 outline-none transition"
      :class="{
        'border-zinc-300 focus:border-zinc-900': !error,
        'border-red-300 focus:border-red-500': error
      }"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>
