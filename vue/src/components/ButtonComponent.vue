<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="[
      'inline-flex items-center justify-center px-4 py-2 rounded-md font-medium text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
      variantClasses,
      disabled ? 'opacity-50 cursor-not-allowed' : ''
    ]"
    @click="$emit('click', $event)"
  >
    <!-- Slot ini berguna agar Anda bisa memasukkan Teks atau Icon ke dalam tombol -->
    <slot></slot>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'button' // Bisa 'button', 'submit', atau 'reset'
  },
  variant: {
    type: String,
    default: 'primary' // 'primary', 'secondary', 'danger'
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])

// Logika untuk menentukan warna berdasarkan props variant
const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-[#0F2A4A] text-white hover:bg-blue-900 focus:ring-[#0F2A4A]'
    case 'secondary':
      return 'bg-gray-200 text-gray-700 hover:bg-gray-300 border border-gray-300 focus:ring-gray-400'
    case 'danger':
      return 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500'
    default:
      return 'bg-[#0F2A4A] text-white hover:bg-blue-900'
  }
})
</script>