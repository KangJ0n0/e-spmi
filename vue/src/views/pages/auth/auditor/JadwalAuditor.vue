<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <div class="mb-6 border-b border-gray-200 pb-4">
      <h1 class="text-2xl font-bold text-gray-800">Daftar Jadwal Audit</h1>
      <p class="text-sm text-gray-500 mt-1">
        Pilih jadwal di mana Anda ditugaskan sebagai Auditor untuk mulai mengisi instrumen.
      </p>
    </div>

    <!-- State Loading -->
    <div v-if="isLoading" class="text-center py-10 text-gray-500">Memuat data penugasan...</div>

    <!-- State Kosong -->
    <div
      v-else-if="listJadwal.length === 0"
      class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200"
    >
      <svg
        class="mx-auto h-12 w-12 text-gray-400 mb-3"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
        />
      </svg>
      <p class="text-gray-600 font-medium">Belum ada penugasan audit untuk Anda saat ini.</p>
    </div>

    <!-- Grid Kartu Jadwal -->
    <!-- FIX tampilan: sebelumnya grid-cols-1/2/3 tetap reserve 3 kolom di layar lebar walau
         jadwalnya cuma 1-2 (jadwal per role emang jarang banyak), jadi nyisain banyak ruang
         kosong di kanan. Sekarang pakai auto-fit: kartu otomatis ngisi lebar yang ada (1 kartu =
         full width sampai batas max-w, 2 kartu = bagi dua, dst), nggak reserve kolom kosong. -->
    <div
      v-else
      class="grid gap-6 max-w-4xl"
      style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))"
    >
      <div
        v-for="item in listJadwal"
        :key="item.id"
        class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow bg-white flex flex-col"
      >
        <div class="flex justify-between items-start mb-3">
          <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded">
            {{ formatSemester(item.jadwal?.semester || item.semester) }}
          </span>
        </div>

        <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2">
          {{ item.jadwal?.nama_jadwal || item.nama_jadwal }}
        </h3>

        <div class="space-y-1 mb-5 flex-grow">
          <p class="text-sm text-gray-600">
            <span class="font-medium">Area:</span> {{ item.jadwal?.area_audit || item.area_audit }}
          </p>
          <p class="text-xs text-gray-500">
            Masa Pelaksanaan: {{ item.jadwal?.tanggal_awal || item.tanggal_awal }} s/d
            {{ item.jadwal?.tanggal_akhir || item.tanggal_akhir }}
          </p>
        </div>

        <!-- Halaman "Lihat Jawaban & Nilai" (NilaiInstrumenAuditor.vue) - halaman BARU terpisah
             dari IsiInstrumenAuditor.vue lama, kirim id lewat path param (bukan query) sesuai
             definisi route-nya. -->
        <div class="pt-4 border-t border-gray-100 space-y-2">
          <router-link
            :to="`/auditor/nilai-instrumen/${item.jadwal_spmi_id || item.id}`"
            class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-md transition-colors text-sm"
          >
            Lihat Jawaban & Nilai
          </router-link>
          <router-link
            :to="`/auditor/pilih-pertanyaan/${item.jadwal_spmi_id || item.id}`"
            class="block w-full text-center border border-blue-600 text-blue-600 hover:bg-blue-50 font-medium py-2 rounded-md transition-colors text-sm"
          >
            Pilih Pertanyaan
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axiosClient from '@/axios'
// QOL fix (13 Sep 2026, dilaporkan user) - dulu tampilin kode mentah "20272", sekarang label
// manusiawi "Genap 2027/2028". Lihat komentar lengkap di utils/formatSemester.js.
import { formatSemester } from '@/utils/formatSemester'

const listJadwal = ref([])
const isLoading = ref(false)

const fetchJadwalAuditor = async () => {
  isLoading.value = true
  try {
    // API ini harus mengambil data dari tabel penunjukan_auditors
    // dengan filter status = 'auditor' dan dosen_id = user yg login
    const response = await axiosClient.get('/auditor/jadwal-saya')
    listJadwal.value = response.data.data || response.data
  } catch (error) {
    console.error('Gagal mengambil jadwal:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchJadwalAuditor()
})
</script>
