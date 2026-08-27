<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-800 mb-1">Dashboard Auditor</h1>
      <p class="text-gray-500 mb-8">Ringkasan tugas audit Anda</p>

      <!-- Ringkasan / Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="bg-white rounded-lg shadow-sm border border-gray-100 p-5"
        >
          <p class="text-sm text-gray-500 mb-1">{{ stat.label }}</p>
          <p class="text-3xl font-bold text-gray-800">
            {{ isLoading ? '...' : stat.value }}
          </p>
          <p v-if="stat.note" class="text-xs text-gray-400 mt-1">{{ stat.note }}</p>
        </div>
      </div>

      <!-- Jadwal Audit -->
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Jadwal Audit Saya</h2>
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-10">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-left">
            <tr>
              <th class="px-5 py-3 font-medium">Tanggal</th>
              <th class="px-5 py-3 font-medium">Unit Diaudit</th>
              <th class="px-5 py-3 font-medium">Progres</th>
              <th class="px-5 py-3 font-medium">Status</th>
              <th class="px-5 py-3 font-medium"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading">
              <td colspan="5" class="px-5 py-6 text-center text-gray-400">Memuat...</td>
            </tr>
            <tr
              v-else
              v-for="jadwal in jadwalAudit"
              :key="jadwal.jadwal_spmi_id"
              class="border-t border-gray-100 hover:bg-gray-50"
            >
              <td class="px-5 py-3 text-gray-700">
                {{ formatTanggal(jadwal.tanggal_awal) }} - {{ formatTanggal(jadwal.tanggal_akhir) }}
              </td>
              <td class="px-5 py-3 text-gray-700">{{ jadwal.area_audit }}</td>
              <td class="px-5 py-3 text-gray-500 text-xs w-40">
                <div class="flex items-center justify-between mb-1">
                  <span>{{ jadwal._dinilai }}/{{ jadwal._total }} soal dinilai</span>
                  <span class="font-semibold text-gray-600">{{ persenSelesai(jadwal) }}%</span>
                </div>
                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-blue-500 rounded-full"
                    :style="{ width: persenSelesai(jadwal) + '%' }"
                  ></div>
                </div>
                <div v-if="jadwal._dinilai > 0" class="mt-1 text-[11px] text-gray-400">
                  {{ jadwal._ks }} KS &middot; {{ jadwal._kts }} KTS
                </div>
              </td>
              <td class="px-5 py-3">
                <span
                  class="text-xs px-2 py-1 rounded-full font-medium"
                  :class="statusClass(jadwal._status)"
                >
                  {{ jadwal._status }}
                </span>
              </td>
              <td class="px-5 py-3 text-right">
                <router-link
                  :to="`/auditor/nilai-instrumen/${jadwal.jadwal_spmi_id}`"
                  class="text-blue-600 hover:underline text-sm"
                >
                  Buka
                </router-link>
              </td>
            </tr>
            <tr v-if="!isLoading && jadwalAudit.length === 0">
              <td colspan="5" class="px-5 py-6 text-center text-gray-400">
                Belum ada jadwal audit yang ditugaskan ke Anda
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Menu Navigasi - cuma halaman yang beneran ada route-nya (samain sama useSidebar.js) -->
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Menu</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <router-link
          v-for="menu in menus"
          :key="menu.label"
          :to="menu.to"
          class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-blue-200 transition flex flex-col items-start"
        >
          <span class="text-2xl mb-2">{{ menu.icon }}</span>
          <span class="font-medium text-gray-800">{{ menu.label }}</span>
          <span class="text-xs text-gray-400 mt-1">{{ menu.description }}</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'AuditorDashboard' })

import { ref, computed, onMounted } from 'vue'
import axiosClient from '@/axios'

const jadwalAudit = ref([])
const isLoading = ref(false)

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject) promise-nya
// untuk error 400/404/422/500 - jadi cek bentuk response-nya, sama seperti pola di file lain.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const formatTanggal = (tgl) => {
  if (!tgl) return '-'
  return new Date(tgl).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

// Status per jadwal dihitung dari progres penilaian beneran (bukan cuma dari rentang tanggal),
// karena yang paling relevan buat Auditor adalah "masih ada soal yang perlu dinilai atau nggak".
const hitungStatus = (jadwal) => {
  if (jadwal._total === 0) return 'Belum Ada Soal'
  if (jadwal._dinilai === jadwal._total) return 'Selesai'
  const akhir = new Date(jadwal.tanggal_akhir + 'T23:59:59')
  if (new Date() > akhir) return 'Kedaluwarsa'
  if (jadwal._siapDinilai > 0) return 'Siap Dinilai'
  return 'Menunggu Auditee'
}

