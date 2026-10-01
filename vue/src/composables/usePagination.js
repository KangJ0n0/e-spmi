import { computed, ref, watch } from 'vue'

/**
 * Composable pagination (1 Okt 2026) - dipakai di halaman-halaman daftar/tabel di seluruh app
 * (Admin/Auditor/Auditee) yang datanya sudah di-fetch PENUH sekali jalan ke memori lewat axios
 * (ref([])), BUKAN paginated dari backend. Composable ini CUMA motong array itu jadi per-halaman
 * di sisi FE (client-side) - tidak mengubah cara fetch data, tidak ada request baru tiap pindah
 * halaman.
 *
 * Dipakai bareng komponen <Pagination> (lihat vue/src/components/Pagination.vue).
 *
 * @param {import('vue').Ref<Array>} items - ref array sumber (hasil fetch, misal listPertanyaan)
 * @param {number} perPage - jumlah baris per halaman (standar app: 20)
 */
export function usePagination(items, perPage = 20) {
  const page = ref(1)

  const totalPages = computed(() => Math.max(1, Math.ceil((items.value?.length || 0) / perPage)))

  const pagedItems = computed(() => {
    const start = (page.value - 1) * perPage
    return (items.value || []).slice(start, start + perPage)
  })

  // Reset ke halaman 1 tiap kali sumber datanya diganti (fetch ulang / refresh list) - biar
  // nggak "nyangkut" di halaman lama yang mungkin sudah nggak ada lagi di data baru.
  watch(items, () => {
    page.value = 1
  })

  // Jaga-jaga: kalau lagi di halaman terakhir dan datanya berkurang (terfilter/terhapus) sampai
  // halaman itu nggak valid lagi, mundur ke halaman valid terakhir - bukan nge-reset ke 1 (biar
  // nggak kerasa "lompat" kalau cuma berkurang sedikit).
  watch(totalPages, (tp) => {
    if (page.value > tp) page.value = tp
  })

  return { page, totalPages, pagedItems, perPage }
}
