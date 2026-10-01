<!--
  Komponen baru (1 Okt 2026) - pager sederhana buat pagination SISI KLIEN (lihat
  composables/usePagination.js) yang dipasang di halaman-halaman daftar/tabel seluruh app.
  Sengaja TIDAK dipasangkan ke TableComponent.vue (itu punya mekanisme pagination SERVER-SIDE
  sendiri - dataTable.page/per_page/last_page dari backend, dipakai BankPertanyaan.vue &
  JadwalAudit.vue, TIDAK disentuh di paket ini) - ini komponen baru yang lebih ringan, khusus buat
  halaman-halaman yang datanya sudah di-fetch penuh ke memori (ref([])) dan belum ada paginationnya
  sama sekali.
-->
<template>
  <nav
    v-if="totalPages > 1"
    class="flex items-center justify-between flex-wrap gap-2 mt-4 pt-3 border-t border-gray-200"
  >
    <p class="text-xs text-gray-500">Halaman {{ page }} dari {{ totalPages }}</p>
    <div class="flex items-center gap-1">
      <button
        type="button"
        :disabled="page === 1"
        @click="$emit('update:page', page - 1)"
        class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
      >
        &larr; Sebelumnya
      </button>
      <button
        v-for="n in nomorHalaman"
        :key="n"
        type="button"
        @click="$emit('update:page', n)"
        class="w-8 h-8 text-xs font-medium rounded-md"
        :class="n === page ? 'bg-blue-600 text-white' : 'border border-gray-300 hover:bg-gray-50'"
      >
        {{ n }}
      </button>
      <button
        type="button"
        :disabled="page === totalPages"
        @click="$emit('update:page', page + 1)"
        class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
      >
        Selanjutnya &rarr;
      </button>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  page: { type: Number, required: true },
  totalPages: { type: Number, required: true },
})
defineEmits(['update:page'])

// Tampilin maksimal 5 nomor halaman sekaligus (dicenter di sekitar halaman aktif), biar nggak
// kepanjangan kalau total halamannya banyak (mis. bank soal ribuan baris -> puluhan halaman).
const MAKS_NOMOR = 5
const nomorHalaman = computed(() => {
  const total = props.totalPages
  const cur = props.page
  if (total <= MAKS_NOMOR) return Array.from({ length: total }, (_, i) => i + 1)
  let start = Math.max(1, cur - Math.floor(MAKS_NOMOR / 2))
  let end = start + MAKS_NOMOR - 1
  if (end > total) {
    end = total
    start = end - MAKS_NOMOR + 1
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})
</script>