const statusClass = (status) => {
  const map = {
    Selesai: 'bg-green-50 text-green-600',
    Kedaluwarsa: 'bg-red-50 text-red-600',
    'Siap Dinilai': 'bg-yellow-50 text-yellow-700',
    'Menunggu Auditee': 'bg-gray-100 text-gray-500',
    'Belum Ada Soal': 'bg-gray-100 text-gray-500',
  }
  return map[status] || 'bg-gray-100 text-gray-500'
}

// Persentase progres "sampai mana prosesnya" per jadwal - dipakai buat progress bar di tabel.
const persenSelesai = (jadwal) => {
  if (!jadwal._total) return 0
  return Math.round((jadwal._dinilai / jadwal._total) * 100)
}

const stats = computed(() => {
  const totalJadwal = jadwalAudit.value.length
  const totalSiapDinilai = jadwalAudit.value.reduce((sum, j) => sum + (j._siapDinilai || 0), 0)
  const totalDinilai = jadwalAudit.value.reduce((sum, j) => sum + (j._dinilai || 0), 0)
  const totalKS = jadwalAudit.value.reduce((sum, j) => sum + (j._ks || 0), 0)
  const totalKTS = jadwalAudit.value.reduce((sum, j) => sum + (j._kts || 0), 0)
  return [
    { label: 'Jadwal Ditugaskan', value: totalJadwal, note: 'audit yang ditugaskan ke Anda' },
    { label: 'Soal Siap Dinilai', value: totalSiapDinilai, note: 'auditee sudah jawab, menunggu penilaian' },
    { label: 'Soal Sudah Dinilai', value: totalDinilai, note: `${totalKS} KS, ${totalKTS} KTS` },
    {
      label: 'Progres Keseluruhan',
      value: `${persenTotal.value}%`,
      note: 'dari semua soal yang ditugaskan',
    },
  ]
})

// Progres gabungan semua jadwal (dinilai / total), dipakai buat stat card "Progres Keseluruhan".
const persenTotal = computed(() => {
  const total = jadwalAudit.value.reduce((sum, j) => sum + (j._total || 0), 0)
  const dinilai = jadwalAudit.value.reduce((sum, j) => sum + (j._dinilai || 0), 0)
  if (!total) return 0
  return Math.round((dinilai / total) * 100)
})

const menus = ref([
  {
    icon: '📅',
    label: 'Jadwal Audit',
    description: 'Lihat jadwal audit yang ditugaskan',
    to: '/auditor/jadwal-auditor',
  },
  {
    icon: '🖨️',
    label: 'Cetak Dokumen',
    description: 'Download dokumen resmi Instrumen 1-4 & 6',
    to: '/auditor/cetak-dokumen',
  },
])

const fetchDashboard = async () => {
  isLoading.value = true
  const res = await axiosClient.get('/auditor/jadwal-saya')
  if (gagal(res)) {
    isLoading.value = false
    return
  }

  const jadwalList = res.data || []

  // Buat tiap jadwal, ambil daftar soalnya buat hitung progres penilaian - dipanggil paralel
  // (bukan satu-satu) karena jumlah jadwal per Auditor biasanya kecil.
  const hasilPerJadwal = await Promise.all(
    jadwalList.map(async (jadwal) => {
      const resSoal = await axiosClient.get(`/jadwal-audit/${jadwal.jadwal_spmi_id}/pertanyaan`)
      const soal = gagal(resSoal) ? [] : resSoal.data || []
      const total = soal.length
      const dinilai = soal.filter((s) => s.status_jawaban === 'sudah').length
      const siapDinilai = soal.filter(
        (s) => s.jawaban?.deskripsi_hasil && s.status_jawaban !== 'sudah',
      ).length
      const ks = soal.filter((s) => s.jawaban?.status_temuan === 'KS').length
      const kts = soal.filter((s) => s.jawaban?.status_temuan === 'KTS').length
      return {
        ...jadwal,
        _total: total,
        _dinilai: dinilai,
        _siapDinilai: siapDinilai,
        _ks: ks,
        _kts: kts,
      }
    }),
  )

  hasilPerJadwal.forEach((j) => {
    j._status = hitungStatus(j)
  })

  jadwalAudit.value = hasilPerJadwal
  isLoading.value = false
}

onMounted(() => {
  fetchDashboard()
})
</script>
