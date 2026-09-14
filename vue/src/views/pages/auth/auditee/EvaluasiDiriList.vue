<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <div class="mb-6 border-b border-gray-200 pb-4">
      <h1 class="text-2xl font-bold text-gray-800">Evaluasi Diri</h1>
      <p class="text-sm text-gray-500 mt-1">
        Pilih jadwal evaluasi untuk menginput jawaban dan mengunggah dokumen bukti.
      </p>
    </div>

    <div v-if="isLoading" class="text-center py-10 text-gray-500">Memuat data penugasan...</div>

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
          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>
      <p class="text-gray-600 font-medium">
        Belum ada jadwal evaluasi untuk unit/program studi Anda.
      </p>
    </div>

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
          <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
            {{ formatSemester(item.jadwal?.semester || item.semester) }}
          </span>
        </div>

        <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2">
          {{ item.jadwal?.nama_jadwal || item.nama_jadwal }}
        </h3>

        <div class="space-y-1 mb-5 flex-grow">
          <p class="text-sm text-gray-600">
            <span class="font-medium">Area Audit:</span>
            {{ item.jadwal?.area_audit || item.area_audit }}
          </p>
          <p class="text-xs text-gray-500">
            Tenggat Waktu: {{ item.jadwal?.tanggal_akhir || item.tanggal_akhir }}
          </p>
        </div>

        <div class="pt-4 border-t border-gray-100">
          <router-link
            :to="{
              path: '/auditee/instrumen-auditee',
              query: { id: item.jadwal_spmi_id || item.id },
            }"
            class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-md transition-colors text-sm"
          >
            Mulai Evaluasi Diri
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Halaman BARU (11 Sep 2026) - pecahan dari JadwalAuditee.vue lama yang menggabungkan 2 aksi
// ("Mulai Evaluasi Diri" + "Lihat Hasil") dalam 1 halaman. User minta dipisah jadi 2 halaman per
// role. Halaman ini fokus cuma buat alur "Evaluasi Diri" (isi jawaban + bukti dokumen).
import { ref, onMounted } from 'vue'
import axiosClient from '@/axios'
// QOL fix (13 Sep 2026, dilaporkan user) - dulu tampilin kode mentah "20272", sekarang label
// manusiawi "Genap 2027/2028". Lihat komentar lengkap di utils/formatSemester.js.
import { formatSemester } from '@/utils/formatSemester'

const listJadwal = ref([])
const isLoading = ref(false)

const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const fetchJadwalAuditee = async () => {
  isLoading.value = true
  const response = await axiosClient.get('/auditee/jadwal-saya')
  isLoading.value = false
  if (gagal(response)) return
  listJadwal.value = response.data.data || response.data
}

onMounted(() => {
  fetchJadwalAuditee()
})
</script>
