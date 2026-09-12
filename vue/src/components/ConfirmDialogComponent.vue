<template>
  <Teleport to="body">
    <div v-if="store.isOpen" class="fixed inset-0 z-[60] flex items-center justify-center px-4">
      <!-- Overlay - klik di luar kotak = sama seperti klik Batal -->
      <div class="fixed inset-0 bg-black/40" @click="onCancel"></div>

      <div
        class="relative bg-white rounded-lg shadow-xl w-full max-w-sm p-6 animate-fade-in-confirm"
      >
        <h3 class="text-lg font-bold text-gray-800 mb-2">{{ store.title }}</h3>
        <p class="text-sm text-gray-600 mb-6 whitespace-pre-line">{{ store.message }}</p>

        <div class="flex justify-end gap-3">
          <ButtonComponent variant="secondary" @click="onCancel">
            {{ store.cancelText }}
          </ButtonComponent>
          <ButtonComponent :variant="store.variant" @click="onConfirm">
            {{ store.confirmText }}
          </ButtonComponent>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
// Instance TUNGGAL/global (dipasang sekali di App.vue) untuk modal konfirmasi Ya/Tidak modular -
// lihat stores/confirmDialog.js (state + Promise resolver) dan utils/confirmDialog.js (helper
// yang dipanggil dari halaman mana saja, gantinya window.confirm() bawaan browser). Teleport ke
// <body> supaya z-index-nya nggak kehalang parent manapun (mis. kalau dipanggil dari dalam modal
// lain seperti KategoriInstrumenModal.vue).
import { onMounted, onUnmounted } from 'vue'
import { useConfirmStore } from '@/stores/confirmDialog'
import ButtonComponent from './ButtonComponent.vue'

const store = useConfirmStore()

const onConfirm = () => store.resolve(true)
const onCancel = () => store.resolve(false)

// Esc = Batal, biar konsisten sama modal lain di sistem ini yang juga dengerin keydown global.
const handleKeyDown = (event) => {
  if (event.key === 'Escape' && store.isOpen) {
    onCancel()
  }
}
onMounted(() => window.addEventListener('keydown', handleKeyDown))
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown))
</script>

<style scoped>
@keyframes fadeInConfirm {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(4px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
.animate-fade-in-confirm {
  animation: fadeInConfirm 0.15s ease-out;
}

@media (prefers-reduced-motion: reduce) {
  .animate-fade-in-confirm {
    animation: none;
  }
}
</style>
