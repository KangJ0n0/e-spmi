<template>
  <Transition name="fade">
    <div v-if="isLoading" class="top-loading-bar" role="status" aria-label="Memuat...">
      <div class="top-loading-bar__track"></div>
    </div>
  </Transition>
</template>

<script setup>
defineOptions({ name: 'TopLoadingBar' })

import { computed } from 'vue'
import { useStore } from '@/stores'

// Global loading indicator dipakai di SELURUH sistem (Admin/Auditor/Auditee/Guest) - state-nya
// diambil dari Pinia store yang sudah ada (`store.loader`), diaktifkan lewat interceptor axios
// di `src/axios.js` (lihat komentar di sana). Sebelumnya store ini sudah ada tapi `setLoader`
// tidak pernah dipanggil dari mana pun, jadi loading indicator ini "mati" - sekarang dihidupkan.
const store = useStore()
const isLoading = computed(() => store.isLoading)
</script>

<style scoped>
.top-loading-bar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  z-index: 9999;
  background: transparent;
  overflow: hidden;
  pointer-events: none;
}

.top-loading-bar__track {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 40%;
  background: linear-gradient(90deg, transparent, #c9a227, #0f2a4a, #c9a227, transparent);
  animation: top-loading-bar-sweep 1.1s ease-in-out infinite;
}

@keyframes top-loading-bar-sweep {
  0% {
    left: -40%;
  }
  100% {
    left: 100%;
  }
}

.fade-enter-active {
  transition: opacity 0.15s ease;
}
.fade-leave-active {
  transition: opacity 0.25s ease 0.1s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
  .top-loading-bar__track {
    animation: none;
    left: 0;
    width: 100%;
    opacity: 0.7;
  }
}
</style>
