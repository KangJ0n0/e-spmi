<template>
  <div class="w-full overflow-hidden rounded-lg border border-gray-100">
    <table class="w-full text-sm text-left text-gray-500">
      <thead class="text-xs text-gray-700 uppercase bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-3">Nama Dosen</th>
          <th scope="col" class="px-6 py-3">Jumlah Jadwal</th>
          <th scope="col" class="px-6 py-3"></th>
        </tr>
      </thead>
      <tbody v-if="loading && items.length === 0">
        <tr class="bg-white border-b">
          <td class="px-6 py-8 text-center" colspan="3">
            <span class="inline-flex items-center justify-center gap-2 text-gray-500">
              <svg class="animate-spin h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Memuat data...
            </span>
          </td>
        </tr>
      </tbody>
      <tbody v-else-if="items.length === 0">
        <tr class="bg-white border-b">
          <td class="px-6 py-8 text-center" colspan="3">Data tidak ditemukan</td>
        </tr>
      </tbody>
      <template v-else v-for="dosen in items" :key="dosen.dosen_id">
        <tr
          class="bg-white border-b hover:bg-gray-50 cursor-pointer select-none"
          @click="toggle(dosen.dosen_id)"
        >
          <td class="px-6 py-3 font-medium text-gray-900">{{ dosen.nama_dosen }}</td>
          <td class="px-6 py-3">
            <span class="text-xs font-semibold inline-block py-1 px-2.5 rounded-full bg-blue-100 text-blue-700">
              {{ dosen.jumlah_jadwal }} jadwal
            </span>
          </td>
          <td class="px-6 py-3 text-right">
            <svg
              class="w-4 h-4 inline-block transition-transform text-gray-400"
              :class="{ 'rotate-180': isOpen(dosen.dosen_id) }"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </td>
        </tr>
        <tr v-if="isOpen(dosen.dosen_id)" class="bg-gray-50 border-b">
          <td colspan="3" class="px-6 py-3">
            <ul class="divide-y divide-gray-200 bg-white rounded-md border border-gray-200">
              <li
                v-for="jadwal in dosen.jadwal_list"
                :key="jadwal.penugasan_id"
                class="flex items-center justify-between px-4 py-2.5 hover:bg-blue-50 cursor-pointer"
                @click.stop="$emit('go-to-jadwal', jadwal.jadwal_spmi_id)"
              >
                <div class="text-sm text-gray-700">
                  <span class="font-medium">{{ jadwal.nama_jadwal }}</span>
                  <span v-if="jadwal.semester" class="text-gray-400 ml-2">— {{ formatSemester(jadwal.semester) }}</span>
                  <span
                    v-if="jadwal.is_ketua"
                    class="ml-2 text-xs font-semibold inline-block py-0.5 px-2 rounded-full bg-yellow-100 text-yellow-700"
                  >
                    Ketua
                  </span>
                </div>
                <span class="text-xs text-blue-600 font-medium">Buka jadwal &rarr;</span>
              </li>
            </ul>
          </td>
        </tr>
      </template>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'
// QOL fix (13 Sep 2026, dilaporkan user) - dulu tampilin kode mentah "20272", sekarang label
// manusiawi "Genap 2027/2028". Lihat komentar lengkap di utils/formatSemester.js.
import { formatSemester } from '@/utils/formatSemester'

defineProps({
  items: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['go-to-jadwal'])

// Baris per-jadwal disembunyikan default, buka/tutup per dosen lewat klik baris nama - ini
// yang bikin dosen yang pegang banyak jadwal cuma makan 1 baris di daftar (nggak lagi
// berulang-ulang persis nama yang sama kayak sebelumnya).
const openSet = ref(new Set())
const isOpen = (dosenId) => openSet.value.has(dosenId)
const toggle = (dosenId) => {
  const next = new Set(openSet.value)
  if (next.has(dosenId)) {
    next.delete(dosenId)
  } else {
    next.add(dosenId)
  }
  openSet.value = next
}
</script>
